<?php

namespace Contus\Video\Models;

use Contus\Base\Model;
use Carbon\Carbon;
use Contus\Video\Models\EmailTemplatesTranslation;


class EmailTemplates extends Model {
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'email_templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'name','slug','subject','content','is_active' ];

    /**
     * funtion to automate operations while Saving
     */
    public function bootSaving() {
        $this->setDynamicSlug ( 'name' );
    }
    /**
     * Get the formated created date
     *
     * @return object
     */
    public function getFormattedCreatedDateAttribute()
    {
        return  Carbon::parse($this->created_at)->format('M d Y');
    }

    public function EmailTemplatesTranslation() {
        return $this->hasMany(EmailTemplatesTranslation::class, 'email_template_id');
    }
}
