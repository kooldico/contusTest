<?php
/**
 * Albums Model
 *
 * Audio album management related model
 *
 * @name Albums
 * @version 1.0
 * @author Contus Team <developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Models;

use Contus\Base\Model;
use Contus\Audio\Scopes\ActiveRecordScope;
use Contus\Audio\Scopes\AlbumHasTracksScope;
use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Carbon\Carbon;
use Contus\Audio\Models\Audios;
use Contus\Audio\Models\Artist;
use Contus\Audio\Models\FavouriteAlbum;
use Contus\Audio\Models\AudioLanguageCategory;

use Contus\Audio\Traits\AlbumTrait;

class Albums extends Model{
    use HybridRelations, AlbumTrait;
     /**
     * The database table used by the model.
     *
     * @vendor Contus
     * @package Audio
     * @var string
     */
    protected $table = 'audio_albums';

    protected $appends = ['is_favourite', 'artist_name', 'artist_slug', 'language'];
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
        static::addGlobalScope(new AlbumHasTracksScope);
    }

    /**
     * Constructor method
     * sets hidden for customers
     */
    public function __construct()
    {
        parent::__construct();
        $this->setHiddenCustomer([ 'creator_id', 'updator_id']);
    }

    /**
     * Method to get the formated released date
     *
     * @vendor Contus
     * @package Audio
     * @return object
     */
    public function getAlbumReleaseDateAttribute($value){
        return  Carbon::parse($value)->format('M d Y');
    }
    public function getPassphraseAttribute() {
        $referer        = app()->request->header('Referer');
        $TitleWithTime  = $time = '';

        if($referer == env('DOWNLOAD_REFERER') || isWebsite()){
            $time = time();
            $TitleWithTime = cryptoJsAesEncrypt($time);
        }
        return $TitleWithTime;
    }
    /**
     * Method to get the albums repsective tracks
     * 
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function albumTracks(){
        return $this->hasMany(Audios::class,'album_id','id');
    }
    /**
     * Method to get the albums artists
     * 
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function albumArtist(){
        return $this->belongsTo(Artist::class, 'album_artist_id', 'id');
    }
    /**
     * Method to get the albums language
     * 
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function albumLanguage(){
        return $this->belongsTo(AudioLanguageCategory::class,'audio_language_category_id');
    }
    /**
     * Method to get the favourite albums of a customer
     * 
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function customerFavouriteAlbums(){
        return $this->hasMany(FavouriteAlbum::class,'album_id','id');
    }
    /**
     * Method to set favourite flag for album list
     * 
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Database\Eloquent\Builder    
     */
    public function getIsFavouriteAttribute(){
        $info = $this->customerFavouriteAlbums()->where('customer_id', !empty(authUser()->id) ? authUser()->id : 0)->count();
        return ($info > 0) ? 1 : 0;
    }

    /**
     * Method to get album audios
     * 
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Database\Eloquent\Builder    
     */
    public function getAlbumTracksAttribute(){
        return $this->albumTracks()->paginate(10);
    }
    /**
     * Method to get the album thumbnail full URL
     * 
     * @vendor contus
     * @package Audio
     * @return string
     */
    public function getAlbumThumbnailAttribute($value){
        return $this->getAudiosPkgThumbnailImageAttributes($value, 'album');
    }
    /**
     * Method to get the album artist slug
     * 
     * @vendor Contus
     * @package Audio
     * @return string  
     */
    public function getArtistSlugAttribute(){
        $artistData = $this->albumArtist()->first();
        return (!empty($artistData))?$artistData->slug:'';
    }
    /**
     * Method to get the album language
     * 
     * @vendor Contus
     * @package Audio
     * @return string  
     */
    public function getLanguageAttribute(){
        $languageData = $this->albumLanguage()->first();
        return (!empty($languageData))?$languageData->language_name:'';
    }
}
?>