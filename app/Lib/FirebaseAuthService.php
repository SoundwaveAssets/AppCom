<?php

namespace App\Lib;

use App\Models\User;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Factory;

class FirebaseAuthService
{
    private ?Auth $auth = null;

    public function verifyIdToken(string $token): mixed
    {
        try {
            return $this->auth()->verifyIdToken($token);
        } catch (\Throwable) {
            return null;
        }
    }

    public function assignBootstrapRoleIfNeeded(User $user, string $uid): void
    {
        if (User::query()->where('role', User::ROLE_ADMIN)->exists()) {
            return;
        }

        if ($user->role !== User::ROLE_ADMIN) {
            $user->update(['role' => User::ROLE_ADMIN]);
        }

        try {
            $this->auth()->setCustomUserClaims($uid, ['role' => 'super_admin']);
        } catch (\Throwable) {
        }
    }

    private function auth(): Auth
    {
        if ($this->auth) {
            return $this->auth;
        }

        $factory = new Factory();
        $credentialsPath = (string) config('services.firebase.credentials');
        if ($credentialsPath !== '') {
            $factory = $factory->withServiceAccount($credentialsPath);
        }

        $this->auth = $factory->createAuth();
        return $this->auth;
    }
}
