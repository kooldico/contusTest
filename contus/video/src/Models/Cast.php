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

class Cast extends Model  {

    /**
     * The database table used by the model.
     *
     * @vendor Contus
     *
     * @package Video
     * @var string
     */
    protected $table = 'x_ray_cast';
    protected $hidden = array('pivot');
    /**
     * The attribute will used to generate url
     *
     * @var array
     */
    protected $url = [ 'banner_image' ];

    /**
     * The attributes that are mass assignable.
     *
     * @vendor Contus
     *
     * @package Video
     * @var array
     */
    protected $fillable = [ 'name','description','banner_image','external_url','is_active'];

    public $timestamps = true;

    /**
     * Constructor method
     * sets hidden for customers
     */
    public function __construct() {
      
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
