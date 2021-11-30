<?php

/**
 * Watch History Models.
 *
 * @name WatchHistory
 * @vendor Contus
 * @package Video
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Models;

use Contus\Base\MongoModel;

class CustomerDeviceDetails extends MongoModel
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
    protected $collection = 'customer_device_details';
    protected $connection = 'mongodb';
    protected $appends = [];
    protected $fillable = ['name', 'customer_id', 'device_id', 'device_name', 'device_category', 'device_os', 'is_playing', 'request_ip', 'request_type'];
    /**
     * Hidden variable to be returned
     *
     * @vendor Contus
     *
     * @package Video
     * @var array
     */
    protected $hidden = [];
}
