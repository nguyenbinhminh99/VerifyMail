<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $admin = $request->user();
        if($admin !== null && $admin->hasRole('admin'))
        {
            return $next($request);
        }
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
