<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\ResponseAPI;

class UserController extends Controller
{
    use ResponseAPI;

    public function createUser(){

    }

    public function deleteUser(){

    }

    public function updateUser(){

    }

    public function getAllUser(Request $request){
        try {
            $currentPage = $request->query('page', 1);
            $users = User::paginate(10, ['*'], 'page', $currentPage);

            if ($users->isEmpty()) {
                return $this->sendError('No users found', 404, '404 Not Found');
            }

            $data = $this->PaginatedResponse($users, $currentPage);
            return $this->sendSuccess('Get All User Success', $data);

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500, '500 Internal Server Error');
        }
    }

    public function getUserDetail(){
        
    }

}
