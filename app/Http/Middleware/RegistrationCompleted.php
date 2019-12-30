<?php

namespace App\Http\Middleware;

use Closure;

class RegistrationCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if(!$request->user()->registration_completed)
            abort('403', 'Please complete the registration');

        return $next($request);
    }
}
