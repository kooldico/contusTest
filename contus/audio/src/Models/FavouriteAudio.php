<?php

/**
 * Favourite Audio Model.
 *
 * @name FavouriteAudio
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
use Contus\Audio\Models\Audios;


class FavouriteAudio extends MongoModel{
    protected $collection = 'favourite_audios';
    protected $connection = 'mongodb';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'audio_id', 'customer_id', 'created_at', 'updated_at'
    ];
}