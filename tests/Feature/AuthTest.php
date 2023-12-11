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
            'password' => 'JustAPassword2',
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
            'password' => 'JustAPassword2',
        ];
        $this->post($this->registerUrl, $request);

        // Register user email already
        $request = [
            'name' => 'Rozikin',
            'email' => 'geronimo794@gmail.com',
            'password' => 'JustAPassword2',
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
                    'password' => 'JustAPassword2',
                ],
                'resp' => 'errors.name'
            ],
            [
                'req' => [
                    'name' => 'Ach Rozikin',
                    'email' => '',
                    'password' => 'JustAPassword2',
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
                    'password' => 'A',
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
            'password' => 'JustAPassword2',
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
            'password' => 'JustAPassword2',
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
