<?php
/**
 * Audio Play History Model
 *
 * @name AudioPlayHistory
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

class AudioPlayHistory extends MongoModel{
    protected $collection = 'audio_played_history';
    protected $connection = 'mongodb';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'audio_id', 'customer_id', 'ip_address', 'is_active','created_at', 'updated_at'
    ];
}