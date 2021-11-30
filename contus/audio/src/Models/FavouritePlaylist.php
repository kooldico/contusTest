<?php

/**
 * Favourite Album Model.
 *
 * @name FavouriteAlbum
 * @vendor Contus
 * @package Audio
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Models;

use Contus\Base\MongoModel;
use Contus\Base\Model;
use Contus\Audio\Models\Playlist;

class FavouritePlaylist extends MongoModel{
    protected $collection = 'favourite_playlist';
    protected $connection = 'mongodb';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'playlist_id', 'customer_id', 'created_at', 'updated_at'
    ];
    /**
     * Method to get the favourite tracks of the customer
     * 
     * @vendor Contus
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function favouritePlaylist(){
        return $this->belongsTo(Playlist::class);
    }

}