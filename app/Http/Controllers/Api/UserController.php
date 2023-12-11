<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ResponseHelper;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request){
        // Just procced the valid response
        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password)
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        // Build response
        $response = ResponseHelper::buildSuccess([
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ]);
        return response()->json($response, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }

    /**
     * Login process
     */
    public function login(LoginUserRequest $request){
        $user = User::where('email', $request->email)->first();

        if(!$user){
            return response()->json(ResponseHelper::buildError(['email' => [ResponseHelper::NOT_FOUND]]), 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(ResponseHelper::buildError(['password' => [ResponseHelper::INVALID]]), 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = ResponseHelper::buildSuccess([
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ]);
        return response()->json($response);
    }

}
