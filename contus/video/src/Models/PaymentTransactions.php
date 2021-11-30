<?php

/**
 * Payment Transaction Model is used to manage the payment transactions in database
 *
 * @name PaymentTransactions
 * @vendor Contus
 * @package payment
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Models;

use Contus\Base\Model;
use Contus\Video\Models\Customer;
use Contus\Payment\Models\PaymentMethod;
use Contus\Video\Models\Subscribers;
use Contus\Video\Models\SubscriptionPlan;
use Contus\Video\Models\Video;

class PaymentTransactions extends Model {
  
    /**
     * The database table used by the model.
     *
     * @vendor Contus
     *
     * @package payment
     * @var string
     */
    protected $table = 'payment_transactions';
    
    /**
     * The attributes that are mass assignable.
     *
     * @vendor Contus
     *
     * @package payment
     * @var array
     */
    protected $fillable = [ 'payment_method_id','customer_id','status','transaction_message','transaction_id','response' ];
    /**
     * Relationship to fetch users for each transaction
     * @vendor Contus
     *
     * @package payment
     */
    /**
     * Constructor method
     * sets hidden for customers
     */
    public function __construct() {
        parent::__construct();
        $this->setHiddenCustomer (['creator_id','updator_id','response']);
    }
    
    public function getTransactionUser() {
        return $this->belongsTo ( Customer::class, 'customer_id', 'id' )->select ( [ 'id','name' ] );
    }
    /**
     * Relationship to fetch video details for each transaction
     * @vendor Contus
     *
     * @package payment
     */
  
    public function video() {
        return $this->belongsTo ( Video::class, 'video_id', 'id' )->select ( [ 'id','title', 'slug', 'thumbnail_image'] );
    }
    /**
     * Relationship to fetch users for each transaction payment method
     * @vendor Contus
     *
     * @package payment
     */
    public function getPaymentMethod() {
        return $this->belongsTo ( PaymentMethod::class, 'payment_method_id', 'id' )->select ( [ 'id','name' ] );
    }
    /**
     * Relationship to fetch users for each sucscription
     */
    public function getSubscriptionPlan() {
        return $this->belongsTo( SubscriptionPlan::class,'subscription_plan_id','id');
    }
}
