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
            $missingDependency = false;
            $parameters = ListViews::getParameters();
            foreach ($parameters as $parameter) {
                if (!request()->exists($parameter) && ListViews::hasDefault($parameter) && !ListViews::missingDependency($parameter, $request)) {
                    $missingParameters = array_merge($missingParameters, [$parameter => ListViews::getDefault($parameter)]);
                }
            }
            foreach (array_keys(request()->all()) as $parameter) {
                if (ListViews::missingDependency($parameter, $request)) {
                    $missingDependency = true;
                    $requestParameters = request()->all();
                    unset($requestParameters[$parameter]);
                    request()->replace($requestParameters);
                }
            }
            if (!empty($missingParameters) || $missingDependency) {
                return redirect(request()->fullUrlWithQuery($missingParameters));
            }
        }

        return $next($request);
    }
}