<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SwitchTenantDatabase
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = config('tenants')[$request->getHost()] ?? null;

        if ($tenant) {
            Config::set('database.connections.mysql.database', $tenant['database']);
            Config::set('database.connections.mysql.username', $tenant['username']);
            Config::set('database.connections.mysql.password', $tenant['password']);

            DB::purge('mysql');
        }

        return $next($request);
    }
}
