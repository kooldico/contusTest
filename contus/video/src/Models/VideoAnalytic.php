<?php

/**
 * Video Analytics Models.
 *
 * @name VideoAnalytic
 * @vendor Contus
 * @package Video
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Models;

use Contus\Base\MongoModel;
use Contus\Base\Model;

class VideoAnalytic extends MongoModel
{
    protected $collection = 'video_analytics';
    protected $connection = 'mongodb';
    public $timestamps = false;

    public function bootSaving() {
        $this->created_at = $this->freshTimestamp();
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'video_id', 'video_title', 'customer_id', 'country', 'platform','created_at'
    ];
    /**
     * Get the video that owns the comment.
     */
    public function video(){
        return $this->belongsTo('Contus\Video\Models\Video');
    }

}