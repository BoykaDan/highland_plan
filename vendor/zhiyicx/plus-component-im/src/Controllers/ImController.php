<?php

namespace Zhiyi\Component\ZhiyiPlus\PlusComponentIm\Controllers;

use Illuminate\Http\Request;
use Zhiyi\Plus\Models\CommonConfig;
use Zhiyi\Plus\Http\Controllers\Controller;
use Zhiyi\Component\ZhiyiPlus\PlusComponentIm\Models\ImUser;
use Zhiyi\Component\ZhiyiPlus\PlusComponentIm\Models\ImConversation;
use Zhiyi\Component\ZhiyiPlus\PlusComponentIm\Service\IM\Service as ImService;

class ImController extends Controller
{
    protected $config;

    /**
     * 初始化服务器�
     * �置.
     *
     * @var string
     */
    public function __construct()
    {
        $imserviceconfig = CommonConfig::byNamespace('common')->byName('im:serve')->first();

        $this->config = [
            'base_url' => 'http://'.$imserviceconfig->value,
        ];
    }

    /**
     * 获取聊天服务账号信息.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-01-18T16:08:41+080
     *
     * @version  1.0
     *
     * @param Request $request 请求类
     *
     * @return mixed 返回结果
     */
    public function getImAccount(Request $request)
    {
        // 当前登陆的用户
        $user = $request->user();

        // 获取本地的IM用户
        $ImUser = new ImUser();
        $data = $ImUser->where('user_id', $user->id)->first();

        // 本地不存在账号信息
        if (! $data) {
            $ImService = new ImService($this->config);
            $res = $ImService->usersPost(['uid' => $user->id, 'name' => $user->name]);
            // 处理返回
            if ($res['code'] == 201) {
                // 注册成功,保存本地用户
                $data = [
                    'user_id' => $user->id,
                    'im_password' => $res['data']['token'],
                ];
                $data = $ImUser->create($data);
            }
        }
        if ($data) {
            return $this->returnMessage(0, $data->toArray(), 200);
        } else {
            return $this->returnMessage(3002, [], 422);
        }
    }

    /**
     * 创建会话.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-01-18T16:19:33+080
     *
     * @version  1.0
     *
     * @param Request $request 请求类
     *
     * @return mixed 返回结果
     */
    public function createConversations(Request $request)
    {
        $type = intval($request->input('type'));
        $ImService = new ImService($this->config);
        // 聊天对话类型
        if (! $request->exists('type') || ! $ImService->checkConversationType($type)) {
            // 会话类型不支持
            return $this->returnMessage(3003, [], 400);
        }
        $user = $request->user();
        // 对话成员处理
        $uids = is_array($request->input('uids')) ? $request->input('uids') : array_filter(explode(',', $request->input('uids')));
        $uids[] = $user->id;
        $uids = array_unique($uids);
        sort($uids);
        // 私聊时检测对话是否已经存在
        if (intval($type) === 0) {
            $info = ImConversation::where(['type' => 0, 'uids' => implode(',', $uids)])->first();
            if ($info) {
                $info = $info->toArray();

                return $this->returnMessage(0, $info, 200);
            }
        }

        // 组装数据
        $conversations = [
            'type' => intval($type),
            'name' => (string) $request->input('name'),
            'pwd' => (string) $request->input('pwd'),
            'uids' => $uids,
            'uid' => $user->id,
        ];

        // 检测uids参数是否合法
        $is_void = $ImService->checkUids($conversations['type'], $conversations['uids']);
        if (! $is_void) {
            // 返回会话参数错误
            return $this->returnMessage(3004, [], 422);
        }
        $res = $ImService->conversationsPost($conversations);
        if ($res['code'] != '201') {
            return response()->json(static::createJsonData([
                'code' => 3005,
                'status' => false,
                'message' => '会话成员没有聊天授权',
            ]))->setStatusCode(422);
        } else {
            // 保存会话
            $addConversation = [
                'user_id' => $user->id,
                'cid' => $res['data']['cid'],
                'name' => $res['data']['name'],
                'pwd' => $res['data']['pwd'],
                'is_disabled' => 0,
                'type' => $res['data']['type'],
                'uids' => $uids,
            ];
            $info = ImConversation::create($addConversation);
            $info = $info->toArray();

            return $this->returnMessage(0, $info, 200);
        }
    }

