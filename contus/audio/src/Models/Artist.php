<?php

/**
 * Categories Models.
 *
 * @name Categories
 * @vendor Contus
 * @package Video
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Models;

use Contus\Base\Model;
use Contus\Audio\Scopes\ActiveRecordScope;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Contus\Audio\Models\Audios;
use Contus\Audio\Traits\ArtistTrait;
use Contus\Audio\Models\Albums;

class Artist extends Model{
    use ArtistTrait;
    /**
     * The database table used by the model.
     *
     * @vendor Contus
     *
     * @package Video
     * @var string
     */
    protected $table = 'audio_artists';
    /**
     * The attributes that are mass assignable.
     *
     * @vendor Contus
     *
     * @package Video
     * @var array
     */
    protected $fillable = ['artist_name', 'artist_thumbnail', 'artist_biography', 'is_active'];
    
    /**
     * Constructor method
     * sets hidden for customers
     */
    public function __construct(){
        parent::__construct();
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
     * Method to get audios tracks related to artists
     * 
     * @vendor contus
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function artistTracks(){
        return $this->belongsTo(Audios::class, 'id', 'audio_artist_id');
    }
    /**
     * Method to get the favourite artist of a customer
     * 
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function customerFavouriteArtists(){
        return $this->hasMany(FavouriteArtist::class,'artist_id','id');
    }
    /**
     * Method to set favourite flag for album list
     * 
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Database\Eloquent\Builder    
     */
    public function getIsFavouriteAttribute(){
        $info = $this->customerFavouriteAritst()->where('customer_id', !empty(authUser()->id) ? authUser()->id : 0)->count();
        return ($info > 0) ? 1 : 0;
    }
    /**
     * Method to get artist tracks with pagination
     * 
     * @vendor Contus
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getArtistTracksAttribute(){
        return $this->artistTracks()->paginate(config('contus.audio.audio.record_per_page'));
    }
    /**
     * Method to get the aritst thumbnail full URL
     * 
     * @vendor contus
     * @package Audio
     * @return string
     */
    public function getArtistThumbnailAttribute($value){
        return $this->getAudiosPkgThumbnailImageAttributes($value, 'artist');
    }

    public function albumTracks(){
        return $this->hasMany(Albums::class,'album_artist_id','id');
    }
}
