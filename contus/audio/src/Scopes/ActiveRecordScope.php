<?php
/**
 * Active record scope
 *
 * Global scope to fetch only active records
 *
 * @name ActiveRecordScope
 * @version 1.0
 * @author Contus Team <developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ActiveRecordScope implements Scope{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @vendor Contus
     * @package Audio
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model){
        $builder->where('is_active', '=', 1);
    }
}
?>