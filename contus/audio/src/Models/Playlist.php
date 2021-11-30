<?php

/**
 * Playlists Models.
 *
 * @name Playlists
 * @vendor Contus
 * @package Audio
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Models;

use Carbon\Carbon;
use Contus\Audio\Models\Playlist;
use Contus\Audio\Scopes\ActiveRecordScope;
use Contus\Base\Contracts\AttachableModel;
use Contus\Base\Model;
use Contus\Audio\Models\FavouritePlaylist;
use Contus\Audio\Models\AudioLanguageCategory;
use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Contus\Audio\Traits\AlbumTrait;
use Symfony\Component\HttpFoundation\File\File;

class Playlist extends Model 
{
    use HybridRelations;
    /**
     * The database table used by the model.
     *
     * @vendor Contus
     *
     * @package Audio
     * @var string
     */
    protected $table = 'audio_admin_playlists';
    protected $appends = ['is_favourite'];

    /**
     * Constructor method
     * sets hidden for customers
     */
    public function __construct()
    {
        parent::__construct();
        $this->setHiddenCustomer(['updated_at', 'created_at']);
    }
    /**
     * The "booting" method of the model.
     *
     * @vendor Contus
     * @package Audio
     * @return void
     */
    protected static function boot(){
        parent::boot();
        static::addGlobalScope(new ActiveRecordScope);
    }
    /**
     * Method to get the formated Playlist Thumbnail
     *
     * @vendor Contus
     * @return object
     */
    public function getPlaylistThumbnailAttribute($value)
    {
        return (!empty($value)) ? env('AWS_BUCKET_URL') . $value : '';
    }
   
    /**
     * Method to fetch playlist audios
     * 
     * @vendor Contus
     * 
     * @package Audios
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function playlistAudios(){
        return $this->belongsToMany(Audios::class,'audio_admin_playlist_tracks','playlist_id','audio_id');
    }

    /**
     * Method to get the favourite playlist of a customer
     * 
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function customerFavouritePlaylist(){
        return $this->hasMany(FavouritePlaylist::class,'playlist_id','id');
    }
    /**
     * Method to set favourite flag for album list
     * 
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Database\Eloquent\Builder    
     */
    public function getIsFavouriteAttribute(){
        $info = $this->customerFavouritePlaylist()->where('customer_id', !empty(authUser()->id) ? authUser()->id : 0)->count();
        return ($info > 0) ? 1 : 0;
    }

}
