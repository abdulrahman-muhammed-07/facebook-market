<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CodeMiddleware
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

        $data = $request->all();

        isset($data['state']) ? $data['state'] = md5($data['state']) : '';

        $request->replace($data);

        return $next($request);
    }
}
