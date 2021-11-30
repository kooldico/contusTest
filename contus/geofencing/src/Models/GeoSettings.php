<?php

namespace Contus\Geofencing\Models;

use Contus\Base\Model;

class GeoSettings extends Model {
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'geofencing_setting';

    protected $fillable = ['is_active'];
}