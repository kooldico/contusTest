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

class AlbumSearch extends Model{
     /**
     * The database table used by the model.
     *
     * @vendor Contus
     * @package Audio
     * @var string
     */
    protected $table = 'album_search';
    /**
     * Method to get the album language
     * 
     * @vendor Contus
     * @package Audio
     * @return string  
     */
    public function getAlbumNameAttribute($val){
        if (!is_null(app()->make('request')->header('X-LANGUAGE-CODE')) && app()->make('request')->header('X-LANGUAGE-CODE') == 'hi') {
            if($this->album_name_hindi){
                return $this->album_name_hindi;
            } else {
                return $val;
            }
        } else {
            return $val;
        }
        
    }

    public function getArtistNameAttribute($val){
        if (!is_null(app()->make('request')->header('X-LANGUAGE-CODE')) && app()->make('request')->header('X-LANGUAGE-CODE') == 'hi') {
            if($this->artist_name_hindi){
                return $this->artist_name_hindi;
            } else {
                return $val;
            }
        } else {
            return $val;
        }
        
    }

    public function getAlbumThumbnailAttribute($val){
        if($val){
            return rtrim(env('AWS_BUCKET_URL'),'/'). '/' . $val;
        }
        return $val;
        
    }
}
?>