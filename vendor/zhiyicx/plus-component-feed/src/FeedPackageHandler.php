<?php

namespace Zhiyi\Component\ZhiyiPlus\PlusComponentFeed;

use Zhiyi\Plus\Models\File;
use Zhiyi\Plus\Models\Storage;
use Illuminate\Console\Command;
use Zhiyi\Plus\Models\FileWith;
use Illuminate\Support\Facades\DB;
use Zhiyi\Plus\Support\PackageHandler;
use Zhiyi\Component\ZhiyiPlus\PlusComponentFeed\Models\FeedStorage;

class FeedPackageHandler extends PackageHandler
{
    /**
     * Resolve handler.
     *
     * @param \Illuminate\Console\Command $command
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function resolveHandle($command)
    {
        // publish public assets
        $command->call('vendor:publish', [
            '--provider' => FeedServiceProvider::class,
            '--tag' => 'public',
            '--force' => true,
        ]);

        // Run the database migrations
        $command->call('migrate');

        if ($command->confirm('Run seeder')) {
            // Run the database seeds.
            $command->call('db:seed', [
                '--class' => \FeedDatabaseAllSeeder::class,
            ]);
        }
    }

    /**
     * Create a migration file.
     *
     * @param \Illuminate\Console\Command $command
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function createMigrationHandle(Command $command)
    {
        $path = str_replace(app('path.base'), '', dirname(__DIR__).'/database/migrations');
        $table = $command->ask('Enter the table name');
        $prefix = $command->ask('Enter the table migration prefix', 'create');
        $name = sprintf('%s_%s_table', $prefix, $table);
        $create = $command->confirm('Is it creating a new migration');

        return $command->call('make:migration', [
            'name' => $name,
            '--path' => $path,
            '--table' => $table,
            '--create' => $create,
        ]);
    }

    public function checkstorageHandle($command)
    {
        if ($command->confirm('This will change your datas with new storages')) {
            // 动态
            $storages = FeedStorage::with('feed')->get();
            foreach ($storages as $storage) {
                $this->checkFileId($storage->feed_storage_id, 'feed:image', $storage->feed->id, $storage->feed->user_id);
            }
        }
        $command->info('have done');
    }

    protected function checkFileId($storage_id, $channel, $data_id, $user_id = 1)
    {
        $info = Storage::where('id', $storage_id)->first(); // 附件迁移
        $hasMove = FileWith::where('id', $storage_id)->where('channel', $channel)->where('raw', $data_id)->first();  // 已经迁移的不再处理
        if ($info && (! $hasMove)) {
            $file = File::where('hash', $info->hash)->first();
            if (! $file) {
                $file = new File();
                $file->hash = $info->hash;
                $file->origin_filename = $info->origin_filename;
                $file->filename = $info->filename;
                $file->mime = $info->mime;
                $file->width = $info->image_width;
                $file->height = $info->image_height;
                $file->save();
            }
            if (empty($file->id)) {
                return $storage_id;
            }

            $filewith = new FileWith();
            $filewith->file_id = $file->id;
            $filewith->user_id = $user_id;
            $filewith->channel = $channel;
            $filewith->raw = $data_id;
            $filewith->size = ($size = sprintf('%sx%s', $file->width, $file->height)) === 'x' ? null : $size;
            $filewith->save();

            return $filewith->id; // 迁移生成成功 返回filewithid
        }

        return $storage_id; // 查找失败暂时原样返回
    }
}
