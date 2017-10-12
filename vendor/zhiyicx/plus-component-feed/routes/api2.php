<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/feeds')->group(function () {

    // 动态
    Route::get('/', 'FeedController@index');
    Route::get('/{feed}', 'FeedController@show')->where(['feed' => '[0-9]+']);

    // 评论
    Route::get('/{feed}/comments', 'FeedCommentController@index');
    Route::get('/{feed}/comments/{comment}', 'FeedCommentController@show');

    /*
     * 需要授权的路由
     */
    Route::middleware('auth:api')->group(function () {

        // 动态
        Route::post('/', 'FeedController@store');
        Route::delete('/{feed}', 'FeedController@destroy');
        Route::patch('/{feed}/comment-paid', 'FeedPayController@commentPaid');

        // 评论
        Route::post('/{feed}/comments', 'FeedCommentController@store');
        Route::delete('/{feed}/comments/{comment}', 'FeedCommentController@destroy');

        // 点赞（喜欢）
        Route::post('/{feed}/diggs', 'FeedDiggController@store');
        Route::delete('/{feed}/undigg', 'FeedDiggController@destroy');

        // 收藏
        Route::get('/collections', 'FeedCollectionController@list');
        Route::post('/{feed}/collections', 'FeedCollectionController@store');
        Route::delete('/{feed}/uncollect', 'FeedCollectionController@destroy');

        // 固定
        Route::post('/{feed}/pinneds', 'PinnedController@feedPinned');
        Route::post('/{feed}/comments/{comment}/pinneds', 'PinnedController@commentPinned');
        Route::patch('/{feed}/comments/{comment}/pinneds/{pinned}', 'CommentPinnedController@pass');
        Route::delete('/{feed}/comments/{comment}/unpinned', 'CommentPinnedController@delete');

        // 点赞
        Route::post('/{feed}/like', 'LikeController@store');
        Route::delete('/{feed}/unlike', 'LikeController@destroy');
        Route::get('/{feed}/likes', 'LikeController@index');
    });
});

/*
 * 需要授权的非 feeds 资源路由.
 */
Route::middleware('auth:api')->group(function () {

    // 评论固定审核
    Route::get('/user/feed-comment-pinneds', 'CommentPinnedController@index');
    Route::delete('/user/feed-comment-pinneds/{pinned}', 'CommentPinnedController@reject');
});
