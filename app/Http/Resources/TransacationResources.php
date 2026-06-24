<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'reference_code' => $this->reference_code,
            'type'           => $this->type,
            'amount'         => $this->amount,
            'balance_before' => $this->balance_before,
            'balance_after'  => $this->balance_after,
            'description'    => $this->description,
            'related_wallet' => $this->whenLoaded('relatedWallet', fn() => [
                'owner' => $this->relatedWallet->user->name,
            ]),
            'created_at'     => $this->created_at->format('d M Y, H:i'),
        ];
    }
}