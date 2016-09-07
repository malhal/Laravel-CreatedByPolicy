# Laravel-CreatedBySecurity
A trait that adds simple security to your model authorization policies. It provides create, write and read modes and world, authenticated and creator user-based security.

It requires [Laravel-CreatedBy](https://github.com/malhal/Laravel-CreatedBy) which provides the functionality of tracking which user created and updated models, so please follow its setup instructions including adding the table columns and relations for the created_by_id and updated_by_id.

## Configuration

Enter the standard artisan command to make a policy (replacing the name with your model name):

    php artisan make:policy VenuePolicy
    
As normal, add the policy to AuthServiceProvider policies array, e.g.

    protected $policies = [
        'App\Venue' => 'App\Policies\VenuePolicy',
    ];   

Now for something different, in your project open the generated policy class and add the following trait:

    use CreatedByPolicy;
    
The default security params are shown in the CreateByPolicy trait php file for easy access, and look like this:

    // const WORLD_CREATE = false;
    // const WORLD_READ = true;
    // const WORLD_WRITE = false;
    // const AUTHENTICATED_CREATE = true;
    // const AUTHENTICATED_READ = false;
    // const AUTHENTICATED_WRITE = false;
    // const CREATOR_READ = false;
    // const CREATOR_WRITE = true;
    
You can override the default security levels by implementing the constants, e.g. to prevent guests from reading:

    const WORLD_READ = true;
    
Now you can use Laravel's built-in [authorization](https://laravel.com/docs/5.3/authorization) methods as normal, exceptions will be thrown in the case of no access, and for creatorRead a global scope will be added to limit queries to only records created by the currently authenticated user, for example:

    protected function create(Request $request){
        $this->authorize('create', Venue::class);
        //
    }

Laravel has a limitation that authorization only works if there is a user, since our security model supports world access, we had to create a guest user in the case of no auth user. I haven't quite figured out the best place to do this, in the meant time put it in App\Providers\AuthServiceProvider boot:

    public function boot()
    {
        $this->registerPolicies();

        CreatedByPolicy::register(); // Set guest user if no user.
    }


This means that `Auth::check()` will no longer work as designed, and instead you have to test `!(Auth::user() instanceof CreatedByGuest)`.

You shouldn't need this but just in case, to temporarily disable security checking on a query that has had the scope added by an authorization read check use:

    $query->withoutCreatedBySecurity()

## Installation

[PHP](https://php.net) 5.6.4+ and [Laravel](http://laravel.com) 5.3+ are required.

To get the latest version of Laravel CreatedBySecurity, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require malhal/createdbysecurity dev-master
```
