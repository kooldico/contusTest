<?php

namespace Contus\Video\Models;

use Contus\Base\Model;
use Contus\Video\Models\SubscriptionPlan;

class Subscribers extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'subscribers';

    protected $fillable = ['customer_id', 'subscription_plan_id', 'start_date', 'end_date', 'creator_id', 'is_active'];
    /**
     * Belongs to many relation with subscription plan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id', 'id');
    }

}
