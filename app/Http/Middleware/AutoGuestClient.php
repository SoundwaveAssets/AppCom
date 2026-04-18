<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AutoGuestClient
{
    public function handle(Request $request, Closure $next)
    {
        // Si l'utilisateur n'est pas authentifié, créer un client anonyme
        if (!Auth::check()) {
            $guestUser = User::firstOrCreate([
                'email' => 'guest_' . session()->getId() . '@example.com',
            ], [
                'name' => 'Visiteur',
                'password' => bcrypt('guest_' . session()->getId()),
                'role' => 'user',
            ]);
            
            Auth::login($guestUser);
        }
        
        return $next($request);
    }
}
