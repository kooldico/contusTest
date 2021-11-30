<?php

/**
 * Implements of Model
 *
 *
 * @name Model
 * @vendor Contus
 * @package Base
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Base;

use Illuminate\Database\Eloquent\Model as IlluminateModel;
use Illuminate\Support\Facades\Config;
use Contus\Video\Models\SiteLanguage;


class Model extends IlluminateModel
{
    protected $url = [ ];
    /**
     * Create image dynamically while saving
     */
    protected static function boot()
    {
        parent::boot();
        static::saving ( function ($model) {
            $tableName = str_ireplace('_translation', '', $model->getTable());
            app('cache')->tags($tableName)->flush();
            $model->bootSaving ();
        } );

        static::deleting(function ($model) {
            $tableName = str_ireplace('_translation', '', $model->getTable());
            app('cache')->tags($tableName)->flush();
        });
    }
    /**
     * Saving automation
     */
    public function bootSaving() {
    }
    /**
     * Set the hidden attributes for the model based on user.
     *
     * @param array $hidden
     * @return $this
     */
    public function setHiddenCustomer(array $hidden)
    {
        if (Config::get('auth.providers.users.table') === 'customers') {
            if ((app()->make('request')->header('x-request-type') == 'mobile') && (($key = array_search('id', $hidden)) !== false)) {
                unset($hidden [$key]);
            }
            $this->hidden = $hidden;
        }
        return $this;
    }
     /**
     * Function to fetch locale code and return its associate ID
     * @return [type] [description]
     */
    public function fetchLanugageId() {
        return app('cache')->tags([getCacheTag(), 'site_languages'])->remember(getCacheKey(1).'_global_site_languages', getCacheTime(), function () {
            $langCode = app()->getLocale();
            $language = SiteLanguage::where('code', $langCode)->first();
            return (!empty($language)) ? $language->id : 0;
        });
    }
    /**
     * Set the visible attributes for the model based on user.
     *
     * @param array $visible
     * @return $this
     */
    public function setVisibleCustomer(array $visible)
    {
        if (Config::get('auth.providers.users.table') === 'customers') {
            $this->visible = $visible;
        }
        return $this;
    }
    /**
     * Set Url for images and videos
     *
     * {@inheritdoc}
     *
     * @see \Illuminate\Database\Eloquent\Model::getAttributes()
     */
    public function setRawAttributes(array $attributes, $sync = false)
    {
        $url = $this->url;
        foreach ($url as $genurl) {
            $prefix = 'https://s3.' . env('AWS_REGION') . '.amazonaws.com/' . env('AWS_BUCKET').'/';
            if (isset($attributes [$genurl]) && $attributes [$genurl] && substr($attributes [$genurl], 0, strlen($prefix)) == $prefix) {
                $attributes [$genurl] = substr($attributes [$genurl], strlen($prefix));
            }
            if (isset($attributes [$genurl]) && $attributes [$genurl] && filter_var($attributes [$genurl], FILTER_VALIDATE_URL) === false) {
                $attributes [$genurl] = env('AWS_BUCKET_URL') . $attributes [$genurl];
            }
        }
        return parent::setRawAttributes($attributes);
    }
    /**
     * Method to set thumbnail values for audio, albums and artist
     *
     * @vendor Contus
     * @param string $value
     * @return string
     */
    public function getAudiosPkgThumbnailImageAttributes($value, $type)
    {
        $placeholderImg = '';
        switch ($type) {
            case 'track':
                $placeholderImg = url(config('contus.audio.audioMedia.track_placeholder_image_path'));
                break;
            case 'album':
                $placeholderImg = url(config('contus.audio.audioMedia.album_placeholder_image_path'));
                break;
            case 'artist':
                $placeholderImg = url(config('contus.audio.audioMedia.artist_placeholder_image_path'));
                break;
            default:
                $placeholderImg = url(config('contus.audio.audioMedia.common_placeholder_image_path'));
                break;
        }
        return (!empty($value) && @getimagesize(env('AWS_BUCKET_URL') . $value))
        ? env('AWS_BUCKET_URL') . $value
        : $placeholderImg;
    }
}
