<?php

/**
 * ArtistTrait
 *
 * To manage the functionalities related to the Album
 *
 * @vendor Contus
 *
 * @package Audio
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2019 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Traits;

use Contus\Audio\Models\Artist;
use Contus\Audio\Models\ArtistTranslation;

trait ArtistTrait
{

    public function ArtistTranslation()
    {
        return $this->hasMany(ArtistTranslation::class, 'artist_id');
    }

    public function getArtistNameAttribute($value)
    {
        $langCode = app()->getLocale();
        if ($langCode === 'en') {
            return $value;
        } else {
            $trans = $this->ArtistTranslation()->where('language_id', $this->fetchLanugageId())->first();
            if (!empty($trans)) {
                return $trans->artist_name;
            }
            return $value;
        }
    }

    public function getArtistBiographyAttribute($value)
    {
        $langCode = app()->getLocale();
        if ($langCode === 'en') {
            return $value;
        } else {
            $trans = $this->ArtistTranslation()->where('language_id', $this->fetchLanugageId())->first();
            if (!empty($trans)) {
                return $trans->artist_bio;
            }
            return $value;
        }
    }
}
