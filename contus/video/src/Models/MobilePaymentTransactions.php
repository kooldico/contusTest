<?php

/**
 * MobilePaymentTransactions Models.
 *
 * @name MobilePaymentTransactions
 * @vendor Contus
 * @package Video
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2021 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Models;

use Contus\Base\Model;
use Contus\Base\Helpers\StringLiterals;
use Carbon\Carbon;

class MobilePaymentTransactions extends Model  {

    /**
     * The database table used by the model.
     *
     * @vendor Contus
     *
     * @package Video
     * @var string
     */
    protected $table = 'mobile_payment_transactions';

    protected $fillable = ['name', 'email', 'customer_id', 'transaction_type', 'plan_name', 'transaction_id', 'amount', 'status', 'latest_receipt', 'purchase_response', 'subscription_start_date', 'subscription_end_date', 'canceled_at', 'cancellation_reason'];

    /**
     * Constructor method
     * sets hidden for customers
     */
    public function __construct() {
        parent::__construct ();
    }
    /**
     * Get the formated created date
     *
     * @return object
     */
    public function getFormattedCreatedDateAttribute()
    {
        return Carbon::parse($this->subscription_date)->format('m/d/Y');
    }
}
