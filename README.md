# Laravel-CreatedBySecurity
A trait that adds simple security to your models. It requires [Laravel-CreatedBy](https://github.com/malhal/Laravel-CreatedBy) which provides the functionality of tracking which user created and updated models. 
It provides create, write and read modes and world, authenticated and creator user-based security.

Since it makes use of the [CreatedBy](https://github.com/malhal/Laravel-CreatedBy) trait you need to follow its setup instructions by adding the table columns and relations for the created_by_id and updated_by_id.

Enter the artisan command to make a policy (replacing the name with your model name):

    php artisan make:policy VenuePolicy

Now in your project open the class and add use this following trait:

    use CreatedByPolicy;

You can override the default security levels by implementing protected variables, e.g. to prevent guests from reading:

    const WORLD_READ = true;

Now you can use the built in authorization methods, exceptions will be thrown in the case of no access, and for creatorRead a global scope will be added to limit queries to only records created by the currently authenticated user.

To temporarily disable security checking on a query use:

    $query->withoutCreatedBySecurity()

    
## Installation

[PHP](https://php.net) 5.6.4+ and [Laravel](http://laravel.com) 5.3+ are required.

To get the latest version of Laravel CreatedBySecurity, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require malhal/createdbysecurity dev-master
```
