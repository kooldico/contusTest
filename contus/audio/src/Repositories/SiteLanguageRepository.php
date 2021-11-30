<?php
/**
 * SiteLanguageRepository
 *
 * To manage the site language management such as create, edit and delete
 *
 * @name SiteLanguageRepository
 * @version 1.0
 * @author Contus Team <developers@contus.in>
 * @copyright Copyright (C) 2019 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Repositories;

use Contus\Base\Repository as BaseRepository;
use Contus\User\Models\SiteLanguage;

class SiteLanguageRepository extends BaseRepository
{
    /**
     * Class construct method initialization
     */
    public function __construct()
    {
        parent::__construct();

        $this->siteLanguage = new SiteLanguage();
        $this->IP_address = getIPAddress();
        $this->records_per_page = config('contus.audio.audio.record_per_page');
    }

    /**
     * Method to fetch site language
     *
     * @vendor Contus
     * @package Audio
     * @return array
     */
    public function getSiteLanguages()
    {
        return $this->siteLanguage->get()->toArray();
    }
}
