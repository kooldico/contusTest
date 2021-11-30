<?php
/**
 * AudioLanguageController
 *
 * To manage the audio language management such as create, edit and delete
 *
 * @name AudioLanguageController
 * @version 1.0
 * @author Contus Team <developers@contus.in>
 * @copyright Copyright (C) 2019 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Api\Controllers\Frontend;

use Contus\Audio\Repositories\AudioLanguageRepository;
use Contus\Base\ApiController;

class AudioLanguageController extends ApiController
{
    /**
     * Class construct method initialization
     */
    public function __construct()
    {
        parent::__construct();
        $this->repository = new AudioLanguageRepository();
        $this->repository->setRequestType(static::REQUEST_TYPE);
    }

    /** Method to fetch the audio languages
     *
     * @vendor Contus
     * @Package Audio
     * @return Illuminate\Http\Response
     */
    public function getAudioLanguages()
    {
        $data = $this->repository->getAudioLanguages();
        return ($data) ? $this->getSuccessJsonResponse(['response' => $data, 'message' => trans('audio::audio.audio_language.fetch.success')]) : $this->getErrorJsonResponse([], trans('audio::audio.audio_language.fetch.error'));
    }

}
