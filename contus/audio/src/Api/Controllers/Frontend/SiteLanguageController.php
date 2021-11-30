<?php
/**
 * SiteLanguageController
 *
 * To manage the site language management such as create, edit and delete
 *
 * @name SiteLanguageController
 * @version 1.0
 * @author Contus Team <developers@contus.in>
 * @copyright Copyright (C) 2019 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Api\Controllers\Frontend;

use Contus\Audio\Repositories\SiteLanguageRepository;
use Contus\Base\ApiController;

class SiteLanguageController extends ApiController
{
    /**
     * Class construct method initialization
     */
    public function __construct()
    {
        parent::__construct();
        $this->repository = new siteLanguageRepository();
        $this->repository->setRequestType(static::REQUEST_TYPE);
    }

    /** Method to fetch the site languages
     *
     * @vendor Contus
     * @Package Audio
     * @return Illuminate\Http\Response
     */
    public function getSiteLanguages()
    {
        $data = $this->repository->getSiteLanguages();
        return ($data) ? $this->getSuccessJsonResponse(['response' => $data, 'message' => trans('audio::audio.site_language.fetch.success')]) : $this->getErrorJsonResponse([], trans('audio::audio.site_language.fetch.error'));
    }

}
