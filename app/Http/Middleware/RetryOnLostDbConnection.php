<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Database\LostConnectionDetector;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * إعادة محاولة طلب GET/HEAD مرة واحدة عند انقطاع اتصال MySQL (gone away)،
 * بدون أي تغيير في منطق البحث أو الاستعلامات.
 */
class RetryOnLostDbConnection
{
    private const REQUEST_FLAG = 'db_lost_connection_retried';

    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (QueryException $e) {
            if (!$this->shouldRetry($request, $e)) {
                throw $e;
            }

            Log::warning('Database connection lost; reconnecting and retrying safe request once.', [
                'method' => $request->method(),
                'path' => $request->path(),
                'message' => $e->getMessage(),
            ]);

            $this->reconnectAllMysqlConnections();

            return $next($request);
        }
    }

    protected function shouldRetry(Request $request, QueryException $e): bool
    {
        if (!$request->isMethodSafe()) {
            return false;
        }

        if ($request->attributes->get(self::REQUEST_FLAG)) {
            return false;
        }

        if (!$this->isLostConnection($e)) {
            return false;
        }

        $request->attributes->set(self::REQUEST_FLAG, true);

        return true;
    }

    protected function isLostConnection(QueryException $e): bool
    {
        $detector = app(LostConnectionDetector::class);

        if ($detector->causedByLostConnection($e)) {
            return true;
        }

        $previous = $e->getPrevious();

        return $previous instanceof Throwable
            && $detector->causedByLostConnection($previous);
    }

    protected function reconnectAllMysqlConnections(): void
    {
        foreach (array_keys(config('database.connections', [])) as $name) {
            $driver = config("database.connections.{$name}.driver");
            if (!in_array($driver, ['mysql', 'mariadb'], true)) {
                continue;
            }

            try {
                DB::purge($name);
                DB::reconnect($name);
            } catch (Throwable $e) {
                // best-effort reconnect
            }
        }
    }
}
