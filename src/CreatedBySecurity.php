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
use Malhal\CreatedBy\CreatedBy;

trait CreatedBySecurity
{
    use CreatedBy;

    protected $disableCreatedBySecurity;

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

    public static function bootCreatedBySecurity()
    {
        static::addGlobalScope(new CreatedBySecurityScope());

        static::creating(function($model){
            if($model->disableCreatedBySecurity){
                return;
            }

            if (!$model->getWorldCreate() && $model->getAuthenticatedCreate()) {
                if(is_null($model->currentUser())) {
                    throw new AuthorizationException('Only authenticated can create.'); // CREATE not permitted
                }
            } else if (!$model->getWorldCreate()) {
                throw new AuthorizationException('No-one can create.');
            }
        });

        // at this stage, we only need to check if it is creator write only and if they are the creator.
        $writing = function($model) {

            // Since the saving event occurs before creates we need to filter these.
            // This allows creates to still work even if creator write is false.
            if(!$model->exists){
                return;
            }

            if($model->disableCreatedBySecurity){
                return;
            }

            if (!$model->getWorldWrite() && !$model->getAuthenticatedWrite() && $model->getCreatorWrite()) {
                $user = $model->currentUser();
                if (is_null($user) || $user->getKey() != $model->getAttribute($model->createdByForeignKey())) {
                    throw new AuthorizationException('Only the creator can write.');
                }
            }
            else if (!$model->getWorldWrite() && $model->getAuthenticatedWrite()) {
                if (is_null($model->currentUser())) {
                    throw new AuthorizationException('Only authenticated can write.');
                }
            } else if (!$model->getWorldWrite()) {
                throw new AuthorizationException('No-one can write.');
            }

        };

        static::deleting($writing);
        // by using the saving event rather than updating, even if the update is idempotent (not dirty) but the user is different we can still check
        // if only creator can write. Unfortunately saving is also called during a create, so the callback has to filter those out.
        static::saving($writing);
    }

    public function deleteWithoutCreatedBySecurity()
    {
        $this->disableCreatedBySecurity = true;

        $deleted = $this->delete();

        $this->disableCreatedBySecurity = false;

        return $deleted;
    }

    public function saveWithoutCreatedBySecurity(array $options = [])
    {
        $this->disableCreatedBySecurity = true;

        $saved = $this->save($options);

        $this->disableCreatedBySecurity = false;

        return $saved;
    }

    public function getWorldCreate(){
        return defined('self::WORLD_CREATE') ? self::WORLD_CREATE : false;
    }

    public function getWorldRead(){
        return defined('self::WORLD_READ') ? self::WORLD_READ : true;
    }

    public function getWorldWrite(){
        return defined('self::WORLD_WRITE') ? self::WORLD_WRITE : false;
    }

    public function getAuthenticatedCreate(){
        return defined('self::AUTHENTICATED_CREATE') ? self::AUTHENTICATED_CREATE : true;
    }

    public function getAuthenticatedRead(){
        return defined('self::AUTHENTICATED_READ') ? self::AUTHENTICATED_READ : false;
    }

    public function getAuthenticatedWrite(){
        return defined('self::AUTHENTICATED_WRITE') ? self::AUTHENTICATED_WRITE : false;
    }

    public function getCreatorRead(){
        return defined('self::CREATOR_READ') ? self::CREATOR_READ : false;
    }

    public function getCreatorWrite(){
        return defined('self::CREATOR_WRITE') ? self::CREATOR_WRITE : true;
    }


}