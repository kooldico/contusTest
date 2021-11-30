<?php

/**
 * TVOD Video Percentage Models.
 *
 * @name TvodVideoPercentage
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

class TvodVideoPercentage extends MongoModel
{
    protected $collection = 'tvod_video_percentage';
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
        'transaction_id', 'complete_percentage','created_at'
    ];

}