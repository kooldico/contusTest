<?php

/**
 * VideoTag Models.
 *
 * @name VideoTag
 * @vendor Contus
 * @package Video
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Models;

use Illuminate\Database\Eloquent\Model;

class VideoMetaData extends Model
{

    /**
     * The database table used by the model.
     *
     * @vendor Contus
     *
     * @package Video
     * @var string
     */
    protected $table = 'video_metadata';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'video_id', 'custom_url', 'title', 'description', 'keyword', 'creator_id', 'updator_id', 'created_at', 'updated_at', 'deleted_at',
    ];

    /**
     * Get the video that owns the metadata.
     */
    public function video()
    {
        return $this->belongsTo('Contus\Video\Models\Video');
    }
}
