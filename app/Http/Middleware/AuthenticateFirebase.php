<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateFirebase
{
    public function __construct(private ResolveFirebaseUser $resolver) {}

    public function handle(Request $request, Closure $next)
    {
        if (! $request->bearerToken()) {
            return response()->json(['message' => 'Authentification Firebase requise.'], 401);
        }

        $result = $this->resolver->handle($request, fn () => true);

        if ($result instanceof \Illuminate\Http\JsonResponse) {
            return $result;
        }

        if (! $request->user()) {
            return response()->json(['message' => 'Authentification Firebase requise.'], 401);
        }

        return $next($request);
    }
}
