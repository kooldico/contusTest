<?php
/**
 * Orderby record scope
 *
 * Global scope to sort the data based on order field
 *
 * @name OrderByScope
 * @version 1.0
 * @author Contus Team <developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OrderByScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @vendor Contus
     * @package Audio
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {

        $table = $model->getTable();
        if ($table === 'video_webseries_detail') {
            $builder->orderBy('webseries_order', 'asc');
        }
    }
}
