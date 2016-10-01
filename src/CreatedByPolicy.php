<?php
/**
 *  Laravel-CreatedByPolicy (http://github.com/malhal/Laravel-CreatedByPolicy)
 *
 *  Created by Malcolm Hall on 6/9/2016.
 *  Copyright © 2016 Malcolm Hall. All rights reserved.
 */

namespace Malhal\CreatedByPolicy;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

trait CreatedByPolicy
{
    // Define any of these constants in your class to override,
    // they are not defined here because of a limitation with traits but there default values are shown.

    // const WORLD_CREATE = false;
    // const WORLD_READ = true;
    // const WORLD_WRITE = false;

    // const AUTHENTICATED_CREATE = true;
    // const AUTHENTICATED_READ = false;
    // const AUTHENTICATED_WRITE = false;

    // const CREATOR_READ = false;
    // const CREATOR_WRITE = true;

    public function getWorldCreate()
    {
        return defined('self::WORLD_CREATE') ? self::WORLD_CREATE : false;
    }

    public function getWorldRead()
    {
        return defined('self::WORLD_READ') ? self::WORLD_READ : true;
    }

    public function getWorldWrite()
    {
        return defined('self::WORLD_WRITE') ? self::WORLD_WRITE : false;
    }

    public function getAuthenticatedCreate()
    {
        return defined('self::AUTHENTICATED_CREATE') ? self::AUTHENTICATED_CREATE : true;
    }

    public function getAuthenticatedRead()
    {
        return defined('self::AUTHENTICATED_READ') ? self::AUTHENTICATED_READ : false;
    }

    public function getAuthenticatedWrite()
    {
        return defined('self::AUTHENTICATED_WRITE') ? self::AUTHENTICATED_WRITE : false;
    }

    public function getCreatorRead()
    {
        return defined('self::CREATOR_READ') ? self::CREATOR_READ : false;
    }

    public function getCreatorWrite()
    {
        return defined('self::CREATOR_WRITE') ? self::CREATOR_WRITE : true;
    }
/*
    public function create($user)
    {
        return $this->canCreate($user);
    }

    public function update($user, $model){

        return $this->canWrite($user, $model);
    }

    public function delete($user, $model){

        return $this->canWrite($user, $model);
    }

    public function show($user, $model){

        return $this->canRead($user, $model);
    }
*/
    public function create($user){

        if (!$this->getWorldCreate() && $this->getAuthenticatedCreate()) {
            if (is_null($user->getKey())) {
                $this->deny('Only authenticated can create.'); // CREATE not permitted
            }
        } else if (!$this->getWorldCreate()) {
            $this->deny('No-one can create.');
        }

        return true;
    }

    public function write($user, $model){

        if (!$this->getWorldWrite() && !$this->getAuthenticatedWrite() && $this->getCreatorWrite()) {
            //$user = Auth::user();
            if (is_null($user->getKey()) || $user->getKey() != $model->getCreatedByForeignKey()) {
                $this->deny('Only the creator can write.');
            }
        }
        else if (!$this->getWorldWrite() && $this->getAuthenticatedWrite()) {
            if (is_null($user->getKey())) {
                $this->deny('Only authenticated can write.');
            }
        } else if (!$this->getWorldWrite()) {
            $this->deny('No-one can write.');
        }
        return true;
    }

    public function read($user, $model){

        if (!$this->getWorldRead() && !$this->getAuthenticatedRead() && !$this->getCreatorRead()){
            $this->deny('No-one can read.');
        }
        else if (!$this->getWorldRead() && ($this->getAuthenticatedRead() || $this->getCreatorRead())){
            if (is_null($user->getKey())) {
                // optimise by throwing here rather than have the query run and find nothing.
                throw (new ModelNotFoundException())->setModel(get_class($model));
            }
            else if ($this->getCreatorRead()) {
                $model::addGlobalScope(new CreatedByPolicyScope());
            }
        }

        return true;
    }
}