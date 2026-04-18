<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Autorisé pour les utilisateurs connectés ET les invités
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Articles du panier
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|integer|exists:products,id',
            'items.*.quantity'     => 'required|integer|min:1|max:99',

            // Informations de livraison
            'customer_email'       => 'required|email|max:255',
            'shipping_address'     => 'required|array|min:1',
            'shipping_address.address'   => 'required|string|max:255',
            'shipping_address.city'      => 'required|string|max:100',
            'shipping_address.postal_code' => 'nullable|string|max:20',
            'shipping_address.country'    => 'required|string|max:100',
            'shipping_address.phone'      => 'required|string|max:20',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Le panier ne peut pas être vide.',
            'items.min' => 'Le panier doit contenir au moins un article.',
            'items.*.product_id.exists' => 'Un produit du panier est invalide.',
            'items.*.quantity.min' => 'La quantité doit être d\'au moins 1.',
            'items.*.quantity.max' => 'La quantité maximale par article est de 99.',
            'customer_email.required' => 'L\'email est requis.',
            'shipping_address.required' => 'L\'adresse de livraison est requise.',
        ];
    }
}
