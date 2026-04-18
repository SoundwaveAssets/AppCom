<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Inscription
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|min:3|max:50',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ], [
            'name.required'      => 'Le nom est obligatoire.',
            'name.min'           => 'Le nom doit avoir au moins 3 caractères.',
            'email.required'     => 'L\'email est obligatoire.',
            'email.email'        => 'L\'email n\'est pas valide.',
            'email.unique'       => 'Cet email est déjà utilisé.',
            'password.required'  => 'Le mot de passe est obligatoire.',
            'password.min'       => 'Le mot de passe doit avoir au moins 8 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'user',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inscription réussie.',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    // Connexion
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'L\'email est obligatoire.',
            'email.email'       => 'L\'email n\'est pas valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect.',
            ], 401);
        }

        $user  = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie.',
            'user'    => $user,
            'token'   => $token,
            'role'    => $user->role,
        ], 200);
    }

    // Déconnexion
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.',
        ], 200);
    }

    // Profil
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ], 200);
    }

    // Modifier profil
    public function updateProfile(Request $request): JsonResponse
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
        ], [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'new_password.required'     => 'Le nouveau mot de passe est obligatoire.',
            'new_password.min'          => 'Le mot de passe doit avoir au moins 8 caractères.',
            'new_password.confirmed'    => 'Les mots de passe ne correspondent pas.',
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