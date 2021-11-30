<?php

/**
 * Audio Language Category Model
 *
 * Audio Language Category management related model
 *
 * @name Albums
 * @version 1.0
 * @author Contus Team <developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Models;

use Contus\Audio\Scopes\ActiveRecordScope;
use Contus\Audio\Traits\AudioHelperTrait;
use Contus\Base\Model;

class AudioLanguageCategory extends Model
{
    use AudioHelperTrait;
    /**
     * The database table used by the model.
     *
     * @vendor Contus
     *
     * @package Audio
     * @var string
     */
    protected $table = 'audio_language_category';

    /**
     * Constructor method
     * sets hidden for customers
     */
    public function __construct()
    {
        parent::__construct();
        $this->setHiddenCustomer(['created_at', 'updated_at', 'order', 'is_active']);
    }
    /**
     * The "booting" method of the model.
     *
     * @vendor Contus
     * @package Audio
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ActiveRecordScope);
    }
    /**
     * Method to get the albums repsective tracks
     *
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function albums()
    {
        return $this->hasMany(Albums::class, 'audio_language_category_id', 'id');
    }
    /**
     * Method to get the albums related to a language
     *
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getAlbumListAttribute()
    {
        $fields = 'id, album_name, slug, album_description, album_thumbnail, play_count, album_release_date, album_artist_id, audio_language_category_id';
        $albumBuilder = $this->albums()->selectRaw($fields);
        if (app('request')->has('search')) {
            $searchValue = app('request')->get('search');
            $language_id = app('request')->get('language_id');
            $resultQuery = $this->browseAlbumsAlphanumericWise($albumBuilder, $searchValue);
            $albumBuilder = $resultQuery->where('audio_language_category_id', $language_id);
        }
        return $albumBuilder->orderBy('id', 'desc')->paginate(config('access.perpage'), ['id', 'album_name', 'slug', 'album_description']);
    }
}
