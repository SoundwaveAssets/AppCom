<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\OrderItemResource; // Add this line

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'order_number'   => $this->order_number,
            'status'         => $this->status,
            'status_label'   => $this->statusLabel(),

            // Montant (XAF = entier, pas de conversion en centimes)
            'total_amount'          => $this->total_amount,
            'total_amount_formatted' => number_format($this->total_amount, 0, ',', ' ') . ' XAF',

            'customer_email'  => $this->customer_email,
            'is_guest'        => is_null($this->user_id),
            'shipping_address' => $this->shipping_address,

            // Relations conditionnelles (chargées via ->load())
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'user'  => $this->whenLoaded('user', fn () => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ]),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function statusLabel(): string
    {
        return match ($this->status) {
            'pending_payment' => 'En attente de paiement',
            'paid'            => 'Payée',
            'processing'      => 'En cours de traitement',
            'shipped'         => 'Expédiée',
            'delivered'       => 'Livrée',
            'cancelled'       => 'Annulée',
            'payment_failed'  => 'Paiement échoué',
            default           => ucfirst($this->status),
        };
    }
}