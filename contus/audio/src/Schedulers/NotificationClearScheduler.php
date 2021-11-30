<?php

/**
 * Upload To S3 Scheduler
 *
 * @name UploadToS3Scheduler
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Schedulers;

use Contus\Base\Schedulers\Scheduler;
use Exception;
use Carbon\Carbon;
use Contus\Notification\Models\Notification;

class NotificationClearScheduler extends Scheduler
{
    protected $notification;
    public function construct()
    {
        parent::__construct();
        $this->notification = new NotificationRepository();
    }
    /**
     * Scheduler frequency
     *
     * @param \Illuminate\Console\Scheduling\Event $event
     * @return void
     */
    public function frequency(\Illuminate\Console\Scheduling\Event $event)
    {
        $event->everyMinute();
    }
    /**
     * Scheduler call method
     * actual execution go's here
     *
     * @return \Closure
     */
    public function call()
    {
        return function () {
            Notification::where('created_at', '<', Carbon::now()->subDays(90))->delete();
        };
    }
}
