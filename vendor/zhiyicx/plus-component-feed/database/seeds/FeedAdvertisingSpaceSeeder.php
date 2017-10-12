<?php

use Illuminate\Database\Seeder;
use Zhiyi\Plus\Models\AdvertisingSpace;

class FeedAdvertisingSpaceSeeder extends Seeder
{
    public function run()
    {
        AdvertisingSpace::create(['channel' => 'feed', 'space' => 'feed:list:top', 'alias' => '动态列表顶部广告', 'allow_type' => 'image']);
        AdvertisingSpace::create(['channel' => 'feed', 'space' => 'feed:single', 'alias' => '动态详情广告', 'allow_type' => 'image']);
    }
}
