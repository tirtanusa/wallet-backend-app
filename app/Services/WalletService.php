<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService
{
    public function getBalance(User $user): array
    {
        $wallet = $user->wallet;

        return [
            'balance'  => $wallet->balance,
            'currency' => $wallet->currency,
        ];
    }

    public function topUp(User $user, float $amount, ?string $description = null): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $description) {
            // Lock row agar tidak ada race condition
            $wallet = $user->wallet()->lockForUpdate()->firstOrFail();

            $balanceBefore = $wallet->balance;
            $balanceAfter  = $balanceBefore + $amount;

            $wallet->update(['balance' => $balanceAfter]);

            return Transaction::create([
                'wallet_id'      => $wallet->id,
                'type'           => 'topup',
                'amount'         => $amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
                'reference_code' => $this->generateReferenceCode('TOP'),
                'description'    => $description,
            ]);
        });
    }

    public function transfer(User $sender, string $recipientEmail, float $amount, ?string $description = null): Transaction
    {
        return DB::transaction(function () use ($sender, $recipientEmail, $amount, $description) {
            // Lock kedua wallet — order by ID untuk hindari deadlock
            $senderWallet = $sender->wallet()->lockForUpdate()->firstOrFail();

            $recipient       = User::where('email', $recipientEmail)->firstOrFail();
            $recipientWallet = $recipient->wallet()->lockForUpdate()->firstOrFail();

            // Cegah transfer ke diri sendiri
            if ($senderWallet->id === $recipientWallet->id) {
                throw new \InvalidArgumentException('Tidak dapat transfer ke diri sendiri.');
            }

            // Cek saldo cukup
            if ($senderWallet->balance < $amount) {
                throw new \InvalidArgumentException('Saldo tidak mencukupi.');
            }

            $senderBefore    = $senderWallet->balance;
            $recipientBefore = $recipientWallet->balance;
            $referenceCode   = $this->generateReferenceCode('TRF');

            // Kurangi saldo pengirim
            $senderWallet->update(['balance' => $senderBefore - $amount]);

            // Tambah saldo penerima
            $recipientWallet->update(['balance' => $recipientBefore + $amount]);

            // Catat transaksi sisi pengirim
            $transaction = Transaction::create([
                'wallet_id'         => $senderWallet->id,
                'related_wallet_id' => $recipientWallet->id,
                'type'              => 'transfer_out',
                'amount'            => $amount,
                'balance_before'    => $senderBefore,
                'balance_after'     => $senderBefore - $amount,
                'reference_code'    => $referenceCode,
                'description'       => $description,
            ]);

            // Catat transaksi sisi penerima
            Transaction::create([
                'wallet_id'         => $recipientWallet->id,
                'related_wallet_id' => $senderWallet->id,
                'type'              => 'transfer_in',
                'amount'            => $amount,
                'balance_before'    => $recipientBefore,
                'balance_after'     => $recipientBefore + $amount,
                'reference_code'    => $referenceCode,
                'description'       => $description,
            ]);

            return $transaction;
        });
    }

    public function getTransactions(User $user): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $user->wallet
            ->transactions()
            ->with('relatedWallet.user')
            ->latest()
            ->paginate(10);
    }

    public function getTransactionByReference(string $referenceCode): Transaction
    {
        return Transaction::with('relatedWallet.user')
            ->where('reference_code', $referenceCode)
            ->firstOrFail();
    }

    private function generateReferenceCode(string $prefix): string
    {
        return $prefix . '-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }
}