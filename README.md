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
## 3. Standarize your response structure with ResponseHelper
Create new folder and new file
```bash
app/Helpers/ResponseHelper.php
```
Fill the file with your response standar
```php
<?php

namespace App\Helpers;

class ResponseHelper{
	private static function build(int $httpStatus, bool $fulfilled, mixed $data, mixed $errors, mixed $pagination){
		$response['statusCode'] = $httpStatus;
		$response['fulfilled'] = $fulfilled;
		if(!empty($data)){
			$response['data'] = $data;
		}
		if(!empty($errors)){
			$response['errors'] = $errors;
		}
		if(!empty($pagination)){
			$response['pagination'] = $pagination;
		}
		return $response;
	}
	public static function buildError(int $httpStatus, mixed $errors){
		return self::build($httpStatus, \false, \null, $errors, \null);
	}
}
```
Use this new response helper on "StoreUserRequest.php"
```php
use App\Helpers\ResponseHelper;

...

/**
 * Overriding function to change the behaviour
 */
public function failedValidation(Validator $validator){
	$response = ResponseHelper::buildError(422, $validator->errors());
	throw new HttpResponseException(response()->json($response, 422));
}
```
Test again with failed error. And you can reuse this response helper your next response.
## 4. Organize Your API on Different Namespace Folder
Sometimes, we don't to mix our normal controller with our API controller. To achive that, we can move our API controller.
First create folder API on Controllers folder
```bash
mkdir app/Http/Controllers/Api
```
Move your API Controller to API Folder. And change the namespace to "App\Http\Controllers\Api"
```php
namespace App\Http\Controllers\Api;

...
```
Open your **web.php** router file, move your resource router to **api.php**
```php
use App\Http\Controllers\Api\UserController;

...

Route::resource('users', UserController::class)->only([
    'store',
]);

```
Now you can access your API via "api" prefix and store your API controller in API Folder.
## 5. Force All API Response to JSON
Sometime for our API response, we just all the response in JSON format. We can force the response to be json format with Force JSON Middle.
First, create your middleware.
```bash
php artisan make:middleware ForceJsonResponse
```
Set the middleware on file.
```bash
app/Http/Middleware/ForceJsonResponse.php
```
Set the handle
```php
public function handle($request, Closure $next){
    $request->headers->set('Accept', 'application/json');
    return $next($request);
}
```
This is gonna change all the incoming request just accept application/json as response.
Activate your middleware for your API on file
```bash
app/Http/Kernel.php 
```
Add your new middleware
```php
protected $middlewareGroups = [        
    'api' => [
        \App\Http\Middleware\ForceJsonResponse::class,
		...
    ],
	...
];
```
Now try to error the laravel response with change db with unexist db name. Now, you will know the different.
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
- https://laravel.com/docs/10.x/sanctum#introduction
- https://github.com/LaravelDaily/laravel-tips/blob/master/api.md