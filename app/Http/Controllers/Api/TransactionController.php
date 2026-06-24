<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\TransactionResources;

class TransactionController extends Controller
{
    public function __construct(
        private readonly WalletService $walletService
    ) {}

    public function index(): JsonResponse
    {
        $transactions = $this->walletService->getTransactions(auth()->user());

        return response()->json([
            'message' => 'Berhasil mengambil riwayat transaksi.',
            'data'    => TransactionResources::collection($transactions),
        ]);
    }

    public function show(string $referenceCode): JsonResponse
    {
        $transaction = $this->walletService->getTransactionByReference(
            referenceCode: $referenceCode,
        );

        $this->authorize('view', $transaction);

        return response()->json([
            'message' => 'Berhasil mengambil detail transaksi.',
            'data'    => new TransactionResource($transaction),
        ]);
    }
}