<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lineTotal = $this->unit_price * $this->quantity;

        return [
            'product_id'            => $this->product_id,
            'product_name'          => $this->product_name,
            'unit_price'            => $this->unit_price,
            'unit_price_formatted'  => number_format($this->unit_price, 0, ',', ' ') . ' XAF',
            'quantity'              => $this->quantity,
            'line_total'            => $lineTotal,
            'line_total_formatted'  => number_format($lineTotal, 0, ',', ' ') . ' XAF',
        ];
    }
}