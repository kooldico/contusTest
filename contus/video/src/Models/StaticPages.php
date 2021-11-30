<?php

namespace Contus\Video\Models;

use Contus\Base\Model;
use Contus\Video\Models\StaticPagesTranslation;

class StaticPages extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'static_pages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'content', 'is_active'];

    /**
     * Constructor method
     * sets hidden for customers
     */
    public function __construct()
    {
        parent::__construct();
        $this->setHiddenCustomer(['id', 'is_active']);
    }

    /**
     * funtion to automate operations while Saving
     */
    public function bootSaving()
    {
        $this->setDynamicSlug('title');
    }

    public function getFormattedUpdatedDateAttribute()
    {
        return Carbon::parse($this->updated_at)->format('M d Y');
    }

    public function staticPagesTranslation()
    {
        return $this->hasMany(StaticPagesTranslation::class, 'static_page_id');
    }

    public function getTitleAttribute($value)
    {
        $trans = $this->staticPagesTranslation()->where('language_id', $this->fetchLanugageId())->first();
        if (!empty($trans)) {
            return $trans->title;
        }
        return $value;
    }

    public function getContentAttribute($value)
    {
        $trans = $this->staticPagesTranslation()->where('language_id', $this->fetchLanugageId())->first();
        if (!empty($trans)) {
            return $trans->content;
        }
        return $value;
    }
}
