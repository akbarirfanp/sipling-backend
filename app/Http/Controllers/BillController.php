<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Bill;
use App\Models\User;
use App\Models\Role;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Traits\ResponseAPI;
use Illuminate\Support\Str;
use App\Http\Requests\GenerateBillRequest;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    use ResponseAPI;

    public function generate(GenerateBillRequest $request)
    {
        try {
            $bills = DB::transaction(function () use ($request) {
                $fee = Fee::findOrFail($request->fee_id);

                $alreadyGeneratedQuery = Bill::where('fee_id', $fee->id);

                if ($fee->period === 'Bulanan') {
                    $alreadyGeneratedQuery
                        ->whereYear('created_at', now()->year)
                        ->whereMonth('created_at', now()->month);
                } elseif ($fee->period === 'Tahunan') {
                    $alreadyGeneratedQuery
                        ->whereYear('created_at', now()->year);
                }

                if ($alreadyGeneratedQuery->exists()) {
                    throw new \Exception(
                        $fee->period === 'Bulanan'
                            ? 'Iuran ini sudah di-generate untuk bulan ini'
                            : 'Iuran ini sudah di-generate untuk tahun ini'
                    );
                }

                $users = $request->filled('user_ids')
                    ? User::whereIn('id', $request->user_ids)->get()
                    : User::whereHas('roles', fn($q) => $q->where('name', 'Warga'))->get();

                if ($users->isEmpty()) {
                    throw new \Exception('Tidak ada user dengan role Warga.');
                }

                $bills = [];
                foreach ($users as $user) {
                    $bill = Bill::create([
                        'user_id'      => $user->id,
                        'gross_amount' => $fee->amount,
                        'status'       => 'unpaid',
                        'due_date'     => $request->due_date,
                        'fee_id'       => $fee->id,
                    ]);

                    $bills[] = $bill->load('fee');
                }

                return $bills;
            });

            return $this->sendSuccess('Tagihan berhasil digenerate', [
                'total' => count($bills),
                'data'  => $bills,
            ]);

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 422, '422 Unprocessable');
        }
    }
    public function getSnapToken(Request $request, $billId)
    {
        try {
            if (empty($billId)) {
                return $this->sendError('Parameter ID tagihan harus diisi', 400, '400 Bad Request');
            }
 
            $bill = Bill::with(['user', 'fee'])->findOrFail($billId);
 
            if (!$bill->user) {
                return $this->sendError('User tagihan tidak ditemukan', 404, '404 Not Found');
            }
 
            if ($request->user()->id !== $bill->user_id) {
                return $this->sendError('Unauthorized', 403, '403 Forbidden');
            }
 
            if ($bill->status !== 'unpaid') {
                return $this->sendError('Tagihan ini sudah dibayar', 422, '422 Unprocessable');
            }
 
            if (!$bill->fee) {
                return $this->sendError('Fee tagihan tidak ditemukan', 404, '404 Not Found');
            }
 
            if ($bill->gross_amount <= 0) {
                return $this->sendError('Nominal tagihan tidak valid', 422, '422 Unprocessable');
            }
 
            // Cek payment existing SEBELUM hit Midtrans
            $payment = Payment::where('bill_id', $bill->id)->first();
 
            if ($payment && $payment->snap_token) {
                return $this->sendSuccess('Snap token generated', [
                    'payment_id' => $payment->id,
                    'snap_token' => $payment->snap_token,
                    'bill'       => [
                        'id'           => $bill->id,
                        'invoice_id'   => $bill->invoice_number,
                        'gross_amount' => (int) $bill->gross_amount,
                        'user'         => [
                            'id'   => $bill->user->id,
                            'name' => $bill->user->name,
                        ],
                    ],
                ]);
            }
 
            // Baru hit Midtrans kalau belum ada token
            \Midtrans\Config::$serverKey    = config('midtrans.serverKey');
            \Midtrans\Config::$isProduction = config('midtrans.isProduction');
            \Midtrans\Config::$isSanitized  = config('midtrans.isSanitized');
            \Midtrans\Config::$is3ds        = config('midtrans.is3ds');
 
            $itemDetails = [
                [
                    'id'       => $bill->fee->id,
                    'price'    => (int) $bill->gross_amount,
                    'quantity' => 1,
                    'name'     => $bill->fee->name,
                ],
            ];
 
            $params = [
                'transaction_details' => [
                    'order_id'     => $bill->invoice_number,
                    'gross_amount' => (int) $bill->gross_amount,
                ],
                'item_details'     => $itemDetails,
                'customer_details' => [
                    'first_name' => $bill->user->name,
                    'email'      => $bill->user->email,
                ],
            ];
 
            $snapToken = \Midtrans\Snap::getSnapToken($params);
 
            $payment = Payment::create([
                'id'         => \Illuminate\Support\Str::uuid(),
                'bill_id'    => $bill->id,
                'amount'     => $bill->gross_amount,
                'status'     => 'pending',
                'snap_token' => $snapToken,
            ]);
 
            return $this->sendSuccess('Snap token generated', [
                'payment_id' => $payment->id,
                'snap_token' => $snapToken,
                'bill'       => [
                    'id'           => $bill->id,
                    'invoice_id'   => $bill->invoice_number,
                    'gross_amount' => (int) $bill->gross_amount,
                    'user'         => [
                        'id'   => $bill->user->id,
                        'name' => $bill->user->name,
                    ],
                ],
            ]);
 
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500, '500 Internal Server Error');
        }
    }

    public function getBillDetail(Request $request, $id)
    {
        try {
            $bill = Bill::with([
                'user:id,name',
                'fee:id,name,description',
            ])->find($id);

            if (!$bill) {
                return $this->sendError('Tagihan tidak ditemukan', 404, '404 Not Found');
            }

            $data = [
                'id'            => $bill->id,
                'invoiceNumber' => $bill->invoice_number,
                'grossAmount'   => $bill->gross_amount,
                'status'        => $bill->status,
                'dueDate'       => $bill->due_date,
                'user'          => $bill->user,
                'fee'           => $bill->fee,
                'price'         => $bill->gross_amount,
            ];

            return $this->sendSuccess('Berhasil mengambil data detail tagihan', $data);

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500, '500 Internal Server Error');
        }
    }

    public function getAllBill(Request $request)
    {
        try {
            $page      = $request->query('page', 1);
            $pageSize  = $request->query('page_size', 10);
            $search    = $request->query('search', '');
            $sortBy    = $request->query('sort_by', 'created_at');
            $sortOrder = $request->query('sort_order', 'desc');

            $user  = $request->user();
            $query = Bill::query()->with('user:id,name')
                ->whereIn('status', ['unpaid', 'pending'])
                ->orderBy('due_date', 'asc');

            if ($user->roles?->name === 'Warga') {
                $query->where('user_id', $user->id);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', fn($u) => $u->where('name', 'LIKE', "%{$search}%"));
                });
            }

            $allowedSortBy = ['invoice_number', 'created_at'];
            $sortBy        = in_array($sortBy, $allowedSortBy) ? $sortBy : 'created_at';
            $sortOrder     = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';
            $query->orderBy($sortBy, $sortOrder);

            $bills = $query->paginate($pageSize, ['*'], 'page', $page);

            $data = $this->PaginatedResponse($bills, $page);
            return $this->sendSuccess('Get All Bill Success', $data);

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500, '500 Internal Server Error');
        }
    }
}