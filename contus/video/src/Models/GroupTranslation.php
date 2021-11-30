<?php

/**
 * Group Model for Exams table in database
 *
 * @name Group
 * @vendor Contus
 * @package Video
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Models;

use Contus\Base\Model;
use Carbon\Carbon;
use Contus\Video\Models\Customer;
use Contus\Video\Models\Group;

class GroupTranslation extends Model {
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'groups_translation';
    protected $primaryKey = 'id';
    protected $connection = 'mysql';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'group_id','language_id', 'name'];
    protected $hidden = [ 'updated_at','created_at'];
    /**
     * The attribute will used to generate url
     *
     * @var array
     */
 

    /**
     * Constructor method
     * sets hidden for customers
     */
    public function __construct() {
       
     
    }

    /**
     * funtion to automate operations while Saving
     */
   
   public function Group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
}
