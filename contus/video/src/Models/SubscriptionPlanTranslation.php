<?php

/**
 * Categories Models.
 *
 * @name Categories
 * @vendor Contus
 * @package Video
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Models;

use Contus\Base\Model;
use Contus\Base\Helpers\StringLiterals;

use Symfony\Component\HttpFoundation\File\File;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Contus\Video\Models\SubscriptionPlan;

class SubscriptionPlanTranslation extends Model  {

    /**
     * The database table used by the model.
     *
     * @vendor Contus
     *
     * @package Video
     * @var string
     */
    protected $table = 'subscription_plans_translation';
    protected $primaryKey = 'id';
    protected $connection = 'mysql';
    /**
     * The attributes that are mass assignable.
     *
     * @vendor Contus
     *
     * @package Video
     * @var array
     */
    protected $fillable = [ 'subscription_plan_id','language_id', 'name', 'type', 'description'];

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

    public function subscription()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }
   
}
