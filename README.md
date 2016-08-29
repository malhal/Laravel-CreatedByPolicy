# Laravel-CreatedBySecurity
A trait that adds simple security to your models. It requires [Laravel-CreatedBy](https://github.com/malhal/Laravel-CreatedBy) which provides the functionality of tracking which user created and updated models. 
It provides create, write and read modes and world, authenticated and creator user-based security.

In your model use this trait:

    use CreatedBySecurity;
    
Since it makes use of the [CreatedBy](https://github.com/malhal/Laravel-CreatedBy) trait you need to follow its setup instructions by adding the table columns and relations for the created_by_id and updated_by_id.

Wwhen querying models that use the trait, the security is automatically applied to the global scope, this means all queries will be secured. If read access is creator only then only the records they created will be returned. If there is no read access an authorization exception will be thrown. Similarly security will be applied to all model create, update and delete.

You can override the default security levels by implementing protected variables, e.g. to prevent guests from reading:

    const WORLD_READ = true;

To temporarily disable security checking on a query use:

    $query->withoutCreatedBySecurity()

To save without the security check, use:

    $model->saveWithoutCreatedBySecurity();
    
To delete without the security check (write permission), use:

    $model->deleteWithoutCreatedBySecurity();
    
## Installation

[PHP](https://php.net) 5.6.4+ and [Laravel](http://laravel.com) 5.3+ are required.

To get the latest version of Laravel CreatedBySecurity, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require malhal/createdbysecurity dev-master
```
