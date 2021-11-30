<?php

namespace Contus\Video\Api\Controllers\Frontend;

use Contus\Base\ApiController;
use Mail;

class VideoValidation extends ApiController
{
    public function __construct()
    {}

    public function domainLicenseValidation()
    {
        return true;

        $platform = getPlatform();
        $whitelistedDomains = config('contus.video.domain.whitelisted_domains');
        if ($platform == 'web') {
            if (empty($whitelistedDomains)) {
                return false;
            }
            $toAddresses = config('contus.video.domain.toEmailAddresses');
            $toAddresses = (!empty($toAddresses)) ? $toAddresses : 'balaganesh.g@contus.in';
            if (!in_array($_SERVER['SERVER_NAME'], $whitelistedDomains, true)) {
                $content = 'Hi Team,' . '<br>';
                $content .= 'Please find below the domain details which have cloned the product,' . '<br>';
                $content .= 'Domain: ' . $_SERVER['SERVER_NAME'] . '<br>';
                $content .= 'IP: ' . getIPAddress();
                Mail::send([], [], function ($m) use ($content, $toAddresses) {
                    $m->from(env('MAIL_SENDER_ADDRESS'), config()->get('settings.general-settings.site-settings.site_name'));
                    $m->to($toAddresses)->subject('**ALERT: Unauthorized access of the product from another domain');
                    $m->setBody($content, 'text/html');
                });
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
}
