<?php

/**
 * Comment Models.
 *
 * @name Comment
 * @vendor Contus
 * @package Video
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Models;

use Contus\Video\Models\Video;
use Contus\Video\Models\Customer;
use Contus\Base\MongoModel ;

class PlaylistVideos extends MongoModel
{
    protected $primaryKey = '_id';
    /**
     * The database table used by the model.
     *
     * @vendor Contus
     *
     * @package Video
     * @var string
     */
    protected $collection = 'playlist_videos';
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
    
    public function bootSaving()
    {
        $keys = array('comments');
        $this->clearCache($keys);
    }

    

    /**
     * Belongs to relationship between video and comments
     */
    public function video()
    {
        return $this->belongsTo(Video::class, 'video_id', 'id');
    }

    /**
     * Belongs to relationship between customer and comments
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    
}