    /**
     * 获取会话信息.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-01-20T16:22:58+080
     *
     * @version  1.0
     *
     * @param int $cid 对话ID
     *
     * @return
     */
    public function getConversation(int $cid)
    {
        $info = ImConversation::where('cid', $cid)->first();
        if ($info) {
            $info = $info->toArray();

            return $this->returnMessage(0, $info, 200);
        }

        return $this->returnMessage(3006, [], 404);
    }

    /**
     * 获取会话列表.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-01-22T09:23:42+080
     *
     * @version  1.0
     *
     * @return
     */
    public function getConversationList(Request $request)
    {
        $user = $request->user();
        $list = ImConversation::whereRaw('find_in_set('.$user->id.',uids)')->orderBy('updated_at', 'desc')->get();
        if ($list) {
            return $this->returnMessage(0, $list->toArray(), 200);
        }

        return $this->returnMessage(0, [], 200);
    }

    /**
     * 删除对话.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-02-04T14:13:28+080
     *
     * @version  1.0
     *
     * @param int $cid 对话ID
     *
     * @return
     */
    public function deleteConversation(int $cid, Request $request)
    {
        $info = ImConversation::where('cid', $cid)->first();
        if ($info) {
            $ImService = new ImService($this->config);
            // 如果是创建者,直接删除对话
            $user = $request->user();
            if ($user->id == $info->user_id) {
                $res = $ImService->conversationsDelete(['cids' => $cid]);
                if ($res['code'] == 204) {
                    $info->delete();

                    return $this->returnMessage(0, ['cid' => $cid], 200);
                }

                return $this->returnMessage(3009, [], 422);
            } else {
                return $this->returnMessage(3010, [], 401);
            }
        }

        return $this->returnMessage(3006, [], 404);
    }

    /**
     * 退出对话.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-02-04T14:13:28+080
     *
     * @version  1.0
     *
     * @param int $cid 对话ID
     *
     * @return
     */
    public function exitConversations(int $cid, Request $request)
    {
        $info = ImConversation::where('cid', $cid)->first();
        if ($info) {
            $user = $request->user();
            $ImService = new ImService($this->config);
            // 退出指定对话
            $res = $ImService->memberDelete(['cid' => $cid, 'uids' => $user->id], '/{uids}');
            if ($res['code'] == 204) {
                $uids = is_array($info->uids) ? $info->uids : explode(',', $info->uids);
                // 更新本地保存的状态
                $removeUid = array_search($user->id, $uids);
                if ($removeUid !== false) {
                    array_splice($uids, $removeUid, 1);
                    $info->uids = $uids;
                    $info->save();
                }

                return $this->returnMessage(0, ['cid' => $cid], 200);
            } else {
                return $this->returnMessage(3013, [], 422);
            }
        }

        return $this->returnMessage(3006, [], 404);
    }

    /**
     * 移除指定对话中的指定成员.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-02-05T14:05:25+080
     *
     * @version  1.0
     *
     * @param int $cid 对话ID
     * @param int $uid 需要移除的用户uid
     *
     * @return
     */
    public function deleteMembers(int $cid, int $uid, Request $request)
    {
        $info = ImConversation::where('cid', $cid)->first();
        if ($info) {
            $user = $request->user();
            if ($user->id != $info->user_id) {
                // 没有权限操作
                return $this->returnMessage(3010, [], 401);
            }
            $ImService = new ImService($this->config);
            // 退出指定对话
            $res = $ImService->memberDelete(['cid' => $cid, 'uids' => $uid], '/{uids}');
            if ($res['code'] == 204) {
                $uids = is_array($info->uids) ? $info->uids : explode(',', $info->uids);
                // 更新本地保存的状态
                $removeUid = array_search($uid, $uids);
                if ($removeUid !== false) {
                    array_splice($uids, $removeUid, 1);
                    $info->uids = $uids;
                    $info->save();
                }

                return $this->returnMessage(0, ['cid' => $cid, 'uid' => $uid], 200);
            } else {
                return $this->returnMessage(3014, [], 422);
            }
        }

        return $this->returnMessage(3006, [], 404);
    }

