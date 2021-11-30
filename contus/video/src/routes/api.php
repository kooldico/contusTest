<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */
Route::group(['prefix' => 'medias/api/v2', 'namespace' => 'Contus\Video\Api\Controllers\Frontend', 'middleware' => ['cors', 'updatedversion', 'jwt-auth:1', 'api.auth']], function () {
    Route::post('videos/{slug}', 'VideoController@browseVideo');
     Route::get('drm_token', 'VideoController@getdrmtoken');
    Route::get('getVideoId/{slug}', 'VideoController@getVideoId');
    Route::get('video-metadata/{slug}', 'VideoController@getMetaData');
    Route::get('videos/{slug}/{playlist_id}', 'VideoController@browseVideo');
    Route::get('watchvideo/{slug}', 'VideoDRMController@browseWatchVideo');
    Route::get('webseason_videos/{slug}/{season}', 'VideoController@browseWebseriesSeasonVideo');
    Route::post('tvod_view_count', 'VideoController@saveTvodViewCount');
    Route::get('replyComments/{id}', 'VideoController@replyVideocomments');
    Route::post('videosRelatedTrending', 'VideoController@browseRelatedTrendingVideos');
    Route::get('season_videos/{slug}/{season}', 'VideoController@browseSeasonVideo');
    Route::get('key', 'VideoDRMController@getKey');
    Route::post('recommended_videos', 'VideoController@recommendedVideos');
    Route::get('video/cast/{slug}', 'VideoController@castList');
    Route::get('videos/update_video_url', 'VideoController@updateVideosUrl');
    Route::post('heart-beat', 'VideoController@postHeartBeat');
    Route::post('stop-heart-beat', 'VideoController@postStopHeartBeat');
    Route::get('can-download-video', 'VideoController@canCustomerDownloadVideo');
     Route::get('watchtrailer/{slug}', 'VideoController@fetchTrailerData');
});

Route::group(['prefix' => 'medias/api/v2', 'namespace' => 'Contus\Video\Api\Controllers\Frontend'], function () {
    Route::group(['middleware' => ['cors', 'updatedversion', 'api', 'jwt-auth', 'api.auth']], function () {
        Route::get('clearallcache', 'CategoryController@clearAllCache');
        Route::post('clear_recent_view', 'VideoController@clearView');
        Route::post('open/{id}', 'VideoController@downloadUrl');
        Route::get('continue_watching', 'VideoController@fetchContinueWatchingVideos');
    });

    Route::group(['middleware' => ['cors', 'updatedversion', 'api', 'jwt-auth:1']], function () {
        Route::get('category_list', 'CategoryController@categoryList');
        Route::get('category_list_all', 'CategoryController@categoryListAll');
    });
});

Route::group(['prefix' => 'medias/api/v2', 'namespace' => 'Contus\Video\Api\Controllers\Frontend'], function () {
    Route::group(['middleware' => ['cors', 'updatedversion', 'api', 'jwt-auth:1', 'api.auth']], function () {
        Route::get('livevideos', 'VideoController@browseAllLiveVideos');
        Route::get('live_more_videos', 'VideoController@browseMoreLiveVideos')->middleware('cacheable');
        Route::get('home_page', 'VideoController@getHome')->middleware('cacheable');
        Route::get('home_page_banner', 'VideoController@fetchHomePageBanner');
        Route::get('home_more', 'VideoController@getMoreVideos')->middleware('cacheable');
        Route::post('playlist', 'PlaylistController@browseCategoryPlaylist');
        Route::get('playlist', 'PlaylistController@browseCategoryPlaylist');
        Route::post('videos', 'VideoController@browseVideos');
        Route::get('category_videos', 'VideoController@fetchCategoryVideos');
        Route::get('home_category_videos', 'VideoController@fetchCategoryVideos')->middleware('cacheable');
        Route::get('series_videos', 'VideoController@fetchSeriesVideos');
        Route::get('more_category_videos', 'VideoController@fetchMoreCategoryVideos')->middleware('cacheable');

        /**
         * Web series based APIS
         */
        Route::get('parentWebseriesList', 'CategoryController@parentWebseriesList'); // Get parent web series
        Route::get('allWebseries', 'CategoryController@getAllWebseries'); // Get all web series
        Route::get('webseries/{slug}', 'CategoryController@browseWebseries'); // Get detail of he web series
        Route::get('childWebseries/{slug}', 'CategoryController@browseChildWebseries'); // Get all child web series based on parent(is_webseries = true)
        Route::get('more_child_webseries', 'CategoryController@fetchMoreWebseries'); // Get the genre based web series based on the pagination

        // CLEAR CACHE
        Route::get('cache_clear', 'VideoControllerq@clearData');
        Route::get('video/get_user_id', 'VideoController@getCurrentUserInfo');
        Route::get('video/get_watermark', 'VideoController@getwatermarkOpacity');
           });
});

Route::group(['middleware' => ['cors']], function () {
    Route::get('health-check', function () {
        return 'Media service is up!';
    });
});
