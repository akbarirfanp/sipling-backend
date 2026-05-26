<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Traits\ResponseAPI;

class RoleController
{
    use ResponseAPI;

    public function getAllRole(Request $request)
    {
        try {
            $page      = $request->query('page', 1);
            $pageSize  = $request->query('page_size', 10);
            $search    = $request->query('search', '');
            $sortBy    = $request->query('sort_by', 'created_at');
            $sortOrder = $request->query('sort_order', 'desc');

            $query = Role::query();

            $roles = $query->paginate($pageSize, ['*'], 'page', $page);

            if ($roles->isEmpty()) {
                return $this->sendError('No roles found', 404, '404 Not Found');
            }

            $data = $this->PaginatedResponse($roles, $page);
            return $this->sendSuccess('Get All Role Success', $data);

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500, '500 Internal Server Error');
        }
    }
}
