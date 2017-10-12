<?php

namespace Zhiyi\Component\ZhiyiPlus\PlusComponentFeed\Repository;

use Carbon\Carbon;
use Zhiyi\Plus\Models\FileWith as FileWithModel;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Zhiyi\Component\ZhiyiPlus\PlusComponentFeed\Models\Feed as FeedModel;

class Feed
{
    protected $model;

    /**
     * Cache repository.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    protected $dateTime;

    /**
     * Create the cash type respositorie.
     *
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function __construct(CacheContract $cache, FeedModel $model, Carbon $dateTime)
    {
        $this->cache = $cache;
        $this->model = $model;
        $this->dateTime = $dateTime;
    }

    /**
     * Find feed.
     *
     * @param int $id
     * @param array $columns
     * @return Zhiyi\Component\ZhiyiPlus\PlusComponentFeed\Models\Feed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function find($id, $columns = ['*'])
    {
        return $this->model = $this->model->findOrFail($id, $columns);
    }

    /**
     * Feed images.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function images()
    {
        $this->model->setRelation('images', $this->cache->remember(sprintf('feed:%s:images', $this->model->id), $this->dateTime->copy()->addDays(7), function () {
            $this->model->load([
                'images',
                'images.paidNode',
            ]);

            return $this->model->images;
        }));

        return $this->model->images;
    }

    /**
     * preview likes.
     *
     * @return Zhiyi\Component\ZhiyiPlus\PlusComponentFeed\Models\Feed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function previewLike()
    {
        $minutes = $this->dateTime->copy()->addDays(1);
        $cacheKey = sprintf('feed:%s:preview-likes', $this->model->id);

        return $this->model->setRelation('likes', $this->cache->remember($cacheKey, $minutes, function () {
            if (! $this->model->relationLoaded('likes')) {
                $this->model->load(['likes' => function ($query) {
                    $query->limit(8)->orderBy('id', 'desc');
                }]);
            }

            return $this->model->likes;
        }));
    }

    /**
     * Format feed data.
     *
     * @param int $user
     * @return Zhiyi\Component\ZhiyiPlus\PlusComponentFeed\Models\Feed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function format(int $user = 0): FeedModel
    {
        $this->model->setRelation('images', $this->model->images->map(function (FileWithModel $item) use ($user) {
            $image = [
                'file' => $item->id,
                'size' => $item->size,
            ];
            if ($item->paidNode !== null) {
                $image['amount'] = $item->paidNode->amount;
                $image['type'] = $item->paidNode->extra;
                $image['paid'] = $item->paidNode->paid($user);
                $image['paid_node'] = $item->paidNode->id;
            }

            return $image;
        }));

        // 动态收费
        if ($this->model->paidNode !== null) {
            $paidNode = [
                'paid' => $this->model->paidNode->paid($user),
                'node' => $this->model->paidNode->id,
                'amount' => $this->model->paidNode->amount,
            ];
            unset($this->model->paidNode);
            $this->model->paid_node = $paidNode;

            // 动态内容截取
            if (! $this->model->paid_node['paid'] && $this->model->user_id != $user) {
                $this->model->feed_content = str_limit($this->model->feed_content, 100, '');
            }
        }

        return $this->model;
    }

    /**
     * Set feed model.
     *
     * @param Zhiyi\Component\ZhiyiPlus\PlusComponentFeed\Models\Feed $model
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function setModel(FeedModel $model)
    {
        $this->model = $model;

        return $this;
    }

    public function forget($key)
    {
        $this->cache->forget($key);
    }
}
