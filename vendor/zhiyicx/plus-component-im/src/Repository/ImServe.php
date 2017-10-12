<?php

namespace Zhiyi\Component\ZhiyiPlus\PlusComponentIm\Repository;

use Zhiyi\Plus\Models\CommonConfig;
use Illuminate\Contracts\Cache\Repository as ContractsCacheRepository;

class ImServe
{
    /**
     * Cache store.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * Create the im serve reposttory instance.
     *
     * @param \Illuminate\Contracts\Cache\Repository $cache
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function __construct(ContractsCacheRepository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get the im serve.
     *
     * @return string
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function get(): string
    {
        if ($this->cache->has($this->cacheKey())) {
            return $this->cache->get($this->cacheKey());
        }

        $model = CommonConfig::firstOrCreate(
            ['name' => 'im:serve', 'namespace' => 'common'],
            ['value' => '120.0.0.1:9900']
        );

        $this->cache->forever($this->cacheKey(), $serve = strval($model->value));

        return $serve;
    }

    /**
     * Save the im serve.
     *
     * @param string $serve
     * @return void|null
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function store(string $serve)
    {
        CommonConfig::updateOrCreate(
            ['name' => 'im:serve', 'namespace' => 'common'],
            ['value' => $serve]
        );

        $this->flush();
        $this->cache->forever($this->cacheKey(), $serve);
    }

    /**
     * Get the config cache key.
     *
     * @return string
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function cacheKey(): string
    {
        return 'im:serve';
    }

    /**
     * Flush all cache.
     *
     * @return void
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function flush()
    {
        $keys = [
            $this->cacheKey(),
            'bootstrappers',
        ];

        foreach ($keys as $key) {
            $this->cache->forget($key);
        }
    }
}
