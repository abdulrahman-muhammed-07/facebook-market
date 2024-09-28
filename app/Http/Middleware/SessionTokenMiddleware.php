<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;

class SessionTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {


        try {

            $decoded = JWT::decode($request->session_token, env('APP_SECRET'), array('HS256'));
        } catch (\Exception $th) {
            return response()->json([
                'error' => $th->getMessage()
            ]);
        }

        $payload = json_decode(json_encode($decoded), true);
        $payload['password'] = md5($payload['store_id'] . $payload['extra']['admin_email']);
        $request->replace($payload);


        return $next($request);
    }
}
