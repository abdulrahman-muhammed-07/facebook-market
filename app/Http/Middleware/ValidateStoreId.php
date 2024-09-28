<?php

namespace App\Http\Middleware;

use Closure;

class ValidateStoreId
{
    public function handle($request, Closure $next)
    {

        if (!$request->has('store_id') && isset($request->user()->store_id)) {

            if ($request->isMethod('POST')) {

                $request->request->add(['store_id' => $request->user()->store_id]);

                //

            } elseif ($request->isMethod('GET')) {

                $request->query->add(['store_id' => $request->user()->store_id]);

                //

            }
        }

        $request->validate([

            'store_id' => 'required|exists:users,store_id',

        ]);

        return $next($request);
    }
}
