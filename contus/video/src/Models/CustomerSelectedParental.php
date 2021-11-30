<?php

namespace Contus\Video\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Contus\Base\Model;

class CustomerSelectedParental extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'customer_selected_parental';
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    /**
     * Morph class name
     *
     * @var string
     */
    protected $morphClass = 'customer_selected_parental';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['customer_id', 'certificates'];

    /**
     * Tthe attributes used for soft delete
     */
    protected $dates = ['deleted_at'];

    
}
