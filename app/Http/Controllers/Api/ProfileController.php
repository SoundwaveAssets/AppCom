<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    // Voir profil
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ], 200);
    }

    // Modifier profil
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'  => 'required|min:3|max:50',
            'email' => 'required|email|unique:users,email,' . $request->user()->id,
        ], [
            'name.required'  => 'Le nom est obligatoire.',
            'name.min'       => 'Le nom doit avoir au moins 3 caractères.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email'    => 'L\'email n\'est pas valide.',
            'email.unique'   => 'Cet email est déjà utilisé.',
        ]);

        $request->user()->update($validated);

        return response()->json([
            'message' => 'Profil mis à jour.',
            'user'    => $request->user(),
        ], 200);
    }

    // Changer mot de passe
    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect.',
            ], 400);
        }

        $request->user()->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'Mot de passe changé avec succès.',
        ], 200);
    }
}