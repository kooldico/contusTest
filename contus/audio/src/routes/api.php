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

Route::group(['prefix' => 'medias/api/v2', 'namespace' => 'Contus\Audio\Api\Controllers\Frontend', 'middleware' => ['cors', 'updatedversion', 'jwt-auth:1', 'api.auth']], function () {
    
    Route::get('banner', 'AlbumController@getHomepageBanner');
    Route::get('homepage', 'AlbumController@getHomepageContentsNew');
    Route::get('homepage_more', 'AlbumController@getHomepageMoreContents');
    Route::get('see_all', 'AlbumController@getSeeAllHomepage');
    Route::get('audio_home_more', 'AlbumController@getSeeAllHomepage');
    Route::post('browse', 'AlbumController@browseAlbums');
    Route::get('artist_list', 'ArtistController@browseArtist');
    Route::get('album', 'AlbumController@getalbumDetailPageContents');

    Route::get('album-new', 'AlbumController@getalbumDetailPageContentsNew');
    
    Route::get('more_album_audio', 'AlbumController@getAlbumAudio');
    Route::get('album_tracks', 'AlbumController@getAlbumTracks');
    Route::get('artist', 'ArtistController@getartistDetailPageContents');
    Route::get('song', 'AudioController@fetchAudioDetails');
    Route::get('playlist_browse', 'PlaylistController@getAllPlaylist');
    Route::get('playlists_details', 'PlaylistController@getplaylistDetailPageContents');
    Route::post('related-albums-more', 'AlbumController@getmoreRelatedAlbums');
    Route::post('audio-play-history', 'AudioController@saveTracksPlayHistory');

    Route::get('search/audios', 'SearchController@searchAudios');
    Route::get('get_audio_Ads', 'AudioAdsController@getRandomAudioAds');
    Route::get('audio-languages', 'AudioLanguageController@getAudioLanguages');
    Route::get('site-languages', 'SiteLanguageController@getSiteLanguages');

    Route::get('search-data', 'AlbumController@getSearchData');
});
/** Routes to pass authorization */
Route::group(['prefix' => 'medias/api/v2', 'namespace' => 'Contus\Audio\Api\Controllers\Frontend', 'middleware' => ['cors', 'updatedversion', 'jwt-auth', 'api.auth']], function () {
    Route::get('album-favourite', 'FavouriteAlbumController@index');
    Route::post('album-favourite', 'FavouriteAlbumController@store');
    Route::put('album-favourite', 'FavouriteAlbumController@destroy');
    Route::delete('album-favourite', 'FavouriteAlbumController@destroy');
    //Favourite Playlist 
    Route::get('playlist-favourite', 'FavouritePlaylistController@index');
    Route::post('playlist-favourite', 'FavouritePlaylistController@store');
    Route::put('playlist-favourite', 'FavouritePlaylistController@destroy');
    Route::delete('playlist-favourite', 'FavouritePlaylistController@destroy');

    Route::get('audio-favourite', 'FavouriteAudiosController@index');
    Route::post('audio-favourite', 'FavouriteAudiosController@store');
    Route::put('audio-favourite', 'FavouriteAudiosController@destroy');
    Route::delete('audio-favourite', 'FavouriteAudiosController@destroy');

    Route::get('artist-favourite', 'FavouriteAlbumController@index');
    Route::post('artist-favourite', 'FavouriteAlbumController@store');
    Route::put('artist-favourite', 'FavouriteAlbumController@destroy');

    Route::post('customer_audio_playlist', 'CustomerAudioPlaylistController@store');
    Route::get('customer_audio_playlist', 'CustomerAudioPlaylistController@index');
    Route::delete('customer_audio_playlist', 'CustomerAudioPlaylistController@destroy');
    Route::get('customer_playlist_audios', 'CustomerAudioPlaylistController@fetchPlaylistAudios');
    Route::delete('customer_playlist_audios', 'CustomerAudioPlaylistController@deletePlaylistAudios');

    Route::get('audio-customer-history', 'AudioController@fetchAudioHistory');
    Route::post('clear_customer_audio_history', 'AudioController@clearHistory');

    Route::get('audio_customer_profile', 'AudioCustomerController@fetchprofilePageData');
});