    /**
     * 对话成员限制.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-02-05T14:39:12+080
     *
     * @version  1.0
     *
     * @param int     $cid     对话ID
     * @param Request $request
     *
     * @return
     */
    public function disableLimited(int $cid, Request $request)
    {
        // 检测对话是存在
        $conversations = ImConversation::where('cid', $cid)->first();
        if ($conversations) {
            // 检测是不是管理员
            $user = $request->user();
            if ($user->id != $conversations->user_id) {
                // 没有权限操作
                return $this->returnMessage(3010, [], 401);
            }

            // 获取指定的限制的成员
            $uids = is_array($request->input('uids')) ? $request->input('uids') : array_filter(explode(',', $request->input('uids')));
            if (! $uids) {
                // 为空
                return $this->returnMessage(3011, [], 422);
            }
            $expire = $request->exists('expire') ? intval($request->input('expire')) : 0;
            $postData = [
                'uids' => $uids,
                'expire' => $expire,
                'cid' => $cid,
            ];

            $ImService = new ImService($this->config);
            // 退出指定对话
            $res = $ImService->limitedPost($postData);
            if ($res['code'] == 201) {
                return $this->returnMessage(0, $postData, 200);
            }

            return $this->returnMessage(3012, [], 422);
        }

        return $this->returnMessage(3006, [], 404);
    }

    /**
     * 移除对话成员限制.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-02-05T14:39:12+080
     *
     * @version  1.0
     *
     * @param int     $cid     对话ID
     * @param Request $request
     *
     * @return
     */
    public function enabledLimited(int $cid, int $uid, Request $request)
    {
        // 检测对话是存在
        $conversations = ImConversation::where('cid', $cid)->first();
        if ($conversations) {
            // 检测是不是管理员
            $user = $request->user();
            if ($user->id != $conversations->user_id) {
                // 没有权限操作
                return $this->returnMessage(3010, [], 401);
            }

            // 获取指定的限制的成员
            if (! $uid) {
                // 为空
                return $this->returnMessage(3011, [], 422);
            }
            $postData = [
                'uid' => $uid,
                'cid' => $cid,
            ];

            $ImService = new ImService($this->config);
            // 退出指定对话
            $res = $ImService->limitedDelete($postData, '/{uid}');
            if ($res['code'] == 204) {
                return $this->returnMessage(0, $postData, 200);
            }

            return $this->returnMessage(3012, [], 422);
        }

        return $this->returnMessage(3006, [], 404);
    }

    /**
     * 刷新聊天授权.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-01-22T17:35:47+080
     *
     * @version  1.0
     *
     * @return
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        // 获取旧的password
        $old_im_password = $request->input('password');

        // 验证是否存在
        $data = ImUser::where('im_password', $old_im_password)->first();
        if ($data) {
            // 刷新授权
            $ImService = new ImService($this->config);
            $res = $ImService->usersPatch(['token' => true, 'uid' => $user->id], '/{uid}');
            // 处理返回数据
            if ($res['code'] == 200) {
                $data->im_password = $res['data']['token'];
                $data->save();

                return $this->returnMessage(0, $data->toArray(), 200);
            } else {
                // 返回错误
                return $this->returnMessage(3008, [], 422);
            }
        }

        return $this->returnMessage(3007, [], 404);
    }

    /**
     * 返回信息.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-01-20T15:49:10+080
     *
     * @version  1.0
     *
     * @param int   $code      code状态码 0表示成功
     * @param int   $http_code http状态码
     * @param array $data      返回数据
     *
     * @return
     */
    private function returnMessage(int $code, array $data, $http_code = 200)
    {
        if ($code !== 0) {
            return response()->json(static::createJsonData([
                'code' => $code,
                'status' => false,
            ]))->setStatusCode($http_code);
        } else {
            return response()->json(static::createJsonData([
                'code' => 0,
                'status' => true,
                'data' => $data,
            ]))->setStatusCode($http_code);
        }
    }
}
