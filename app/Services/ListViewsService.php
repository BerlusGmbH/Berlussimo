<?php

namespace App\Services;

use App\Services\Parser\Lexer;
use App\Services\Parser\Parser;
use Closure;
use Illuminate\Support\Collection;
use Relations;
use Route;

class ListViewsService
{
    protected $views = [];

    protected $parameters = [];

    public function __construct($config)
    {
        if (isset($config['listviews']) && is_array($config['listviews']))
            foreach ($config['listviews'] as $listview) {
                $dependsOn = isset($listview['dependsOn']) ? $listview['dependsOn'] : null;
                $this->registerViews($listview['parameter'], $listview['views'], $listview['default'], $dependsOn, $listview['action']);
            }
    }


    public function registerViews($parameter, $views, $default = null, $dependsOn = null, $action = null)
    {
        $action = $this->resolveAction($action);
        $this->views[$action][$parameter]['default'] = $default;
        $this->views[$action][$parameter]['views'] = $views;
        $this->views[$action][$parameter]['dependsOn'] = $dependsOn;
        $this->parameters[$action][] = $parameter;
    }

    protected function resolveAction($action)
    {
        if (is_null($action)) {
            return Route::currentRouteAction();
        }
        return $action;
    }

    public function getViews($parameter, $action = null)
    {
        $action = $this->resolveAction($action);

        if (!isset($this->views[$action][$parameter]['views']))
            return [];

        $views = $this->resolveViews($action, $parameter);

        return $views;
    }

    protected function resolveViews($action, $parameter)
    {
        if ($this->views[$action][$parameter]['views'] instanceof Closure)
            $this->views[$action][$parameter]['views'] = $this->views[$action][$parameter]['views']->call($this);
        return $this->views[$action][$parameter]['views'];
    }

    public function getViewNames($parameter, $action = null)
    {
        $action = $this->resolveAction($action);

        $views = $this->resolveViews($action, $parameter);

        return array_keys($views);
    }

    public function hasDefault($parameter, $action = null)
    {
        $action = $this->resolveAction($action);

        return isset($this->views[$action][$parameter]['default']);
    }

    public function hasParameters($action = null)
    {
        $action = $this->resolveAction($action);
        return isset($this->parameters[$action]);
    }

    public function calculateResponseData($request, $builder)
    {
        list($query, $size) = $this->parseParameters($request);

        $columns = $this->parseQuery($query, $builder);

        $entities = $builder->paginate($size);

        list($index, $wantedRelations) = $this->generateIndex($entities, $columns);

        return [$columns, $entities, $index, $wantedRelations];
    }

    protected function parseParameters($request)
    {
        $size = $request->input('s', 20);
        $query = "";
        if ($request->has('q')) {
            $query = $request->input('q');
        }
        foreach ($this->getParameters() as $parameter) {
            if (request()->has($parameter) && $parameter != 's') {
                if (is_array($request->input($parameter))) {
                    foreach ($request->input($parameter) as $subparameter) {
                        $query .= " " . $this->getView($parameter, $subparameter);
                    }
                } else {
                    $query .= " " . $this->getView($parameter, $request->input($parameter));
                }
            }
        }
        return [$query, $size];
    }

    public function getParameters($additionalParameters = null, $action = null)
    {
        $action = $this->resolveAction($action);
        if (is_array($additionalParameters)) {
            return array_merge($this->parameters[$action], $additionalParameters);
        }
        if (is_string($additionalParameters)) {
            return array_merge($this->parameters[$action], [$additionalParameters]);
        }
        return $this->parameters[$action];
    }

    public function getView($parameter, $name, $action = null)
    {
        $action = $this->resolveAction($action);

        if (!isset($this->views[$action][$parameter]['views']))
            return [];

        $views = $this->resolveViews($action, $parameter);

        if (!isset($views[$name]))
            return $this->getDefaultView($parameter, $action);

        $view = $this->resolveView($action, $parameter, $name);

        return $view;
    }

    public function getDefaultView($parameter, $action = null)
    {
        $action = $this->resolveAction($action);

        return $this->resolveView($action, $parameter, $this->getDefault($parameter, $action));
    }

    protected function resolveView($action, $parameter, $name)
    {
        if ($this->views[$action][$parameter]['views'][$name] instanceof Closure)
            $this->views[$action][$parameter]['views'][$name] = $this->views[$action][$parameter]['views'][$name]->call($this);
        return $this->views[$action][$parameter]['views'][$name];
    }

    public function getDefault($parameter, $action = null)
    {
        $action = $this->resolveAction($action);

        return $this->views[$action][$parameter]['default'];
    }

    protected function parseQuery($query, $builder)
    {
        $trace = null;
        if (config('app.debug')) {
            $trace = fopen(storage_path('logs/parser.log'), 'w');
        }
        $lexer = new Lexer($query, $trace);
        $parser = new Parser($lexer, $builder);
        $parser->Trace($trace, "\n");
        while ($lexer->yylex()) {
            $parser->doParse($lexer->token, $lexer->value);
        }
        $parser->doParse(0, 0);
        return $parser->retvalue;
    }

