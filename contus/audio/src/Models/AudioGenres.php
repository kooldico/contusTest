<?php
/**
 * Audio Genres Models.
 *
 * @name Audio Genres
 * @vendor Contus
 * @package Audio
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2019 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Models;

use Carbon\Carbon;
use Contus\Base\Model;
use Contus\Audio\Models\Albums;

class AudioGenres extends Model{
    /**
     * The database table used by the model.
     *
     * @vendor Contus
     *
     * @package Audio
     * @var string
     */
    protected $table = 'audio_genres';
    protected $hidden = ['creator_id', 'updated_at', 'updator_id'];

    public function albums(){
        return $this->hasMany(Albums::class,'genre_id','id');
    }
}
