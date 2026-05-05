<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\ResponseAPI;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Str;

class UserController
{
    use ResponseAPI;

    public function createUser(CreateUserRequest $request)
    {
        $user = User::create([
            'id'        =>  Str::uuid(),
            'name'       => $request->name,
            'username'   => $request->username,
            'address'    => $request->address,
            'gender'     => $request->gender,
            'role_id'       => $request->role_id,
            'email'      => $request->email,
            'password'   => bcrypt($request->password),
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->sendSuccess('User berhasil dibuat.', $user);
    }

    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->sendError('User tidak ditemukan.', 404);
        }

        $user->delete();

        return $this->sendSuccess('User berhasil dihapus.');
    }

    public function updateUser(UpdateUserRequest $request, $id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->sendError('User tidak ditemukan.', 404, '404 Not Found');
            }

            $user->update([
                'name'       => $request->name ?? $user->name,
                'username'   => $request->username ?? $user->username,
                'address'    => $request->address ?? $user->address,
                'gender'     => $request->gender ?? $user->gender,
                'role'       => $request->role ?? $user->role,
                'email'      => $request->email ?? $user->email,
                'password'   => $request->password ? bcrypt($request->password) : $user->password,
                'updated_at' => now(),
            ]);

            return $this->sendSuccess('User berhasil diupdate.', $user->fresh());

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500, '500 Internal Server Error');
        }
    }

    public function getAllUser(Request $request)
    {
        try {
            $page      = $request->query('page', 1);
            $pageSize  = $request->query('page_size', 10);
            $search    = $request->query('search', '');
            $sortBy    = $request->query('sort_by', 'created_at');
            $sortOrder = $request->query('sort_order', 'desc');

            $query = User::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }

            $allowedSortBy = ['name', 'email', 'created_at'];
            $sortBy    = in_array($sortBy, $allowedSortBy) ? $sortBy : 'created_at';
            $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';
            $query->orderBy($sortBy, $sortOrder);

            $users = $query->paginate($pageSize, ['*'], 'page', $page);

            if ($users->isEmpty()) {
                return $this->sendError('No users found', 404, '404 Not Found');
            }

            $data = $this->PaginatedResponse($users, $page);
            return $this->sendSuccess('Get All User Success', $data);

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500, '500 Internal Server Error');
        }
    }

    public function getUserDetail(Request $request, $id){
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->sendError('User not found', 404, '404 Not Found');
            }

            return $this->sendSuccess('Get User Detail Success', $user);

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500, '500 Internal Server Error');
        }
    }

}
