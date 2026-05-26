<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Bill;
use App\Models\Fee;
use App\Helpers\MidtransHelper;
use App\Traits\ResponseAPI;

class PaymentController extends Controller
{
    use ResponseAPI;
    public function getAllPaymentHistory(Request $request)
    {
        try {
            $page      = $request->query('page', 1);
            $pageSize  = $request->query('page_size', 10);
            $search    = $request->query('search', '');
            $sortBy    = $request->query('sort_by', 'created_at');
            $sortOrder = $request->query('sort_order', 'desc');

            $user  = $request->user();
            $query = Payment::with(['bill.user'])
                            ->where('status', 'success'); // ✅ hanya status success

            // ✅ Kalau role Warga, hanya tampilkan payment miliknya sendiri
            if ($user->roles?->name === 'Warga') {
                $query->whereHas('bill', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('bill', function ($b) use ($search) {
                        $b->where('invoice_number', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', fn($u) => $u->where('name', 'LIKE', "%{$search}%"));
                    });
                });
            }

            $allowedSortBy = ['created_at', 'amount'];
            $sortBy    = in_array($sortBy, $allowedSortBy) ? $sortBy : 'created_at';
            $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';
            $query->orderBy($sortBy, $sortOrder);

            $payments = $query->paginate($pageSize, ['*'], 'page', $page);

            if ($payments->isEmpty()) {
                return $this->sendError('No payments found', 404, '404 Not Found');
            }

            $data = $this->PaginatedResponse($payments, $page);
            return $this->sendSuccess('Get All Payment Success', $data);

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500, '500 Internal Server Error');
        }
    }

    public function callback(Request $request)
    {
        try {
            \Midtrans\Config::$serverKey    = config('midtrans.serverKey');
            \Midtrans\Config::$isProduction = config('midtrans.isProduction');
            \Midtrans\Config::$isSanitized  = config('midtrans.isSanitized');
            \Midtrans\Config::$is3ds        = config('midtrans.is3ds');

            $notif = new \Midtrans\Notification();

            $orderId           = $notif->order_id;
            $transactionStatus = $notif->transaction_status;
            $statusCode        = $notif->status_code;
            $grossAmount       = $notif->gross_amount;
            $signatureKey      = $notif->signature_key;
            $fraudStatus       = $notif->fraud_status;

            // ✅ Verifikasi signature keamanan
            $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . config('midtrans.serverKey'));
            if ($expectedSignature !== $signatureKey) {
                return response()->json(['message' => 'Invalid signature'], 401);
            }

            // ✅ Cari bill berdasarkan invoice_number
            $bill = Bill::where('invoice_number', $orderId)->first();
            if (!$bill) {
                return response()->json(['message' => 'Bill not found'], 404);
            }

            // ✅ Cari payment berdasarkan bill_id
            $payment = Payment::where('bill_id', $bill->id)->latest()->first();
            if (!$payment) {
                return response()->json(['message' => 'Payment not found'], 404);
            }

            // ✅ Update status berdasarkan transaction_status dari Midtrans
            if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
                // Pembayaran berhasil
                $bill->status    = 'paid';
                $payment->status = 'success';
                $payment->payment_date = now();
            } elseif ($transactionStatus === 'pending') {
                $payment->status = 'pending';
            } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                $payment->status = 'failed';
                // bill tetap unpaid supaya user bisa bayar lagi
            }

            $bill->save();
            $payment->save();

            return response()->json(['message' => 'Callback handled successfully'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
