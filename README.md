# Laravel Sanctum
Laravel Sanctum provides a featherweight authentication system for SPAs (single page applications), mobile applications, and simple, **token based APIs**. Sanctum allows each user of your application to generate multiple API tokens for their account. These tokens may be granted abilities / scopes which specify which actions the tokens are allowed to perform.

Laravel Sanctum provides both **stateful (session-based)** and **stateless (token-based)** authentication options, while JWT operates entirely statelessly.

Laravel Sanctum offers this feature by storing **user API tokens** in a **single database table** and **authenticating incoming HTTP requests** via the Authorization header which should contain a valid API token.

# Learning Journey
## 1. Create Resource Controller
```bash
php artisan make:controller UserController --model=User --resource --requests --api
```
Add create user to router
```php
Route::resource('users', UserController::class)->only([
    'store',
]);
```
Add exception to CSRF on
```bash
app/Http/Middleware/VerifyCsrfToken.php
```
Authorize on Request
```php
/**
 * Determine if the user is authorized to make this request.
 */
public function authorize(): bool{
	// Change to true
	return true;
}
```
Now you can edit your 
```bash
app/Http/Controllers/UserController.php
```
On method
```php
/**
 * Store a newly created resource in storage.
 */
public function store(StoreUserRequest $request){
	//
	echo 'Hello world!';
}
```
## 2. Create Form Request For Request Validation
Create rules on file
```bash
app/Http/Requests/StoreUserRequest.php
```
Set the rules
```php
public function rules(): array{
	return [
		'name' => 'required',
		'email' => 'required|email|unique:users,email,except,id',
		'password' => 'required|min:6',
	];
}
```
Override function "failedValidation" on "StoreUserRequest.php"
```php
use Illuminate\Http\Exceptions\HttpResponseException;

...

/**
 * Overriding function to change the redirect behaviour
 */
public function failedValidation(Validator $validator)
{
	throw new HttpResponseException(response()->json([
		'success' => false,
		'message' => 'Validation errors',
		'data'    => $validator->errors()
	], 422));
}
```
Test your API with failed request.

## Overriding Default Models
You can overidding default models of Sanctum with model 
```bash
app/Models/Sanctum/PersonalAccessToken.php
```
And then set it up on boot AppServiceProvider
```php
use Laravel\Sanctum\Sanctum;
use App\Models\Sanctum\PersonalAccessToken;

...

/**
 * Bootstrap any application services.
 */
public function boot(): void
{
	Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
}

...

```

# Source
https://laravel.com/docs/10.x/sanctum#introduction