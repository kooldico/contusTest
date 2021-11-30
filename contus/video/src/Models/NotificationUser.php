<?php

/**
 * Notification Model is used to manage the notifications in database
 *
 * @name Notification
 * @vendor Contus
 * @package notification
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Models;

use Contus\Base\Model;
use Contus\Video\Models\Customer;
use Contus\User\Models\User;
use Contus\Video\Models\Video;

class NotificationUser extends Model
{

    /**
     * The database table used by the model.
     *
     * @vendor Contus
     *
     * @package notification
     * @var string
     */
    protected $table = 'notification_users';
    protected $primaryKey = 'id';
    protected $connection = 'mysql';
    /**
     * The attributes that are mass assignable.
     *
     * @vendor Contus
     *
     * @package notification
     * @var array
     */
    protected $fillable = [ 'new_video', 'reply_comment', 'user_id', 'auto_play'];
    protected $appends = [ 'is_subscribed'];

    /**
     * Constructor method
     * sets hidden for notifications
     */
    public function __construct()
    {
        parent::__construct();
        $this->setHiddenCustomer([ 'id' ]);
    }

    /**
     * Function to fetch subscribed status
     * @return Boolean
     */
    public function getIsSubscribedAttribute() {
        if(!empty(authUser()->id)) {
            return authUser()->isExpires() ? 1 : 0;
        }
        else {
            return 0;
        }
    }
}
