<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) $request->session()->get('is_admin', false)) {
            if ($request->isMethod('GET')) {
                $request->session()->put('admin_intended_url', $request->fullUrl());
            }

            return redirect()->route('admin.login')->withErrors([
                'access' => 'Acces permis doar administratorului.',
            ]);
        }

        return $next($request);
    }
}
