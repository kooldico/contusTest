<?php

/**
 * This is for favourite videos  for favourite_videos table in database
 *
 * @name       favouritevideos
 * @vendor     Contus
 * @package    Video
 * @version    1.0
 * @author     Contus<developers@contus.in>
 * @copyright  Copyright (C) 2016 Contus. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Models;

use Contus\Base\Model;
use Contus\Video\Models\Category;
use Contus\Video\Models\Collection;
use Contus\Video\Models\Video;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
class FavouriteVideo extends Eloquent {
   /**
     * The database table used by the model.
     *
     * @vendor Contus
     *
     * @package Video
     * @var string
     */
    protected $collection = 'favourite_videos';
    protected $connection = 'mongodb';
   
    /**
     * The primary key used by the model.
     *
     * @var string
     */
    protected $primaryKey = '_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'customer_id','video_id'];

    protected $hidden = [ 'created_at','updated_at'];

    public function video()
    {
        return $this->belongsToMany(Video::class,function($query){
            $query->where('is_active', '1')->where('job_status', 'Complete')->where('is_archived', 0);
        });
    }
}

