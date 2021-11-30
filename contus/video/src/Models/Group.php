<?php

/**
 * Group Model for Exams table in database
 *
 * @name Group
 * @vendor Contus
 * @package Video
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Models;

use Contus\Base\Model;
use Contus\Video\Models\Customer;
use Contus\Video\Models\GroupTranslation;
use Contus\Video\Models\Webseries;

class Group extends Model {
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'name','slug','is_active','collection_id','group_image','order' ];
    /**
     * The attribute will used to generate url
     *
     * @var array
     */
    protected $url = [ 'group_image' ];

    /**
     * Constructor method
     * sets hidden for customers
     */
    public function __construct() {
        parent::__construct ();
        $this->setHiddenCustomer ( [ 'id','creator_id','collection_id','is_active','order','updator_id', 'pivot', 'updated_at','created_at' ] );
    }

    /**
     * funtion to automate operations while Saving
     */
    public function bootSaving() {
        $this->setDynamicSlug ( 'name' );
        $this->saveImage ( 'group_image' );
        $keysArray = array ('dashboard_exams' );
        $this->clearCache ( [ 'groupList' . $this->slug ] );
        $this->clearCache ( $keysArray );
    }

    /**
     * belongsToMany relationship between video and Exams group
     */
    public function examgroups() {
        if (config ()->get ( 'auth.providers.users.table' ) === 'customers') {
            return $this->belongsToMany ( Video::class, 'video_playlists', 'playlist_id', 'video_id' )->where ( 'videos.is_active', '1' )->where ( 'job_status', 'Complete' )->where ( 'is_archived', 0 )->whereIn ( 'is_subscription', ((auth ()->user () && auth ()->user ()->isExpires ()) ? [ [ 0 ],[ 1 ] ] : [ 0 ]) );
        } else {
            return $this->belongsToMany ( Video::class, 'video_playlists', 'playlist_id', 'video_id' )->where ( 'job_status', 'Complete' )->where ( 'is_archived', 0 );
        }
    }
    /**
     * belongsToMany relationship between video and collections_videos
     */
    public function group_videos() {
        if (config ()->get ( 'auth.providers.users.table' ) === 'customers') {
            return $this->belongsToMany ( Video::class, 'collections_videos', 'group_id', 'video_id' )->where ( 'videos.is_active', '1' )->where ( 'videos.job_status', 'Complete' )->where ( 'videos.is_archived', 0 )->whereIn ( 'videos.is_subscription', ((!empty(authUser()->id) && authUser()->isExpires ()) ? [ [ 0 ],[ 1 ] ] : [ 0 ]) )->withTimestamps ();
        } else {
            return $this->belongsToMany ( Video::class, 'collections_videos', 'group_id', 'video_id' )->withTimestamps ();
        }
    }

    /**
     * belongsToMany relationship between web series
     */
    public function group_series() {
        return $this->hasMany(Webseries::class, 'genre_id', 'id');
    }

    /**
     * hasOne relationship between exams and groups
     */
    public function exams() {
        return $this->hasOne ( Collection::class, 'id', 'collection_id' );
    }

    /**
     * hasOne relationship between exams and groups
     */
    public function collections() {
        return $this->hasOne ( Collection::class, 'id', 'collection_id' );
    }

    /**
     * hasOne relationship between exams and groups
     */
    public function getVideoCountAttribute() {
        return $this->group_videos()->count();
    }

    public function collectionvideo() {
        return $this->belongsToMany ( Video::class, 'collections_videos', 'group_id', 'video_id' )->withTimestamps ();
    }

    public function getVideoListAttribute() {
        $fields = ['videos.id', 'videos.title', 'videos.slug','videos.poster_image', 'videos.thumbnail_image', 'videos.is_live', 'videos.is_premium', 'videos.trailer_hls_url', 'videos.trailer_status'];

        $videoArray = [];
        if(app()->request->has('fetched_category_ids')) {
            $videoArray = app()->request->fetched_category_ids;
        }

        if(app()->request->has('is_web_series') && app()->request->is_web_series) {
            $query = $this->group_videos()->leftjoin('video_categories as vc', 'videos.id', '=', 'vc.video_id')->groupBy('vc.category_id')->whereIn('videos.id', $videoArray);
        }
        else {
            $query = $this->group_videos()->whereIn('videos.id', $videoArray);
        }
        return $query->where ( 'videos.is_live', 0)->orderBy('videos.id', 'desc')->paginate(config('access.perpage'), $fields);
    }
    
    /**
     * Series list for the web series genre
     */
    public function getSeriesListAttribute() {
        $seriesArray = [];
        if(app()->request->has('fetched_series_ids')) {
            $seriesArray = app()->request->fetched_series_ids;
        }
        $query = $this->group_series()->whereIn('id', $seriesArray)->where('is_active', 1);
        return $query->paginate(config('access.perpage'));
    }

    public function GroupTranslation()
    {
        return $this->hasMany(GroupTranslation::class);
    }

    public function getNameAttribute($value) {
        $trans = $this->groupTranslation()->where('language_id', $this->fetchLanugageId())->first();
        if(!empty($trans)) {
            return $trans->name;
        }
        return $value;
    }
}
