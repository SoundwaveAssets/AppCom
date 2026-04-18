<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoginForm extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public bool $loading = false;

    protected function rules(): array
    {
        return [
            'email'    => 'required|email',
            'password' => 'required|min:8',
        ];
    }

    protected $messages = [
        'email.required'    => 'L\'email est obligatoire.',
        'email.email'       => 'L\'email n\'est pas valide.',
        'password.required' => 'Le mot de passe est obligatoire.',
        'password.min'      => 'Le mot de passe doit avoir au moins 8 caractères.',
    ];

    public function updated(string $field): void
    {
        $this->validateOnly($field);
    }

    public function login(): void
    {
        $this->loading = true;

        $this->validate();

        if (!Auth::attempt([
            'email'    => $this->email,
            'password' => $this->password,
        ], $this->remember)) {
            $this->loading = false;
            $this->addError('email', 'Email ou mot de passe incorrect.');
            return;
        }

        $user = Auth::user();

        $this->loading = false;

        // Redirection selon le rôle
      if (auth()->user()->role === 'admin') {
        $this->redirect(route('admin.dashboard'), navigate: true);
    } else {
        $this->redirect(route('dashboard'), navigate: true);
}
    }

    public function render()
    {
        return view('livewire.auth.login-form')
            ->layout('layouts.guest');
    }
}