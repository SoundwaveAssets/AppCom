<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Lib\FirebaseAuthService;
use Closure;
use Illuminate\Http\Request;

class ResolveFirebaseUser
{
    public function __construct(private FirebaseAuthService $firebaseAuthService) {}

    public function handle(Request $request, Closure $next)
    {
        $token = $this->extractBearerToken($request);

        if (! $token) {
            return $next($request);
        }

        $decoded = $this->firebaseAuthService->verifyIdToken($token);

        if (! $decoded) {
            return response()->json(['message' => 'Firebase token invalide.'], 401);
        }

        $uid = (string) $decoded->claims()->get('sub');
        $email = (string) ($decoded->claims()->get('email') ?? '');
        $name = (string) ($decoded->claims()->get('name') ?? 'Utilisateur');

        /** @var User $user */
        $user = User::query()->firstOrCreate(
            ['firebase_uid' => $uid],
            ['email' => $email ?: "{$uid}@firebase.local", 'name' => $name, 'role' => User::ROLE_CUSTOMER]
        );

        if (! $user->email && $email) {
            $user->update(['email' => $email]);
        }

        $this->firebaseAuthService->assignBootstrapRoleIfNeeded($user, $uid);

        $request->setUserResolver(fn () => $user);
        $request->attributes->set('firebase_claims', $decoded->claims()->all());

        return $next($request);
    }

    private function extractBearerToken(Request $request): ?string
    {
        $header = $request->header('Authorization');

        if (! $header || ! str_starts_with($header, 'Bearer ')) {
            return null;
        }

        return trim(substr($header, 7));
    }
}
