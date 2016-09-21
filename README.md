# Laravel-CreatedByPolicy
A trait that implements authorization using simple booleans in your policy classes. It provides create, write and read modes and world, authenticated and creator user-based security.

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

Laravel has a limitation that policies only works if there is a user, since our policies include world read/write (guest access) we need to workaround it with a provider. In `config/app.php` add to the providers array:

    Malhal\CreatedByPolicy\WorldServiceProvider::class

All this does is if `Auth::user()` is null it sets it to a new User which has no ID. This means that `Auth::check()` will no longer work as designed, and instead you have to check for guest with `is_null(Auth::user()->getKey())`.

You shouldn't need to but just in case, disable security checking on a query that alreay had the global scope applied remove it with:

    $query->withoutCreatedByPolicy()

There is an issue when using the CreatedBy trait on the User model with auto-increment. MySQL cannot insert a new record and include a foreign key to the newly created ID. This issue doesn't occur if using UUID for keys, however you can still use the policy with auto-increment by implementing this method in your User model, which assumes user records are always created by the user:

    public function getCreatedByForeignKey(){
        return $this->getKey();
    }
    
## Installation

[PHP](https://php.net) 5.6.4+ and [Laravel](http://laravel.com) 5.3+ are required.

To get the latest version of Laravel CreatedByPolicy, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require malhal/laravel-createdbypolicy dev-master
```
