<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Legacy\EinheitenController;
use App\Http\Controllers\Legacy\HaeuserController;
use App\Http\Controllers\Legacy\ObjekteController;
use App\Http\Controllers\Legacy\PersonenController;
use App\Http\Controllers\Legacy\ToDoController;
use Closure;
use Route;

class DefaultParameters
{
    protected $defaults = [
        ToDoController::class . '@index' => [
            'v' => 'auftrag auftrag[erstellt:desc] auftrag[text] von an kostenträger',
            's' => 20
        ],
        ObjekteController::class . '@index' => [
            'v' => 'objekt haus[count] einheit[count] detail[count]',
            's' => 20
        ],
        HaeuserController::class . '@index' => [
            'v' => 'haus !haus[str:asc nr:asc] haus[plz] haus[ort] detail[count] einheit[count] objekt',
            's' => 20
        ],
        EinheitenController::class . '@index' => [
            'v' => 'einheit !einheit[name] mietvertrag person[mietvertrag] einheit[typ] einheit[qm] einheit[lage] haus objekt',
            's' => 20
        ],
        PersonenController::class . '@index' => [
            'v' => 'person(mietvertrag) mietvertrag einheit[mietvertrag] haus[mietvertrag] objekt[mietvertrag] detail[count]',
            's' => 20
        ]
    ];

    public function handle($request, Closure $next)
    {
        $action = Route::currentRouteAction();
        if(key_exists($action, $this->defaults)) {
            $missingParameters = [];
            $parameters = $this->defaults[$action];
            foreach ($parameters as $key => $value) {
                if (!request()->exists($key)) {
                    $missingParameters = array_merge($missingParameters, [$key => $value]);
                }
            }
            if (!empty($missingParameters)) {
                return redirect(request()->fullUrlWithQuery($missingParameters));
            }
        }

        return $next($request);
    }
}