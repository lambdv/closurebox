<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class _HandleOrganizationTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $orgSlug = (string) $request->route('org');

        if (!$user || $orgSlug === '') {
            abort(403);
        }

        $currentOrganization = $user->organizations->first(function ($organization) use ($orgSlug) {
            return Str::slug($organization->name) === $orgSlug;
        });

        if (!$currentOrganization) {
            abort(404);
        }

        // Attach for downstream consumers (controllers, Livewire)
        $request->attributes->set('currentOrganization', $currentOrganization);

        return $next($request);
    }
}


