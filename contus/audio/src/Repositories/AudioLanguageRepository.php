<?php
/**
 * AudioLanguageRepository
 *
 * To manage the audio language management such as create, edit and delete
 *
 * @name AudioLanguageRepository
 * @version 1.0
 * @author Contus Team <developers@contus.in>
 * @copyright Copyright (C) 2019 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Repositories;

use Contus\Audio\Models\AudioLanguageCategory;
use Contus\Base\Repository as BaseRepository;

class AudioLanguageRepository extends BaseRepository
{
    /**
     * Class construct method initialization
     */
    public function __construct()
    {
        parent::__construct();

        $this->audioLanguage = new AudioLanguageCategory();
        $this->IP_address = getIPAddress();
        $this->records_per_page = config('contus.audio.audio.record_per_page');
    }

    /**
     * Method to fetch audio language
     *
     * @vendor Contus
     * @package Audio
     * @return array
     */
    public function getAudioLanguages()
    {
        return $this->audioLanguage->select('id', 'language_name')->get()->toArray();
    }
}
