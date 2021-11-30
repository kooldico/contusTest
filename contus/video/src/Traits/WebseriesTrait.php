<?php

/**
 * VideoTrait
 *
 * To manage the functionalities related to the Videos module from Video Controller
 *
 * @vendor Contus
 *
 * @package Video
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Traits;

use Contus\Video\Models\WebseriesTranslation;

trait WebseriesTrait
{
    public function WebseriesTranslation()
    {
        return $this->hasMany(WebseriesTranslation::class, 'webseries_id');
    }

    public function getTitleAttribute($value)
    {
        $trans = $this->WebseriesTranslation()->where('language_id', $this->fetchLanugageId())->first();
        if (!empty($trans)) {
            return $trans->title;
        }
        return $value;
    }

    public function getDescriptionAttribute($value)
    {
        $trans = $this->WebseriesTranslation()->where('language_id', $this->fetchLanugageId())->first();
        if (!empty($trans)) {
            return $trans->description;
        }
        return $value;
    }
    public function getStarringAttribute($value)
    {
        $trans = $this->WebseriesTranslation()->where('language_id', $this->fetchLanugageId())->first();
        if (!empty($trans)) {
            return $trans->presenter;
        }
        return $value;
    }

    public function fetchTranslationInfo($vId)
    {
        return app('cache')->tags([getCacheTag(), 'webseries_translation'])->remember(getCacheKey(1) . '_global_webseries_translation_' . $vId, getCacheTime(), function () {
            return $this->WebseriesTranslation()->where('language_id', $this->fetchLanugageId())->first();
        });
    }

}
