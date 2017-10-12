<?php

namespace Zhiyi\Component\ZhiyiPlus\PlusComponentFeed\API2;

use Illuminate\Http\Request;
use Zhiyi\Plus\Http\Controllers\Controller;
use Illuminate\Contracts\Routing\ResponseFactory as ResponseContract;
use Zhiyi\Component\ZhiyiPlus\PlusComponentFeed\Models\Feed as FeedModel;
use Zhiyi\Component\ZhiyiPlus\PlusComponentFeed\Repository\Feed as FeedRepository;

class FeedCollectionController extends Controller
{
    /**
     * 收藏动态.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Contracts\Routing\ResponseFactory $response
     * @param \Zhiyi\Component\ZhiyiPlus\PlusComponentFeed\Models\Feed $feed
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function store(Request $request, ResponseContract $response, FeedModel $feed)
    {
        $user = $request->user()->id;

        if ($feed->collected($user)) {
            return $response->json(['message' => ['已经收藏过']])->setStatusCode(422);
        }

        $feed->collect($user);

        return $response->json(['message' => ['收藏成功']])->setStatusCode(201);
    }

    /**
     * 取消收藏.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Contracts\Routing\ResponseFactory $response
     * @param \Zhiyi\Component\ZhiyiPlus\PlusComponentFeed\Models\Feed $feed
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function destroy(Request $request, ResponseContract $response, FeedModel $feed)
    {
        $feed->uncollect(
            $request->user()->id
        );

        return $response->json(null, 204);
    }

    public function list(Request $request, FeedModel $feedModel, ResponseContract $response, FeedRepository $repository)
    {
        $user = $request->user();
        $current_user = $request->query('user', $user->id);
        $limit = $request->query('limit', 20);
        $after = $request->query('after');

        $feeds = $feedModel->where(function ($query) use ($after) {
            if ($after) {
                $query->where('feeds.id', '<', $after);
            }
        })->join('feed_collections', function ($join) use ($current_user) {
            $join->on('feed_collections.feed_id', '=', 'feeds.id')->where('feed_collections.user_id', $current_user);
        })
        ->select('feeds.*')
        ->orderBy('feeds.id', 'desc')
        ->limit($limit)
        ->get();

        return $response->json($feedModel->getConnection()->transaction(function () use ($feeds, $repository, $user) {
            return $feeds->map(function (FeedModel $feed) use ($repository, $user) {
                $repository->setModel($feed);
                $repository->images();
                $repository->format($user->id);

                $feed->has_collect = $feed->collected($user->id);
                $feed->has_like = $feed->liked($user->id);
                $feed->comments = $feed->comments()->orderBy('id', 'desc')->limit(5)->get();

                return $feed;
            });
        }))->setStatusCode(200);
    }
}
