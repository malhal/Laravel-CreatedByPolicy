<?php
/**
 *  Laravel-CreatedBySecurity (http://github.com/malhal/Laravel-CreatedBySecurity)
 *
 *  Created by Malcolm Hall on 22/8/2016.
 *  Copyright © 2016 Malcolm Hall. All rights reserved.
 */

namespace Malhal\CreatedBySecurity;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use \Illuminate\Database\Eloquent\Scope;
use \Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CreatedBySecurityScope implements Scope
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var array
     */
    protected $extensions = ['WithCreatedBySecurity', 'WithoutCreatedBySecurity'];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $this->applyCreatedBySecurity($builder);
    }

    protected function applyCreatedBySecurity(Builder $builder){
        $model = $builder->getModel();

        if (!$model->getWorldRead() && !$model->getAuthenticatedRead() && !$model->getCreatorRead()){
            throw new AuthorizationException('No-one can read.');
        }
        // if the permissions are set to the creator only then filter the query.
        else if (!$model->getWorldRead() && ($model->getAuthenticatedRead() || $model->getCreatorRead())){
            $user = $model->currentUser();
            if (is_null($user)) {
                //throw new AuthorizationException('Only the creator can read.');
                throw (new ModelNotFoundException)->setModel(get_class($model));
            }
            else if ($model->getCreatorRead()) {
                $createdByRelationName = $model->createdByRelationName();
                $createdBy = $model->$createdByRelationName();
                $builder->where($createdBy->getForeignKey(), $user->getKey());
            }
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
    protected function addWithCreatedBySecurity(Builder $builder) {
        $builder->macro('withCreatedBySecurity', function (Builder $builder) {
            $builder->withoutGlobalScope($this);
            $this->applyCreatedBySecurity($builder);
            return $builder;
        });
    }

    /**
     * Add the without-trashed extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addWithoutCreatedBySecurity(Builder $builder)
    {
        $builder->macro('withoutCreatedBySecurity', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}