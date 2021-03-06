<?php

/**
 * Model for audio_presets table in database
 *
 * @name AudioPreset
 * @vendor Contus
 * @package Audio
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Models;

use Contus\Base\Model;

class AudioPreset extends Model {

    /**
     * The database table used by the model.
     *
     * @vendor Contus
     *
     * @package Audio
     * @var string
     */
    protected $table = 'audio_presets';

    /**
     * The attributes that are mass assignable.
     *
     * @vendor Contus
     *
     * @package Audio
     * @var array
     */
    protected $fillable = [ 'name','aws_id','format','description','is_active' ];
    /**
     * Constructor method
     * sets visible for customers
     */
    public function __construct() {
        parent::__construct ();
        $this->setVisibleCustomer ( [ 'name','format','description','thumbnail_format' ] );
    }
}

