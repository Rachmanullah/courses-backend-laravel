<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $data = User::all();

        if ($data->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No Users Found',
            ], 404);
        }
        // return new UserResource($data);
        return response()->json([
            'status' => 200,
            'message' => 'Get Data Users Success',
            'data' => $data,
        ], 200);
    }

    public function login(UserLoginRequest $request): UserResource
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => [
                        'email or password wrong'
                    ]
                ]
            ], 401));
        }

        $user->token = Str::uuid()->toString();
        $user->Save();

        return new UserResource($user);
    }

    public function store(UserRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (User::where('email', $data['email'])->count() == 1) {
            throw new HttpResponseException(response([
                'errors' => [
                    'email' => [
                        'email alredy exists'
                    ]
                ]
            ], 400));
        }

        $user = new User($data);
        $user->password = Hash::make($data['password']);
        $user->save();

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function getdataByid(string $id)
    {
        try {
            $user = User::findOrFail($id);

            return response()->json([
                'status' => 200,
                'message' => 'Success',
                'data' => $user,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'message' => 'Not Found',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();

        $data = User::find($user['id']);
        $data->token = null;
        $data->save();

        return response()->json([
            'data' => $data
        ])->setStatusCode(200);
    }

    public function update(UserUpdateRequest $request, string $id): UserResource
    {

        $data = $request->validated();

        $user = User::find($id);

        $user->name = $data['name'] ? $data['name'] : $user->name;
        $user->email = $data['email'] ? $data['email'] : $user->email;
        $user->password = $data['password'] ? Hash::make($data['password']) : $user->password;
        $user->role = $data['role'] ? $data['role'] : $user->role;
        $user->save();

        return new UserResource($user);
    }

    public function destroy(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User Not Found',
            ], 404);
        }

        try {
            $user->delete();

            return response()->json([
                'status' => 204,
                'message' => 'User deleted successfully',
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
