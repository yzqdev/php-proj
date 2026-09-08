<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 给所有响应追加 X-Mock-Data: true 响应头（全局中间件，bootstrap/app.php 中 append）
 */
class AddMockDataHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('X-Mock-Data', 'true');

        return $response;
    }
}
