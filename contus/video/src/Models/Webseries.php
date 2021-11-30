<?php

/**
 * Webseries Models.
 *
 * @name webseries
 * @vendor Contus
 * @package Video
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2019 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Models;

use Carbon\Carbon;
use Contus\Base\Helpers\StringLiterals;
use Contus\Base\Model;
use Contus\Video\Models\Group;
use Contus\Video\Models\Video;
use Contus\Video\Scopes\OrderByScope;
use Contus\Video\Traits\WebseriesTrait;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\File\File;

class Webseries extends Model
{

    use WebseriesTrait;

    /**
     * The database table used by the model.
     *
     * @vendor Contus
     *
     * @package Video
     * @var string
     */
    protected $table = 'video_webseries_detail';

    /**
     * The attributes that are mass assignable.
     *
     * @vendor Contus
     *
     * @package Video
     * @var array
     */
    protected $fillable = ['title', StringLiterals::ISACTIVE, 'parent_category_id', 'description', 'genre_id', 'starring', 'webseries_order'];
    /**
     * The attribute will used to generate url
     *
     * @var array
     */
    protected $url = ['thumbnail_image', 'poster_image'];

    /**
     * Constructor method
     * sets hidden for customers
     */
    public function __construct()
    {
        parent::__construct();
        $this->setHiddenCustomer(['id', 'is_active', 'updated_at', 'updator_id', 'creator_id']);
    }

    /**
     * funtion to automate operations while Saving
     */
    public function bootSaving()
    {
        $this->setDynamicSlug('title', 'slug');
        $keysArray = array('category_listing_page', 'dashboard_categories', 'dashboard_exams', 'dashboard_categorynave');
        $this->clearCache($keysArray);
        Cache::forget('relatedCategoryList' . $this->slug);
    }

    /**
     * HasOne relationship for category.
     */
    public function parent_category()
    {
        return $this->belongsTo(Category::class, 'parent_category_id', 'id');
    }

    /**
     * HasOne relationship for category.
     */
    public function child_category()
    {
        $returnChildCategory = $this->hasMany(Category::class, 'parent_id', 'id');
        if (config()->get('auth.providers.users.table') === 'customers') {
            $returnChildCategory = $returnChildCategory->where('categories.is_active', 1)->orderBy('is_leaf_category', 'desc');
        }
        return $returnChildCategory;
    }

    /**
     * Get File Information Model
     * the model related for holding the uploaded file information
     *
     * @vendor Contus
     *
     * @package Category
     * @return Contus\Video\Models\Category
     */
    public function getFileModel()
    {
        return $this;
    }
    /**
     * Get the formated created date
     *
     * @return object
     */
    public function getFormattedCreatedDateAttribute()
    {
        return Carbon::parse($this->created_at)->format('M d Y');
    }

    /**
     * Method to get genre
     */
    public function genre()
    {
        return $this->belongsTo(Group::class, 'genre_id', 'id');

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
        static::addGlobalScope(new OrderByScope);
    }

}
