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
use Contus\Video\Models\VideoSeason;
use Carbon\Carbon;
use Contus\Video\Models\SeasonTranslation;

class Season extends Model {
    /**
     * The database table used by the model.
     *
     * @var string
     */
     protected $table = 'seasons';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'title','is_active'];

    public function __construct() {
        parent::__construct ();
        $this->setHiddenCustomer([ 'is_active','created_at', 'updated_at','pivot' ]);
    }

    public function videoSeason () {
        return $this->hasMany(VideoSeason::class, 'season_id', 'id');
    }

    public function SeasonTranslation()
    {
        return $this->hasMany(SeasonTranslation::class,'season_id');
    }

    public function getTitleAttribute($value) {
        $trans = $this->SeasonTranslation()->where('language_id', $this->fetchLanugageId())->first();
        if(!empty($trans)) {
            return $trans->title;
        }
        return $value;
    }
}
