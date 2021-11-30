<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Audio Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines used to display in audio related management
    |
     */
    'empty_request_varaible' => 'Request is empty',
    'favourite_audios' => [
        'add' => [
            'success' => 'Audio has been successfully added as favourite',
            'error' => 'Error occured in adding to favourite',
        ],
        'delete' => [
            'success' => 'Audio successfully removed from favourite list',
            'error' => 'Error occured in deleting from favourite',
        ],
        'fetch_record' => [
            'success' => 'Favourite list successfully fetched',
            'error' => 'Error occured in fetching the favourite list',
        ],
    ],
    'audio_playlists' => [
        'playlist_already_exists' => 'Playlist already exists',
        'playlist_not_exists' => 'Playlist does not exists',
        'audio_not_exists' => 'Audio does not exists',
        'add' => [
            'success' => 'Playlist successfully created',
            'error' => 'Error occured while creating playlist',
        ],
        'update' => [
            'success' => 'Playlist successfully updated',
            'error' => 'Error occured while updating the playlist',
        ],
        'delete' => [
            'success' => 'Playlist successfully deleted',
            'error' => 'Error occured while deleting the playlist',
        ],
        'fetch_record' => [
            'success' => 'Playlist successfully fetched',
            'error' => 'Error occured in fetching the playlist',
        ],
        'delete_audios' => [
            'success' => 'Audio successfully removed from the playlist',
            'error' => 'Error occured in removing the audio from the playlist',
        ],
    ],
    'audio_language' => [
        'fetch' => [
            'success' => 'Audio languages successfully fetched',
            'error' => 'Error occured in fetching the audio languages',
        ],
    ],
    'site_language' => [
        'fetch' => [
            'success' => 'Site languages successfully fetched',
            'error' => 'Error occured in fetching the site languages',
        ],
    ],
    'audio_play_history' => [
        'add' => [
            'success' => 'Audio history successfully tracked',
            'error' => 'Error occured in tracking the audio history',
        ],
        'fetch' => [
            'success' => 'Customer audio history successfully fetched',
            'error' => 'Error occured in fetching the customer audio history',
        ],
        'clear_history' => [
            'success' => 'Customer audio history successfully cleared',
            'error' => 'Error occured in clearing the customer audio history',
        ],
    ],

];
