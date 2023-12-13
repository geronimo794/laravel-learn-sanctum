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

## 6. Simulate Register, Login and Check User Token
We are gonna simulate a register, login, check user token.
First, write the route for register and login API.
You can remove this line
```php
Route::resource('users', UserController::class)->only([
    'store',
]);
```
To this line
```php
Route::post('register', [UserController::class, 'store']);
Route::post('login', [UserController::class, 'login']);
```
Create new request file for login
```bash
app/Http/Requests/LoginUserRequest.php
```
Add validation to login user Request
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Exceptions\HttpResponseException;


class LoginUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ];
    }

    /**
     * Overriding function to change the behaviour
     */
    public function failedValidation(Validator $validator)
    {
        $response = ResponseHelper::buildError($validator->errors());
        throw new HttpResponseException(response()->json($response, 422));
    }
    
}
```
Add login function on file
```bash
app/Http/Controllers/Api/UserController.php
```
Add this function
```php
...
    /**
     * Login process
     */
    public function login(LoginUserRequest $request){
        $user = User::where('email', $request->email)->first();

        if(!$user){
            return response()->json(ResponseHelper::buildError(['email' => [ResponseHelper::NOT_FOUND]]), 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(ResponseHelper::buildError(['password' => [ResponseHelper::NOT_FOUND]]), 404);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ]);
    }
...
```
Now you can try to register and login.
After login, you can use the Bearer token to access request 
```
[GET] {{baseUrl}}api/user
```
*There is some code refactor on ResponseHelper but it doesn't affect so much.
## 7. Testing Register and Login
Time to test your current API. To test our application, we are gonna use sqlite on memory. First, uncomment on this file "phpunit.xml"
```xml
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
```
And then create your test file on
```bash
tests/Feature/AuthTest.php
```
Fill that file, with this test script. You can read the information about the test in the comment.
```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    public $registerUrl = '/api/register';
    public $loginUrl = '/api/login';

    public function test_success_register(): void
    {
        $request = [
            'name' => 'Ach Rozikin',
            'email' => 'geronimo794@gmail.com',
            'password' => 'JustAPassword',
        ];
        $response = $this->post($this->registerUrl, $request);
        $response->assertStatus(201)
            ->assertJsonPath('data.token_type', 'Bearer');
    }
    public function test_failed_register_email_already_exist(): void
    {
        // Register user
        $request = [
            'name' => 'Ach Rozikin',
            'email' => 'geronimo794@gmail.com',
            'password' => 'JustAPassword',
        ];
        $this->post($this->registerUrl, $request);

        // Register user email already
        $request = [
            'name' => 'Rozikin',
            'email' => 'geronimo794@gmail.com',
            'password' => 'JustAPassword',
        ];
        $response = $this->post($this->registerUrl, $request);
        $response->assertStatus(422)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->has('errors.email')
                    ->etc()
            );
    }
    public function test_failed_register_form_not_complete(): void
    {
        // Create test case and expected response
        $testCase = [
            [
                'req' => [
                    'name' => '',
                    'email' => 'geronimo794@gmail.com',
                    'password' => 'JustAPassword',
                ],
                'resp' => 'errors.name'
            ],
            [
                'req' => [
                    'name' => 'Ach Rozikin',
                    'email' => '',
                    'password' => 'JustAPassword',
                ],
                'resp' => 'errors.email'
            ],
            [
                'req' => [
                    'name' => 'Ach Rozikin',
                    'email' => 'geronimo794@gmail.com',
                    'password' => '',
                ],
                'resp' => 'errors.password'
            ],
            [
                'req' => [
                    'name' => 'Ach Rozikin',
                    'email' => 'geronimo794@',
                    'password' => 'JustAPassword',
                ],
                'resp' => 'errors.email'
            ],
            [
                'req' => [
                    'name' => 'Ach Rozikin',
                    'email' => 'geronimo794@gmail.com',
                    'password' => 'a',
                ],
                'resp' => 'errors.password'
            ],

        ];

        foreach ($testCase as $perTestCase) {
            $response = $this->post($this->registerUrl, $perTestCase['req']);
            $response->assertStatus(422)
                ->assertJson(
                    fn (AssertableJson $json) =>
                    $json->has($perTestCase['resp'])
                        ->etc()
                );
        }
    }
    public function test_success_login(): void
    {
        $request = [
            'name' => 'Ach Rozikin',
            'email' => 'geronimo794@gmail.com',
            'password' => 'JustAPassword',
        ];
        $response = $this->post($this->registerUrl, $request);
        $response->assertStatus(201)
            ->assertJsonPath('data.token_type', 'Bearer');

        $response = $this->post($this->loginUrl, $request);
        $response->assertStatus(200)
            ->assertJsonPath('data.token_type', 'Bearer');
    }
    public function test_login_failed(): void
    {
        $request = [
            'name' => 'Ach Rozikin',
            'email' => 'geronimo794@gmail.com',
            'password' => 'JustAPassword',
        ];
        $response = $this->post($this->registerUrl, $request);
        $response->assertStatus(201)
            ->assertJsonPath('data.token_type', 'Bearer');

        // Change to different password
        $request['password'] = 'No Password';
        $response = $this->post($this->loginUrl, $request);
        $response->assertStatus(401)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->has('errors.password')
                    ->etc()
            );

        // Change to not existing user
        $request['email'] = 'ach.rozpp@gmail.com';
        $response = $this->post($this->loginUrl, $request);
        $response->assertStatus(404)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->has('errors.email')
                    ->etc()
            );
    }
}
```
Please read carefully about the test case on the comment.
## 8. Resource Implementation and With User Authorization Validation
First, let's make a routing group with Laravel Sanctum middleware. You can edit it on "routes/api.php"

```php
use App\Helpers\ResponseHelper;
...
Route::middleware('auth:sanctum')->group(function () {
    Route::resource('users', UserController::class)->except([
        'store'
    ])->missing(function (Request $request) {
        return response()->json(ResponseHelper::buildNotFound(), 404);
    });
});
```
Update the authorize function on "app/Http/Requests/UpdateUserRequest.php". Because we just want to allow user that login to change data for it self. It cannot edit another data.
```php
/**
 * Determine if the user is authorized to make this request.
 */
