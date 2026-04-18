<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,user',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Créer un fichier pour les vrais clients (non anonymes)
        if ($request->role === 'user') {
            $fileName = "C_" . $user->id;
            $filePath = base_path("Vrais_client/{$fileName}");
            
            $content = "Nom: " . $user->name . "\n";
            $content .= "Email: " . $user->email . "\n";
            $content .= "ID: " . $user->id . "\n";
            $content .= "Date d'inscription: " . $user->created_at->format('d/m/Y H:i:s') . "\n";
            
            file_put_contents($filePath, $content);
        }

        // Rediriger les admins vers le tableau de bord
        if ($request->role === 'admin') {
            return redirect()->route('admin.dashboard')->with('success', 'Compte administrateur créé avec succès!');
        }

        return redirect()->route('welcome')->with('success', 'Compte client créé avec succès!');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Rediriger les admins vers le tableau de bord
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard')->with('success', 'Connexion réussie!');
            }
            
            return redirect()->route('welcome')->with('success', 'Connexion réussie!');
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('welcome')->with('success', 'Déconnexion réussie!');
    }
}
