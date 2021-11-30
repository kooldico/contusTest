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

use Contus\Video\Models\ReplyComment;
use Contus\Video\Models\Video;

use Contus\Video\Models\Customer;
use Carbon\Carbon;
use Contus\Video\Models\PlaylistVideos;

use Contus\Base\MongoModel ;

class UserPlaylist extends MongoModel
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
    protected $collection = 'user_playlist';
    protected $connection = 'mongodb';
    protected $appends = ['is_added', 'video_count', 'poster_image'];
    /**
     * Hidden variable to be returned
     *
     * @vendor Contus
     *
     * @package Video
     * @var array
     */
    protected $hidden = [ 'video_id','creator_id','updator_id', 'updated_at'];
    
    public function bootSaving()
    {
        $keys = array('comments');
        $this->clearCache($keys);

        $this->setDynamicSlug('name');
    }

    

    /**
     * Belongs to relationship between video and comments
     */
    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    /**
     * Belongs to relationship between customer and comments
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    
    /**
     * Belongs to relationship between playlist and Playlist
     */
    public function playlistVideos() {
        return $this->hasMany(PlaylistVideos::class, 'playlist_id', '_id');
    }

    /**
     * Function to fetch already added flag
     * @return Array
     */
    public function getIsAddedAttribute() {
        if(app()->request->has('video_id') && app()->request->video_id != '') {
            $video = app()->request->video_id;
            $videoInfo = Video::where($this->getKeySlugorId(), $video)->first();
            if(!empty($videoInfo)) {
                $videoInfo = $videoInfo->makeVisible(['id']);
                if($this->playlistVideos()->where('video_id', (string) $videoInfo['id'])->where('is_active', 1)->first()) {
                    return 1;
                }
            }
        }
        return 0;
    }

    /**
     * Function to fetch video count in playlist
     * @return integer
     */
    public function getVideoCountAttribute() {
        return $this->playlistVideos()->where('is_active', 1)->count();
    }

    /**
     * Function to fetch Video image for the playlist
     * @return string
     */
    public function getPosterImageAttribute() {
        if($this->playlistVideos()->where('is_active', 1)->count()) {
            $videoInfo = $this->playlistVideos()->where('is_active', 1)->first();
            if(!empty($videoInfo)) {
                $video = Video::where('id', $videoInfo['video_id'])->first();
                if(!empty($video)) {
                    return $video->thumbnail_image;
                }
            }
        }
        return '';
    }
}