public function authorize(): bool{
    // Compare current data with current logged user
    return auth()->user()->id == $this->route('user')->id;
}
```
Create rules for our UpdateUserRequest
```php
/**
 * Get the validation rules that apply to the request.
 *
 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
 */
public function rules(): array
{
    return [
        'name' => 'required',
        'email' => 'required|email|unique:users,email,'.auth()->user()->id,
        'password' => 'min:6',
    ];
}
/**
 * Overriding function to change the behaviour
 */
public function failedValidation(Validator $validator)
{
    $response = ResponseHelper::buildError($validator->errors());
    throw new HttpResponseException(response()->json($response, 422));
}
```
Update the update on "app/Http/Controllers/Api/UserController.php"
```php
/**
 * Update the specified resource in storage.
 */
public function update(UpdateUserRequest $request, User $user)
{
    $user->name = $request->name;
    $user->email = $request->email;

    if(!empty($request->password)){
        $user->password = Hash::make($request->password);
    }
    if(!$user->save()){
        $response = ResponseHelper::buildError(['user' => [ResponseHelper::SAVE_FAILED]]);
        return response()->json($response, 500);
    }
    $response = ResponseHelper::buildSuccess($user);
    return response()->json($response, 200);
}

```
Update delete methode on "UserController.php"
```php
/**
 * Remove the specified resource from storage.
 */
public function destroy(User $user)
{
    if(auth()->user()->id != $user->id){
        return response()->json(ResponseHelper::buildUnauthorize(), 401);
    }
    $user->delete();
    $response = ResponseHelper::buildSuccess($user);
    return response()->json($response, 200);
}
```
Now you can delete your resource, but only the logged user can use this for their current data.
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