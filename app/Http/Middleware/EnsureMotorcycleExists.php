<?php

namespace App\Http\Middleware;

use App\Models\Motorcycle;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMotorcycleExists
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Motorcycle::exists()) {
            return redirect()->route('motorcycle.create');
        }

        return $next($request);
    }
}
