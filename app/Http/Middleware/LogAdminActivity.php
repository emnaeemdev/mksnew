<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAdminActivity
{
    public function __construct(protected ActivityLogger $logger)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->shouldLog($request)) {
            return $next($request);
        }

        $snapshot = $this->logger->snapshotSubject($request);
        $response = $next($request);

        if ($response->getStatusCode() < 400 && $request->user()) {
            $this->logger->recordRequest($request, $response->getStatusCode(), $snapshot);
        }

        return $response;
    }

    protected function shouldLog(Request $request): bool
    {
        if (!$request->user()) {
            return false;
        }

        $method = strtoupper($request->method());
        $routeName = (string) optional($request->route())->getName();

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return true;
        }

        return $method === 'GET' && (
            str_contains($routeName, 'export')
            || str_contains($routeName, 'download')
        );
    }
}
