<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class FirebaseAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si le token est présent dans le header Authorization
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['message' => 'Token d\'authentification manquant'], 401);
        }

        try {
            // Vérifier le token avec Sanctum en utilisant la méthode native
            $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            
            if (!$tokenModel) {
                return response()->json(['message' => 'Token invalide ou expiré'], 401);
            }

            // Récupérer l'utilisateur associé au token
            $user = $tokenModel->tokenable;
            
            if (!$user) {
                return response()->json(['message' => 'Token invalide ou expiré'], 401);
            }

            // Authentifier l'utilisateur
            auth()->login($user);
            
            return $next($request);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur d\'authentification: ' . $e->getMessage()], 401);
        }
    }
}
