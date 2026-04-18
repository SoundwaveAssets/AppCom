<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class RegisterForm extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $loading = false;

    // Validation en temps réel
    protected function rules(): array
    {
        return [
            'name'     => 'required|min:3|max:50',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|regex:/^[0-9]{9}$/',
            'password' => 'required|min:8|confirmed',
        ];
    }

    protected $messages = [
        'name.required'     => 'Le nom est obligatoire.',
        'name.min'          => 'Le nom doit avoir au moins 3 caractères.',
        'email.required'    => 'L\'email est obligatoire.',
        'email.email'       => 'L\'email n\'est pas valide.',
        'email.unique'      => 'Cet email est déjà utilisé.',
        'phone.regex'       => 'Le numéro doit contenir 9 chiffres.',
        'password.required' => 'Le mot de passe est obligatoire.',
        'password.min'      => 'Le mot de passe doit avoir au moins 8 caractères.',
        'password.confirmed'=> 'Les mots de passe ne correspondent pas.',
    ];

    // Validation en temps réel champ par champ
    public function updated(string $field): void
    {
        $this->validateOnly($field);
    }

    public function register(): void
    {
        $this->loading = true;

        $validated = $this->validate();
    
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'user',
        ]);

        event(new Registered($user));

        Auth::login($user);

        $this->loading = false;

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register-form')
            ->layout('layouts.guest');
    }
}