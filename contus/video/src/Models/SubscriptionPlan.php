<?php

namespace Contus\Video\Models;

use Contus\Base\Model;
use Contus\Video\Models\SubscriptionPlanTranslation;

class SubscriptionPlan extends Model {
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'subscription_plans';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'name','type','amount','description','duration','is_active' ];

    /**
     * Constructor method
     * sets hidden for customers
     */
    public function __construct() {
        parent::__construct ();
        $this->setHiddenCustomer ( [ 'id','is_active','creator_id','updator_id','created_at','updated_at' ] );
    }

    /**
     * funtion to automate operations while Saving
     */
    public function bootSaving() {
        $this->setDynamicSlug ( 'name' );
    }
    /**
     * Belongs to many relation with customer
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function SubscriberInfo() {
        return $this->belongsToMany ( Customer::class, 'subscribers' );
    }

    public function getIsSubscribeAttribute() {
        $subscribe = [];
        if(auth() && auth()->user()) {
            $subscribe = $this->Subscribers()->where('customer_id', (!empty(authUser()->id)) ? authUser()->id : 0)->get()->toArray();
        }
        
        return (!empty($subscribe)) ? 1 : 0;
    }

    /**
     * Belongs to many relation with customer
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function Subscribers() {
        return $this->hasMany ( Subscribers::class )->where('is_active', 1);
    }

    public function subscriptionTranslation()
    {
        return $this->hasMany(SubscriptionPlanTranslation::class);
    }

    public function getNameAttribute($value)
    {
        $trans = $this->subscriptionTranslation()->where('language_id', $this->fetchLanugageId())->first();
        if (!empty($trans)) {
            return $trans->name;
        }
        return $value;
    }

    public function getTypeAttribute($value)
    {
        $trans = $this->subscriptionTranslation()->where('language_id', $this->fetchLanugageId())->first();
        if (!empty($trans)) {
            return $trans->type;
        }
        return $value;
    }
}
