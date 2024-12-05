<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\DomainsModel;


class CheckDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Assuming the domain is passed as part of the request
        // $domain = $request->getHost(); // or $request->input('domain') if passed differently
        $domain = $request->input('domain'); // or $request->input('domain') if passed differently

        // Check if domain exists in the database
        if (DomainsModel::where('name', $domain)->exists()) {
            return $next($request); // Domain exists, proceed to the next middleware or controller
        }

        // If domain does not exist, return a 404 response
        return abort(404, 'Domain not found');
    }
}
