<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ResponseAPI;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    use ResponseAPI;

    public function login(LoginRequest $request)
    {
        try {
            $user = User::select(['id', 'username', 'email', 'password', 'name', 'role_id'])
                ->with(['roles:id,name'])
                ->where('email', $request->input('email'))
                ->where('status', true)
                ->first();

            if (!$user) {
                return $this->sendError('Unauthorized', 401);
            }

            if (!Hash::check($request->input('password'), $user->password)) {
                return $this->sendError('Wrong credentials', 401);
            }

            $token = Auth::login($user);

            $data = [
                'user' => [
                    'id'  => $user->id,
                    'username' => $user->username,
                    'name'     => $user->name,
                    'email'    => $user->email,
                    'roles'    => $user->roles,
                ],
                'expired_at' => Carbon::now()->addMinutes((int) config('jwt.ttl'))->timestamp,
                'token'      => $token,
            ];

            return $this->sendSuccess('Login successful', $data);

        } catch (Exception $ex) {
            Log::error('Login error: ', [
                'input'   => $request->all(),
                'message' => $ex->getMessage(),
                'trace'   => $ex->getTraceAsString(),
            ]);
            return $this->sendError('Login failed', 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            Auth::logout();
            return $this->sendSuccess('Logout successful', []);

        } catch (Exception $ex) {
            Log::error('Logout error: ', [
                'input'   => $request->all(),
                'message' => $ex->getMessage(),
                'trace'   => $ex->getTraceAsString(),
            ]);
            return $this->sendError('Logout failed', 500);
        }
    }
}