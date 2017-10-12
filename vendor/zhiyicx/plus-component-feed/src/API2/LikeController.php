<?php

namespace Zhiyi\Component\ZhiyiPlus\PlusComponentFeed\API2;

use Illuminate\Http\Request;
use Zhiyi\Plus\Http\Controllers\Controller;
use Illuminate\Contracts\Routing\ResponseFactory as ResponseContract;
use Zhiyi\Component\ZhiyiPlus\PlusComponentFeed\Models\Feed as FeedModel;

class LikeController extends Controller
{
    /**
     * Get feed likes.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Contracts\Routing\ResponseFactory $response
     * @param \Zhiyi\Component\ZhiyiPlus\PlusComponentFeed\Models\Feed $feed
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function index(Request $request, ResponseContract $response, FeedModel $feed)
    {
        $limit = $request->query('limit', 20);
        $after = $request->query('after', false);
        $likes = $feed->likes()
            ->when($after, function ($query) use ($after) {
                return $query->where('id', '<', $after);
            })
            ->limit($limit)
            ->orderBy('id', 'desc')
            ->get();

        return $response->json($likes)->setStatusCode(200);
    }

    /**
     * 用户点赞接口.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Contracts\Routing\ResponseFactory $response
     * @param \Zhiyi\Component\ZhiyiPlus\PlusComponentFeed\Models\Feed $feed
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function store(Request $request, ResponseContract $response, FeedModel $feed)
    {
        $user = $request->user();
        $like = $feed->like($user);

        if (! $feed->id) {
            return $response->json(['message' => ['操作失败']])->setStatusCode(500);
        }

        return $response->json(['message' => ['操作成功']])->setStatusCode(201);
    }

    /**
     * 取消动态赞.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Contracts\Routing\ResponseFactory $response
     * @param \Zhiyi\Component\ZhiyiPlus\PlusComponentFeed\Models\Feed $feed
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function destroy(Request $request, ResponseContract $response, FeedModel $feed)
    {
        $user = $request->user();
        $feed->unlike($user);

        return $response->json(['message' => ['操作成功']])->setStatusCode(204);
    }
}
