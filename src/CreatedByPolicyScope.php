<?php
/**
 *  Laravel-CreatedByPolicy (http://github.com/malhal/Laravel-CreatedByPolicy)
 *
 *  Created by Malcolm Hall on 22/8/2016.
 *  Copyright © 2016 Malcolm Hall. All rights reserved.
 */

namespace Malhal\CreatedByPolicy;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use \Illuminate\Database\Eloquent\Scope;
use \Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreatedByPolicyScope implements Scope
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var array
     */
    protected $extensions = ['WithCreatedByPolicy', 'WithoutCreatedByPolicy'];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // this global scope is applied if the only read access is creatorRead, done in CreatedByPolicy.
        $this->applyCreatedByPolicy($builder);
    }

    protected function applyCreatedByPolicy(Builder $builder){

        $model = $builder->getModel();
        $user = Auth::user();
        if(is_null($user->getKey())){
            // prevent anthing being found, we won't exception so this scope can be removed.
            $builder->whereNull($model->getKeyName());
        }
        else {
            $createdByRelationName = $model->createdByRelationName();
            $createdBy = $model->$createdByRelationName();
            $builder->where($createdBy->getForeignKey(), $user->getKey());
        }
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    /**
     * Add the with-trashed extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addWithCreatedByPolicy(Builder $builder) {
        $builder->macro('withCreatedByPolicy', function (Builder $builder) {
            $builder->withoutGlobalScope($this);
            $this->applyCreatedByPolicy($builder);
            return $builder;
        });
    }

    /**
     * Add the without-trashed extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addWithoutCreatedByPolicy(Builder $builder)
    {
        $builder->macro('withoutCreatedByPolicy', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}