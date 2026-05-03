<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $userData = [
            'id'       => $user->id,
        ];

        if (strtoupper($request->method()) === 'GET') {
            $request->query->add($userData);
        } else {
            $request->merge($userData);
        }

        return $next($request);
    }
}