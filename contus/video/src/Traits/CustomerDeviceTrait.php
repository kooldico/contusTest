<?php

/**
 * CustomerTrait
 *
 * To manage the functionalities related to the Categories module from Categories Controller
 *
 * @vendor Contus
 *
 * @package customer
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Traits;

use Carbon\Carbon;
use Contus\Video\Models\CustomerDeviceDetails;
use Contus\Video\Models\Subscribers;
use Illuminate\Support\Facades\Auth;

trait CustomerDeviceTrait
{
    public function deviceRegisterValidator($customer, $deviceId, $request)
    {
        $subscribed = Subscribers::where('customer_id', $customer->id)
            ->where('is_active', 1)
            ->with('subscriptionPlan')
            ->first();

        if ($subscribed != null && $subscribed->subscriptionPlan) {

            if (!empty(config()->get('settings.site-global-settings.site-global-module-settings.screen_restriction'))) {
                $this->deviceRegister($customer, $request);
                return true;
            }

            $deviceLimit = $subscribed->subscriptionPlan->device_limit;

            // if there is no device_limit which implies there is no device limit(users can use any number of devices)
            if (is_null($deviceLimit)) {
                $this->deviceRegister($customer, $request);
                return true;
            }

            $customerDeviceDetails = CustomerDeviceDetails::where('customer_id', $customer->id)->get();

            foreach ($customerDeviceDetails as $customerDeviceDetail) {
                if ($customerDeviceDetail->device_id === $deviceId) {
                    return true;
                }
            }

            $deviceCount = $customerDeviceDetails->count();

            if ($deviceLimit > $deviceCount) {
                $this->deviceRegister($customer, $request);
                return true;
            }

            return false;
        }
        return true;
    }

    public function deviceRegister($customer, $request)
    {

        $deviceId = trim($request->header('X-DEVICE-ID'));
        $deviceName = $request->header('X-DEVICE-NAME');
        $deviceOs = $request->header('X-DEVICE-OS');
        $requestType = $request->header('X-REQUEST-TYPE');
        $deviceCategory = $request->header('X-DEVICE-CATEGORY');
        $_deviceCategory = null;

        if ($deviceCategory && strtolower($deviceCategory) === 'tv') {
            $deviceType = $request->header('X-DEVICE-TYPE');

            switch (strtolower($deviceType)) {
                case 'ios':
                    $deviceType = 'Apple';
                    break;
                case 'android':
                    $deviceType = 'Android';
                    break;
                case 'lg':
                    $deviceType = 'LG';
                    break;
                case 'samsung':
                    $deviceType = 'Samsung';
                    break;
                default:
                    $deviceType = null;
                    break;
            }

            if ($deviceType && $deviceCategory) {
                $_deviceCategory = $deviceType . ' Tv';
            }
        }

        $customerDeviceDetail = CustomerDeviceDetails::where('customer_id', $customer->id)->where('device_id', $deviceId)->first();

        if ($customerDeviceDetail) {
            return true;
        }

        $deviceDetails = new CustomerDeviceDetails();
        $deviceDetails->updateOrCreate(
            ['device_id' => $deviceId],
            [
                'customer_id' => $customer->id,
                'device_id' => $deviceId,
                'device_name' => $deviceName,
                'device_category' => $_deviceCategory,
                'device_os' => $deviceOs,
                'is_playing' => false,
                'request_ip' => getClientIp(),
                'request_type' => $requestType,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }
}
