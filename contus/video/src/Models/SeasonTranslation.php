<?php

/**
 * Season Model for seasons table in database
 *
 * @name Season
 * @vendor Contus
 * @package Video
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Models;

use Contus\Base\Model;
use Carbon\Carbon;
use Contus\Video\Models\Season;

class SeasonTranslation extends Model {
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'seasons_translation';
    protected $primaryKey = 'id';
    protected $connection = 'mysql';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'season_id','language_id', 'title'];
    protected $hidden = [ 'updated_at','created_at'];


    /**
     * Constructor method
     * sets hidden for customers
     */
    public function __construct() {
       
    }
    
    /**
     * Get the formated created date
     *
     * @return object
     */
    public function Season()
    {
        return $this->belongsTo(Season::class, 'season_id');
    }

}
