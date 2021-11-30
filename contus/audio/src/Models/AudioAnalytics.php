<?php
/**
 * Audio Analytics Model
 *
 * @name AudioAnalytics
 * @vendor Contus
 * @package Audio
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Models;

use Contus\Base\MongoModel;
use Contus\Base\Model;

class AudioAnalytics extends MongoModel{
    protected $collection = 'audio_analytics';
    protected $connection = 'mongodb';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'audio_id', 'album_id', 'audio_title', 'customer_id', 'country', 'platform', 'ip_address', 'listened_date', 'created_at', 'updated_at'
    ];
}