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
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Carbon\Carbon;

class Comment extends Eloquent
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
    protected $collection = 'comments';
    protected $connection = 'mongodb';
    protected $appends = ['reply_comment'];
    /**
     * Hidden variable to be returned
     *
     * @vendor Contus
     *
     * @package Video
     * @var array
     */
    protected $hidden = [ 'video_id','creator_id','updator_id', 'updated_at'];
    /**
     * belongsTo relationship between video and comments
     */
    public function videos()
    {
        return $this->belongsTo(Video::class);
    }

    public function bootSaving()
    {
        $keys = array('comments');
        $this->clearCache($keys);
    }

    /**
     * HasMany relationship between videos and reply comments
     */
    public function reply()
    {
        return $this->hasMany(Comment::class, 'parent_id', '_id');
    }

    public function getReplyCommentAttribute()
    {
        if(app()->request->has('video_id') && app()->request->video_id != '') {
            // To force pagination to 1 when there is a main comment loadmore action
            app()->request->request->add(['page' => 1]);
        }

        return $this->reply()->with(['customer' => function($query) {
            $query->withTrashed();
        }])->orderBy('_id', 'desc')->paginate(config('access.perpage'));
    }

    /**
     * Belongs to relationship between user and comments
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'user_id')->select('id', 'name', 'profile_image as profile_picture');
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
     * Function to formate created at
     *
     * @param date $date
     * @return string
     */
    public function getCreatedAtAttribute($date)
    {
        return Carbon::createFromTimeStamp(strtotime($date))->diffForHumans();
    }
}
