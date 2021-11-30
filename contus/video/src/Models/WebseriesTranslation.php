<?php

/**
 * Video Model for videos table in database
 *
 * @name Video
 * @vendor Contus
 * @package Video
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Models;


use Contus\Base\Model;
use Contus\Video\Models\Webseries;
use Symfony\Component\HttpFoundation\File\File;

class WebseriesTranslation extends Model 
{
    /**
     * The database table used by the model.
     *
     * @vendor Contus
     *
     * @package Video
     * @var string
     */
    protected $table = 'webseries_translation';
    /**
     * Morph class name
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @vendor Contus
     *
     * @package Video
     * @var array
     */
    protected $fillable = ['webseries_id', 'language_id', 'title', 'description', 'presenter'];

    /**
     * The attributes added from the model while fetching.
     *
     * @var array
     */
    
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $connection = 'mysql';

    /**
     * Constructor method
     * sets hidden for customers
     */
    public function __construct()
    {
        parent::__construct();
        $this->setHiddenCustomer(['id']);
    }

    /**
     * Get File Information Model
     * the model related for holding the uploaded file information
     *
     * @vendor Contus
     *
     * @package Base
     * @return Contus\Base\Model\Video
     */
    public function getFileModel()
    {
        return $this;
    }
    /**
     * many to one relation to video
     */
    public function video()
    {
        return $this->belongsTo(Webseries::class, 'webseries_id');
    }

    /**
     * many to one relation to video
     */
    public function webseries()
    {
        return $this->belongsTo(Webseries::class, 'webseries_id');
    }
}
