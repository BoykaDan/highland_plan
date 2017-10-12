<?php

namespace Zhiyi\Component\ZhiyiPlus\PlusComponentIm\AdminControllers;

use Zhiyi\Plus\Models\User;
use Zhiyi\Plus\Models\CommonConfig;
use Zhiyi\Plus\Http\Controllers\Controller;
use Zhiyi\Component\ZhiyiPlus\PlusComponentIm\Models\ImUser;
use Zhiyi\Component\ZhiyiPlus\PlusComponentIm\Request\StoreHelperPost;
use Zhiyi\Component\ZhiyiPlus\PlusComponentIm\Service\IM\Service as ImService;
use Zhiyi\Component\ZhiyiPlus\PlusComponentIm\Repository\ImServe as ImServeRepostory;

class HelperController extends Controller
{
    /**
     * 助手设置页面.
     *
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function show()
    {
        return view('component-im::helper', [
            'helpers' => $this->helpers(),
        ]);
    }

    /**
     * 添加助手.
     *
     * @param \Zhiyi\Component\ZhiyiPlus\PlusComponentIm\Request\StoreHelperPost $request
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function store(StoreHelperPost $request)
    {
        $uid = intval($request->input('uid'));
        $url = $request->input('url');

        $helpers = $this->helpers();

        if (($response = $this->repeat($request, $uid, $helpers)) === true) {
            array_push($helpers, [
                'uid' => $uid,
                'url' => $url,
            ]);

            CommonConfig::byNamespace('common')->byName('im:helper')
                ->update(['value' => json_encode($helpers)]);

            $this->checkImToken($uid);

            return redirect()->back()
                ->with('message', '添加成功');
        }

        return $response;
    }

    /**
     * 删除助手.
     *
     * @param string|int $uid
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function delete(int $uid)
    {
        $helpers = $this->helpers();

        foreach ($helpers as $key => $helper) {
            if ($helper['uid'] == $uid) {
                unset($helpers[$key]);
            }
        }

        $helpers = array_values($helpers);

        CommonConfig::byNamespace('common')->byName('im:helper')
                ->update(['value' => json_encode($helpers)]);

        return redirect()->back()
            ->with('message', '删除成功');
    }

    /**
     * 去重，判断助手是否已经存在.
     *
     * @param \Zhiyi\Component\ZhiyiPlus\PlusComponentIm\Request\StoreHelperPost $request
     * @param int|string $uid
     * @param array $helpers
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    protected function repeat(StoreHelperPost $request, $uid, array $helpers = [])
    {
        foreach ($helpers as $helper) {
            if ($helper['uid'] == $uid) {
                return redirect()->back()
                    ->withInput($request->only(['uid', 'url']))
                    ->withErrors(['uid' => '添加的用户已经存在']);
            }
        }

        return true;
    }

    /**
     * 获取助手列表.
     *
     * @return array
     * @author Seven Du <shiweidu@outlook.com>
     */
    protected function helpers()
    {
        $config = CommonConfig::byNamespace('common')->byName('im:helper')
            ->select('value')
            ->first();

        if (! $config) {
            $config = new CommonConfig();
            $config->namespace = 'common';
            $config->name = 'im:helper';
            $config->value = json_encode([]);
            $config->save();
        }

        return array_values(
            json_decode($config->value, true) ?: []
        );
    }

    /**
     * 验证助手Im账户.
     *
     * @author bs<414606094@qq.com>
     */
    protected function checkImToken($uid): bool
    {
        if (! ImUser::where('user_id', $uid)->first()) {
            $this->addImUser($uid);
        }

        return true;
    }

    /**
     * 添加im账户.
     *
     * @author bs<414606094@qq.com>
     */
    protected function addImUser($uid)
    {
        $user = User::find($uid);

        $ImService = new ImService(['base_url' => 'http://'.app(ImServeRepostory::class)->get()]);
        $res = $ImService->usersPost(['uid' => $user->id, 'name' => $user->name]);
        // 处理返回
        if ($res['code'] == 201) {
            // 注册成功,保存本地用户
            $data = [
                'user_id' => $user->id,
                'im_password' => $res['data']['token'],
            ];
            $data = ImUser::create($data);

            return true;
        }

        return false;
    }
}
