<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CreateFeeRequest;
use App\Http\Requests\UpdateFeeRequest;
use Illuminate\Support\Str;
use App\Traits\ResponseAPI;
use App\Models\Fee;

class FeeController
{
    use ResponseAPI;

    public function createFee(CreateFeeRequest $request)
    {
        $fee = Fee::create([
            'id'        =>  Str::uuid(),
            'name'       => $request->name,
            'amount'     => $request->amount,
            'description'=> $request->description,
            'period'      => $request->period,
        ]);

        return $this->sendSuccess('Iuran berhasil dibuat', $fee);
    }

    public function deleteFee($id)
    {
        $fee = Fee::find($id);

        if (!$fee) {
            return $this->sendError('Iuran tidak ditemukan.', 404);
        }

        $fee->delete();

        return $this->sendSuccess('Iuran berhasil dihapus.');
    }

    public function updateFee(UpdateFeeRequest $request, $id)
    {
        try {
            $fee = Fee::find($id);

            if (!$fee) {
                return $this->sendError('Iuran tidak ditemukan.', 404, '404 Not Found');
            }

            $fee->update([
                'name'       => $request->name ?? $fee->name,
                'amount'   => $request->amount ?? $fee->amount,
                'description'    => $request->description ?? $fee->description,
                'period'    => $request->period ?? $fee->period,
                'updated_at' => now(),
            ]);

            return $this->sendSuccess('Iuran berhasil diupdate.', $fee->fresh());

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500, '500 Internal Server Error');
        }
    }

    public function getAllFee(Request $request)
    {
        try {
            $page      = $request->query('page', 1);
            $pageSize  = $request->query('page_size', 10);
            $search    = $request->query('search', '');
            $sortBy    = $request->query('sort_by', 'created_at');
            $sortOrder = $request->query('sort_order', 'desc');
            $period    = $request->query('period');

            $query = Fee::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            if ($period) {
                $query->where('period', $period);
            }

            $allowedSortBy = ['name', 'amount', 'created_at'];
            $sortBy    = in_array($sortBy, $allowedSortBy) ? $sortBy : 'created_at';
            $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';
            $query->orderBy($sortBy, $sortOrder);

            $fees = $query->paginate($pageSize, ['*'], 'page', $page);

            if ($fees->isEmpty()) {
                return $this->sendError('Iuran tidak ditemukan', 404, '404 Not Found');
            }

            $data = $this->PaginatedResponse($fees, $page);
            return $this->sendSuccess('Berhasil mengambil semua data iuran', $data);

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500, '500 Internal Server Error');
        }
    }

    public function getFeeDetail(Request $request, $id){
        try {
            $fee = Fee::find($id);

            if (!$fee) {
                return $this->sendError('Iuran tidak ditemukan', 404, '404 Not Found');
            }

            return $this->sendSuccess('Berhasil mengambil data detail iuran', $fee);

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500, '500 Internal Server Error');
        }
    }

}
