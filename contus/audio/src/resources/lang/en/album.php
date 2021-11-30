<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Album Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines used to display in album management
    |
 */
    'record_fetched_success' => 'Records successfully fetched',
    'record_fetched_error' => 'Error in fetching records',
    '404_slug_response' =>"This record does not exist",
    'favourite_albums' =>[
        'add' => [
            'success' => 'Album has been successfully added as favourite',
            'error' => 'Error occured in adding to favourite',
        ],
        'delete' => [
            'success' => 'Album has been successfully deleted from favourite',
            'error' => 'Error occured in deleting from favourite',
        ],
        'fetch_record' => [
            'success' => 'Favourite list successfully fetched',
            'error' => 'Error occured in fetching the favourite list',
        ]
    ],
];