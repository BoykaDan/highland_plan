<?php

namespace Zhiyi\Component\ZhiyiPlus\PlusComponentIm\Service\IM;

use GuzzleHttp\Client;

class Service
{
    /**
     * 是否开启对IM聊天服务器请求的调试功能.
     *
     * @var bool
     */
    public $service_debug = false;

    /**
     * 保存错误信息.
     *
     * @var string
     */
    protected $error = '';

    /**
     * 定义im服务器的相�
     * �请求链接.
     *
     * @var array
     */
    public $service_urls = [
        'base_url' => '',
        'apis' => [
            'users' => '/users',
            'conversations' => '/conversations',
            'member' => '/conversations/{cid}/members',
            'limited' => '/conversations/{cid}/limited-members',
            'message' => '/conversations/{cid}/messages',
        ],
    ];

    /**
     * 定义请求链接中拼接的参数.
     *
     * @var string
     */
    protected $sub_request_url = '';
    /**
     * 请求的类型别名定义.
     *
     * @var array
     */
    protected $response_type = [
        'post' => ['post', 'add', 'init'],
        'put' => ['put', 'update', 'save'],
        'delete' => ['delete', 'del', 'remove'],
        'get' => ['get', 'select'],
        'patch' => ['patch'],
    ];

    /**
     * 定义IM服务器的授权用户登陆信息.
     *
     * @var array
     */
    public $service_auth = [
        'user' => 'admin',
        'password' => '123456',
    ];

    /**
     * 参数数组.
     *
     * @var array
     */
    protected $params = [];

    /**
     * 当前请求操作的模块类型.
     *
     * @var string
     */
    protected $request_mod = '';

    /**
     * 当前操作的请求方式.
     *
     * @var string
     */
    protected $requset_method = '';

    public function __construct($config = [])
    {
        $this->service_urls['base_url'] = $config['base_url'] ?? '';
    }

    /**
     * __call方法.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-01-16T09:40:04+080
     *
     * @version  1.0
     *
     * @param string $method 访问方法
     * @param array  $params 参数信息
     *
     * @return 执行结果
     */
    public function __call($method, $params)
    {
        $type_alias = '';
        $method = strtolower($method);
        $apiList = array_keys($this->service_urls['apis']);
        foreach ($apiList as $api) {
            if (preg_match('/^'.$api.'\w+/', $method)) {
                $this->request_mod = $api;
                $type_alias = self::parseName(substr($method, strlen($api)))[0];
                break;
            }
        }
        if (! $this->request_mod) {
            $this->error = '该聊天服务不可用';

            return false;
        }
        //请求子参数
        if (isset($params[1])) {
            $this->sub_request_url = $params[1];
        }
        // 调用本类中的方法,获取请求方法
        $type_alias = strtolower($type_alias);
        $this->requset_method = $this->getRequestType($type_alias);

        // 请求参数赋值
        $this->params = $params[0];

        // 自定义方法是否存在,存在则执行并返回
        $fun = $this->request_mod.'Do'.ucfirst($this->requset_method);
        if (method_exists($this, $fun)) {
            return $this->$fun();
        }

        // 直接调用请求IM服务器
        $res = $this->request();

        // 是否定义后置方法,已定义则执行并返回
        $after_fun = '_after_'.$fun;
        if (method_exists($this, $after_fun)) {
            return $this->$after_fun($res);
        } else {
            $body = $res->getBody()->getContents();
            if ($body) {
                // 有返回主体
                $ret = json_decode($body, true);
            } else {
                // 没有返回主体,获取状态码
                $ret = [
                    'code' => $res->getStatusCode(),
                ];
            }

            return $ret;
        }
    }

    /**
     * 获取请求类型.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-01-16T09:59:42+080
     *
     * @version  1.0
     *
     * @param string $type_alias 方法别名
     *
     * @return string [description]
     */
    private function getRequestType(string $type_alias) : string
    {
        $type = '';
        if (! $type_alias) {
            return $type;
        }
        foreach ($this->response_type as $key => $value) {
            if (in_array($type_alias, $value)) {
                $type = $key;
                break;
            }
        }

        return $type;
    }

    /**
     * 获取IM服务器请求的地址
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-01-16T14:29:44+080
     *
     * @version  1.0
     *
     * @return string IM服务器请求地址
     */
    private function getRequestUrl() : string
    {
        $url = $this->service_urls['apis'][$this->request_mod] ?? '';
        if (! $url) {
            return '';
        } else {
            $url .= $this->sub_request_url;
            // 待替换字符串匹配
            preg_match_all('/\{(\w+)\}/', $url, $matches);
            $replace = $matches[1];
            $replace = array_intersect($replace, array_keys($this->params));
            // 执行替换并清理不需要的参数
            foreach ($replace as $v) {
                $url = str_replace('{'.$v.'}', $this->params[$v], $url);
                unset($this->params[$v]);
            }

            return $url;
        }
    }

    /**
     * 解析操作方法.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-01-13T13:56:13+080
     *
     * @version  1.0
     *
     * @param string $name 原操作名称
     *
     * @return array 解析结果
     */
    public static function parseName($name) : array
    {
        return strpos($name, '/') ? explode('/', $name, 2) : [$name];
    }

    /**
     * 检测会话类型.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-01-11T18:01:26+080
     *
     * @version  1.0
     *
     * @param int $type 会话类型 0:私聊 1:群聊 2:单聊
     *
     * @return bool 是否合法
     */
    public function checkConversationType(int $type) : bool
    {
        return in_array($type, [0, 1, 2]) ? true : false;
    }

    /**
     * 请求方法.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-01-17T14:16:20+080
     *
     * @version  1.0
     *
     * @return ClientClass
     */
    public function request()
    {
        // 创建请求根地址类
        $client = new Client(['base_uri' => $this->service_urls['base_url']]);

        // 发送请求内容
        $request_body = [
            'auth' => array_values($this->service_auth),
            'http_errors' => $this->service_debug,
        ];

        // 获取请求的地址
        $request_url = $this->getRequestUrl();

        // 处理请求的参数
        if (in_array($this->requset_method, ['get', 'delete'])) {
            if (! empty($this->params)) {
                foreach ($this->params as $key => $value) {
                    $request_url .= '/'.$value;
                }
            }

            // 同时也发送请求的参数信息
            $request_body['query'] = $this->params;
        } else {
            // 采用表单的方式提交数据
            $request_body['form_params'] = $this->params;
        }

        // 发送请求
        return $client->request($this->requset_method, $request_url, $request_body);
    }

    /**
     * 检测聊天的uids参数是否合法.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-01-20T14:19:39+080
     *
     * @version  1.0
     *
     * @param int          $type 聊天会话类型
     * @param string|array $uids 默认加�
     * �聊天的用户uid组
     *
     * @return bool 是否合法
     */
    public function checkUids(int $type, $uids) : bool
    {
        $uids = is_array($uids) ? $uids : array_filter(explode(',', $uids));
        switch ($type) {
            case 0:
                // 私聊 必须包含2个uid
                if (count($uids) < 2) {
                    return false;
                }
                break;
            case 1:
                // 群聊 至少需要一个uid
                if (count($uids) < 1) {
                    return false;
                }
                break;
            case 2:
                // 聊天室 暂时不限制
                break;
            default:
                return false;
        }

        return true;
    }

    /**
     * 获取错误信息.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-01-11T18:08:14+080
     *
     * @version  1.0
     *
     * @return string 错误信息描述
     */
    public function getError() : string
    {
        return $this->error;
    }
}
