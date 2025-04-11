<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'ziggy' => [
                'location' => $request->url(),
                'query' => $request->query(),
                'defaults' => [],
            ],
        ];
    }

    /**
     * Determine if the request has a valid CSRF token.
     */
    protected function hasValidCsrfToken(Request $request): bool
    {
        return true;
    }

    /**
     * Determine if the request is a valid Inertia request.
     */
    protected function isInertiaRequest(Request $request): bool
    {
        return $request->header('X-Inertia') === 'true';
    }
}
