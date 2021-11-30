<?php
/**
 * Audio customer trait
 *
 * To manage the functionalities related to the Customer audios and albums
 *
 * @name AudioCustomerTrait
 * @vendor Contus
 * @package Audio
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Traits;

use Contus\Audio\Models\Audios;
use Contus\Audio\Models\Albums;
use Contus\Audio\Models\FavouriteAudio;
use Contus\Audio\Models\FavouriteAlbum;
use Contus\Audio\Models\FavouritePlaylist;

trait AudioCustomerTrait{
    /**
     * Method for BelongsToMany relationship between audio and favourite_audios
     *
     * @vendor Contus
     * @package Customer
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function audioFavourites(){
        return $this->belongsToMany(FavouriteAudio::class,'favourite_audios','customer_id','audio_id');
    }
    /**
     * Method for BelongsToMany relationship between albums and favourite_albums
     *
     * @vendor Contus
     * @package Customer
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function albumFavourites(){
        return $this->belongsToMany(FavouriteAlbum::class, 'favourite_albums', 'customer_id', 'album_id');
    }
    /**
     * Method for BelongsToMany relationship between artist and favourite_artist
     *
     * @vendor Contus
     * @package Customer
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function artistFavourites(){
        return $this->belongsToMany(FavouriteArtist::class, 'favourite_artist', 'customer_id', 'artist_id');
    }
    /**
     * Method for BelongsToMany relationship between playlist and favourite_playlist
     *
     * @vendor Contus
     * @package Customer
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function playlistFavourites(){
        return $this->belongsToMany(FavouritePlaylist::class, 'favourite_playlist', 'customer_id', 'playlist_id');
    }
}