<?php

/**
 * Geo Global Allowed Countries Model.
 *
 * @name Geo Global Allowed Countries
 * @vendor Contus
 * @package Geofencing
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Geofencing\Models;

use Contus\Base\MongoModel;
use Contus\Base\Model;

class GeoGlobalAllowedCountries extends MongoModel{
    protected $collection = 'global_allowed_countries';
    protected $connection = 'mongodb';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'country_id', 'region_id', 'country_code', 'region_code', 'created_at', 'updated_at'
    ];
}