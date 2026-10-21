<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Audit otomatis: route baru yang lupa diberi middleware akan langsung ketahuan.
 */
class RouteSecurityTest extends TestCase
{
    /** Route yang memang boleh diakses tanpa login. */
    private const PUBLIC_ROUTES = [
        'landing', 'packages.index', 'packages.show', 'portfolios.index', 'availability.index', 'availability.json',
        'login', 'register', 'password.request', 'password.email', 'password.reset', 'password.update',
    ];

    public function test_every_admin_route_requires_auth_and_admin(): void
    {
        $adminRoutes = collect(Route::getRoutes()->getRoutes())->filter(fn ($route) => Str::startsWith($route->uri(), 'admin'));

        $this->assertNotEmpty($adminRoutes);
        foreach ($adminRoutes as $route) {
            $middleware = $route->gatherMiddleware();
            $this->assertContains('auth', $middleware, "{$route->uri()} tanpa auth");
            $this->assertContains('admin', $middleware, "{$route->uri()} tanpa admin");
        }
    }

    public function test_only_whitelisted_routes_are_public(): void
    {
        foreach (Route::getRoutes()->getRoutes() as $route) {
            $name = $route->getName();
            $isApp = Str::startsWith($route->getActionName(), 'App\\');

            if (! $isApp || in_array($name, self::PUBLIC_ROUTES, true) || in_array($route->uri(), ['login', 'register'], true)) {
                continue; // route vendor (debug, storage) atau publik yang disengaja
            }

            $this->assertContains('auth', $route->gatherMiddleware(), "Route {$route->uri()} ({$name}) bisa diakses tanpa login");
        }
    }

    public function test_all_post_forms_have_csrf_token(): void
    {
        foreach (File::allFiles(resource_path('views')) as $file) {
            $content = $file->getContents();
            $this->assertGreaterThanOrEqual(
                substr_count($content, 'method="POST"'),
                substr_count($content, '@csrf'),
                $file->getRelativePathname().' punya form POST tanpa @csrf'
            );
        }
    }
}