    protected function generateIndex($entities, $columns)
    {
        if ($entities->isEmpty()) {
            return [];
        }
        $class = get_class($entities->first()->getModel());
        $wantedRelations = [];
        $wantedRelations['total'] = collect();
        $result = [];
        foreach ($columns as $key => $filters) {
            $column = key($filters);
            $relations = Relations::columnColumnToRelations(Relations::classToColumn($class), $column);
            if (isset($filters[$column]['columns']) && !$filters[$column]['columns']->isEmpty()) {
                foreach ($relations as $relation) {
                    $notWanted = false;
                    if (isset($filters[$column]['columns']) && !$filters[$column]['columns']->isEmpty()) {
                        $notWanted = true;
                        foreach ($filters[$column]['columns'] as $c) {
                            $ccs = Relations::columnColumnToRelations(Relations::classToColumn($class), $c);
                            foreach ($ccs as $cc) {
                                if (strpos($relation, $cc) !== false) {
                                    $notWanted = false;
                                }
                            }
                        }
                    }
                    if (!$notWanted) {
                        $wantedRelations[$key] = isset($wantedRelations[$key]) ? $wantedRelations[$key]->push($relation) : collect($relation);
                        $wantedRelations['total']->push($relation);
                    }
                }
            } else {
                $wantedRelations[$key] = collect();
                foreach ($relations as $relation) {
                    $wantedRelations[$key]->push($relation);
                    $wantedRelations['total']->push($relation);
                }
            }
        }
        $relations = $wantedRelations['total'];
        unset($wantedRelations['total']);

        $combinedRelations = [collect($relations->shift())];

        while (!$relations->isEmpty()) {
            $r1 = $relations->shift();
            $c = count($combinedRelations);
            for ($i = 0; $i < $c; $i++) {
                if (strpos($r1, $combinedRelations[$i]->first()) === 0) {
                    $combinedRelations[$i]->prepend($r1);
                    continue 2;
                } elseif (strpos($combinedRelations[$i]->first(), $r1) === 0) {
                    $combinedRelations[$i]->push($r1);
                    continue 2;
                }
            }
            $combinedRelations[] = collect($r1);
        }

        $id = Relations::classFieldToField($class, 'id');
        foreach ($entities as $entity) {
            $result[$entity->{$id}] = null;
            foreach ($combinedRelations as $cr) {
                $stack = collect([['entity' => $entity, 'pos' => 0]]);
                $r1 = $cr->first();
                $r1parts = explode('.', $r1);
                $r1max = count($r1parts);
                while (!$stack->isEmpty()) {
                    $entry = $stack->pop();
                    $currentR = implode('.', array_slice($r1parts, 0, $entry['pos'] + 1));
                    if ($entry['pos'] < $r1max) {
                        if ($cr->contains($currentR)) {
                            if ($currentR !== '') {
                                if (isset($result[$entity->{$id}][$currentR]) && $result[$entity->{$id}][$currentR] instanceof Collection) {
                                    if ($entry['entity']->{$r1parts[$entry['pos']]} instanceof Collection) {
                                        $result[$entity->{$id}][$currentR] = $result[$entity->{$id}][$currentR]->merge($entry['entity']->{$r1parts[$entry['pos']]}->all());
                                    } else {
                                        $result[$entity->{$id}][$currentR]->push($entry['entity']->{$r1parts[$entry['pos']]});
                                    }
                                } elseif (!isset($result[$entity->{$id}][$currentR]) && $entry['entity']->{$r1parts[$entry['pos']]} instanceof Collection) {
                                    $result[$entity->{$id}][$currentR] = $entry['entity']->{$r1parts[$entry['pos']]};
                                } else {
                                    $result[$entity->{$id}][$currentR] = collect([$entry['entity']->{$r1parts[$entry['pos']]}]);
                                }
                            } else {
                                $result[$entity->{$id}][$currentR] = collect([$entry['entity']]);
                            }
                        }
                        if ($r1parts[$entry['pos']] !== '') {
                            $es = $entry['entity']->{$r1parts[$entry['pos']]};
                        } else {
                            $es = $entry['entity'];
                        }
                        if (!$es instanceof Collection) {
                            $es = collect([$es]);
                        } else {
                            $es = $es->reverse();
                        }
                        foreach ($es as $e) {
                            $stack->push(['entity' => $e, 'pos' => $entry['pos'] + 1]);
                        }
                    }
                }
            }
        }
        return [$result, $wantedRelations];
    }

    public function missingDependency($parameter, $request, $action = null)
    {

        $action = $this->resolveAction($action);
        $dependsOn = null;

        if (isset($this->views[$action][$parameter]['dependsOn']) && !is_null($this->views[$action][$parameter]['dependsOn'])) {
            $dependsOn = $this->views[$action][$parameter]['dependsOn'];
        } else {
            return false;
        }

        foreach ($dependsOn as $parameter => $value) {
            if (!$request->has($parameter) || !in_array($value, $request->input($parameter))) {
                return true;
            }
        }

        return false;
    }
}