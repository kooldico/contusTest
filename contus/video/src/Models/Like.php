<?php

/**
 * Like Models.
 *
 * @name Like
 * @vendor Contus
 * @package Video
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Models;

use Contus\Video\Models\Video;
use Contus\Video\Models\Customer;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Carbon\Carbon;

class Like extends Eloquent
{
    protected $primaryKey = '_id';

    const TYPE = [
      'like' => 1,
      'dislike' => 2
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['video_id'];

    /**
     * The database table used by the model.
     *
     * @vendor Contus
     *
     * @package Video
     * @var string
     */
    protected $collection = 'likes';
    protected $connection = 'mongodb';
    /**
     * Hidden variable to be returned
     *
     * @vendor Contus
     *
     * @package Video
     * @var array
     */
    protected $hidden = [ 'creator_id','updator_id', 'updated_at'];

    /**
     * Belongs to relationship between video and likes
     */
    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    /**
     * Belongs to relationship between customer and likes
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * Function to format created at date
     *
     * @param date $date
     * @return string
     */
    public function getCreatedAtAttribute($date)
    {
        return Carbon::createFromTimeStamp(strtotime($date))->diffForHumans();
    }
}
