<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\TopUpRequest;
use App\Http\Requests\Wallet\TransferRequest;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;

class WalletController extends Controller
{
    public function __construct(
        private readonly WalletService $walletService
    ) {}

    public function balance(): JsonResponse
    {
        $data = $this->walletService->getBalance(auth()->user());

        return response()->json([
            'message' => 'Berhasil mengambil saldo.',
            'data'    => new WalletResource(auth()->user()->wallet),
        ]);
    }

    public function topUp(TopUpRequest $request): JsonResponse
    {
        try {
            $transaction = $this->walletService->topUp(
                user:        auth()->user(),
                amount:      $request->amount,
                description: $request->description,
            );

            return response()->json([
                'message' => 'Top up berhasil.',
                'data'    => new TransactionResource($transaction),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function transfer(TransferRequest $request): JsonResponse
    {
        try {
            $transaction = $this->walletService->transfer(
                sender:         auth()->user(),
                recipientEmail: $request->recipient_email,
                amount:         $request->amount,
                description:    $request->description,
            );

            return response()->json([
                'message' => 'Transfer berhasil.',
                'data'    => new TransactionResource($transaction),
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan, silakan coba lagi.',
            ], 500);
        }
    }
}