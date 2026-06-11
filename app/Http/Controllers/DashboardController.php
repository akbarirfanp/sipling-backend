<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ResponseAPI;
use App\Models\User;
use App\Models\Bill;
use App\Models\Fee;
use App\Models\Payment;
use App\Http\Requests\GenerateReportRequest;
use Carbon\Carbon;

class DashboardController
{
    use ResponseAPI;
    
    public function getDashboardStatistics(Request $request)
    {
        $data = [
            'total_warga' => User::whereHas('roles', fn($q) => $q->where('name', 'Warga'))->count(),
            'unpaid_bill' => Bill::where('status', 'unpaid')->count(),
            'monthly_income' => Payment::where('status', 'success')
                                    ->whereYear('payment_date', now()->year)
                                    ->whereMonth('payment_date', now()->month)
                                    ->sum('amount'),
        ];

        return $this->sendSuccess('Get Dashboard Statistics Success', $data);
    }

    public function getRecentActivity(Request $request)
    {
        try {
            $page     = $request->query('page', 1);
            $pageSize = $request->query('page_size', 10);
    
            $recentActivity = Payment::query()
                ->join('bills', 'payments.bill_id', '=', 'bills.id')
                ->join('users', 'bills.user_id', '=', 'users.id')
                ->join('fees', 'bills.fee_id', '=', 'fees.id')
                ->where('payments.status', 'success')
                ->orderBy('payments.payment_date', 'desc')
                ->paginate($pageSize, [
                    'payments.id',
                    'payments.payment_date',
                    'payments.status',
                    'bills.id as bill_id',
                    'bills.gross_amount',
                    'users.id as user_id',
                    'users.name as user_name',
                    'fees.id as fee_id',
                    'fees.name as fee_name',
                ], 'page', $page);
    
            $data = $this->PaginatedResponse($recentActivity, $page);
    
            return $this->sendSuccess('Berhasil mengambil recent activity', $data);
    
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500, '500 Internal Server Error');
        }
    }

    public function generateReport(Request $request)
    {
        try {
            $request->validate([
                'start_date' => ['required', 'date', 'date_format:Y-m-d'],
                'end_date'   => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            ]);

            $page      = $request->query('page', 1);
            $pageSize  = $request->query('page_size', 10);
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate   = Carbon::parse($request->end_date)->endOfDay();

            $payments = Payment::query()
                ->join('bills', 'payments.bill_id', '=', 'bills.id')
                ->join('users', 'bills.user_id', '=', 'users.id')
                ->join('fees', 'bills.fee_id', '=', 'fees.id')
                ->where('payments.status', 'success')
                ->whereBetween('payments.payment_date', [$startDate, $endDate])
                ->orderBy('users.name', 'asc')
                ->paginate($pageSize, [
                    'payments.payment_date',
                    'bills.invoice_number',
                    'bills.gross_amount',
                    'bills.status',
                    'users.name as user_name',
                    'fees.name as fee_name',
                ], 'page', $page);

            if ($payments->isEmpty()) {
                return $this->sendError('Data tidak ditemukan', 404, '404 Not Found');
            }

            $data = $this->PaginatedResponse($payments, $page);

            return $this->sendSuccess('Berhasil mengambil data report', $data);

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500, '500 Internal Server Error');
        }
    }
}
