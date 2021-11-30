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
namespace Contus\Video\Models;

use Contus\Base\Model;
use Contus\Base\Helpers\StringLiterals;

use Symfony\Component\HttpFoundation\File\File;
use Contus\Video\Models\Video;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class VideoAds extends Model  {

    /**
     * The database table used by the model.
     *
     * @vendor Contus
     *
     * @package Video
     * @var string
     */
    protected $table = 'video_ads';

    /**
     * Constructor method
     * sets hidden for customers
     */
    public function __construct() {
        parent::__construct ();
        $this->setHiddenCustomer ( [ 'updated_at','created_at','updator_id','creator_id','pivot' ] );
    }

    /**
     * funtion to automate operations while Saving
     */
    public function bootSaving() {
    }

    /**
     * Get File Information Model
     * the model related for holding the uploaded file information
     *
     * @vendor Contus
     *
     * @package Category
     * @return Contus\Video\Models\Category
     */
    public function getFileModel() {
        return $this;
    }

    public function ads() {
        return $this->belongsTo(Ads::class, 'ads_id', 'id');
    }

}
