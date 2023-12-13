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
        // Build response
        $response = ResponseHelper::buildSuccess(User::all());
        return response()->json($response, 201);
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
        // Build response
        $response = ResponseHelper::buildSuccess($user);
        return response()->json($response, 201);
    }

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
