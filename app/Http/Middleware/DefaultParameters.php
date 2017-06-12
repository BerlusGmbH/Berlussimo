<?php

namespace App\Http\Middleware;

use Closure;
use ListViews;

class DefaultParameters
{

    public function handle($request, Closure $next)
    {
        if (ListViews::hasParameters()) {
            $missingParameters = [];
            $parameters = ListViews::getParameters();
            foreach ($parameters as $parameter) {
                if (!request()->exists($parameter) && ListViews::hasDefault($parameter)) {
                    $missingParameters = array_merge($missingParameters, [$parameter => ListViews::getDefault($parameter)]);
                }
            }
            if (!empty($missingParameters)) {
                return redirect(request()->fullUrlWithQuery($missingParameters));
            }
        }

        return $next($request);
    }
}