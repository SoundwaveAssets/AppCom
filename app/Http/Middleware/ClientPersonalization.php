<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientPersonalization
{
    public function handle(Request $request, Closure $next)
    {
        // Si l'utilisateur est authentifié et est un client (user)
        if (Auth::check() && Auth::user()->role === 'user') {
            $user = Auth::user();
            $fileName = "C_" . $user->id;
            $filePath = base_path("Vrais_client/{$fileName}");
            
            // Lire le fichier client s'il existe
            if (file_exists($filePath)) {
                $clientData = file_get_contents($filePath);
                $lines = explode("\n", trim($clientData));
                
                $personalizedData = [];
                foreach ($lines as $line) {
                    if (strpos($line, ':') !== false) {
                        list($key, $value) = explode(':', $line, 2);
                        $personalizedData[trim($key)] = trim($value);
                    }
                }
                
                // Partager les données personnalisées avec toutes les vues
                view()->share('clientPersonalization', $personalizedData);
            }
        }
        
        return $next($request);
    }
}
