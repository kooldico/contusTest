<?php
/**
 * AudioAds Model
 *
 * AudioAds management related model
 *
 * @name AudioAds
 * @version 1.0
 * @author Contus Team <developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Models;

use Contus\Base\Model;
use Contus\Audio\Scopes\ActiveRecordScope;

class AudioAds extends Model{
     /**
     * The database table used by the model.
     *
     * @vendor Contus
     *
     * @package Video
     * @var string
     */
    protected $table = 'audio_ads';
    protected $connection = 'mysql';

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
     * Constructor method
     * sets hidden for customers
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Method to get the formated Audio Ad
     *
     * @vendor Contus
     * @return object
     */
    public function getHlsPlaylistUrlAttribute($value)
    {   
        return (!empty($value)) ? env('AWS_BUCKET_URL') . $value : '';
    }

    /**
     * Method to get the formated Ad Thumbnail
     *
     * @vendor Contus
     * @return object
     */
    public function getAudioThumbnailAttribute($value)
    {   
        return (!empty($value)) ? env('AWS_BUCKET_URL') . $value : '';
    }
}
?>