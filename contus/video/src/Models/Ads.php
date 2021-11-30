<?php

/**
 * Ads Models.
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
use Contus\Video\Scopes\ActiveRecordScope;

class Ads extends Model  {

    /**
     * The database table used by the model.
     *
     * @vendor Contus
     *
     * @package Video
     * @var string
     */
    protected $table = 'ads';

    /**
     * The attributes that are mass assignable.
     *
     * @vendor Contus
     *
     * @package Video
     * @var array
     */
    protected $fillable = [ 'title',StringLiterals::ISACTIVE ];
    /**
     * The attribute will used to generate url
     *
     * @var array
     */

    /**
     * Constructor method
     * sets hidden for customers
     */
    public function __construct() {
        parent::__construct ();
        $this->setHiddenCustomer ( [ 'id','is_active','image_path','is_deletable','is_leaf_category','level','parent_id','updated_at','created_at','updator_id','creator_id','pivot' ] );
    }

    /**
     * funtion to automate operations while Saving
     */
    public function bootSaving() {
        $this->setDynamicSlug ( 'title', 'slug' );
        $keysArray = array('category_listing_page','dashboard_categories','dashboard_exams','dashboard_categorynave');
        $this->clearCache($keysArray);
        Cache::forget ( 'relatedCategoryList' . $this->slug );
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



    /**
    * Get the formated created date
    *
    * @return object  
    */
    public function getFormattedCreatedDateAttribute()
    {
        return Carbon::parse($this->created_at)->format('M d Y');
    }


}
