<?php

/**
 * Implements of Model
 *
 *
 * @name Model
 * @vendor Contus
 * @package Base
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Base;

use Illuminate\Support\Facades\Config;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class MongoModel extends Eloquent {

    protected $url = [ ];
    /**
     * Create image dynamically while saving
     */
    protected static function boot() {
        parent::boot ();
        static::saving ( function ($model) {
            app('cache')->tags($model->getTable())->flush();
            $model->bootSaving ();
        } );

        static::deleting(function ($model) {
            app('cache')->tags($model->getTable())->flush();
        });
    }
    /**
     * Saving automation
     */
    public function bootSaving() {}
    /**
     * Set the hidden attributes for the model based on user.
     *
     * @param array $hidden
     * @return $this
     */
    public function setHiddenCustomer(array $hidden) {
        if (Config::get ( 'auth.providers.users.table' ) === 'customers') {
            if ((app ()->make ( 'request' )->header ( 'x-request-type' ) == 'mobile') && (($key = array_search ( 'id', $hidden )) !== false)) {
                unset ( $hidden [$key] );
            }
            $this->hidden = $hidden;
        }
        return $this;
    }

    /**
     * This Method to find slug or id
     *
     * @return String
     */
    public function getKeySlugorId()
    {
        if (isMobile()) {
            return 'id';
        } else {
            return 'slug';
        }
    }
}
