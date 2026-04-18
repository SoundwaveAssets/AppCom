<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EditProfile extends Component
{
    public string $name = '';
    public string $email = '';
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';
    public bool $loading = false;
    public string $successMessage = '';

    public function mount(): void
    {
        $this->name  = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function updated(string $field): void
    {
        $this->validateOnly($field);
    }

    protected function rules(): array
    {
        return [
            'name'  => 'required|min:3|max:50',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore(Auth::id()),
            ],
        ];
    }

    protected $messages = [
        'name.required'  => 'Le nom est obligatoire.',
        'name.min'       => 'Le nom doit avoir au moins 3 caractères.',
        'email.required' => 'L\'email est obligatoire.',
        'email.email'    => 'L\'email n\'est pas valide.',
        'email.unique'   => 'Cet email est déjà utilisé.',
    ];

    public function updateProfile(): void
    {
        $this->loading = true;
        $validated = $this->validate();
        Auth::user()->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ]);
        $this->successMessage = 'Profil mis à jour avec succès.';
        $this->loading = false;
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:8|confirmed',
        ], [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'new_password.required'     => 'Le nouveau mot de passe est obligatoire.',
            'new_password.min'          => 'Le mot de passe doit avoir au moins 8 caractères.',
            'new_password.confirmed'    => 'Les mots de passe ne correspondent pas.',
        ]);

        if (!Hash::check($this->current_password, Auth::user()->password)) {
            $this->addError('current_password', 'Le mot de passe actuel est incorrect.');
            return;
        }

        Auth::user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->current_password          = '';
        $this->new_password              = '';
        $this->new_password_confirmation = '';
        $this->successMessage            = 'Mot de passe changé avec succès.';
    }

    public function render()
    {
        return view('livewire.profile.edit-profile')
            ->layout('layouts.app');
    }
}