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

    public function getParameters($action = null)
    {
        $action = $this->resolveAction($action);
        return $this->views[$action];
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
        foreach ($this->getParameterNames() as $parameter) {
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

    public function getParameterNames($additionalParameters = null, $action = null)
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
            if ($r1 !== '') {
                for ($i = 0; $i < $c; $i++) {
                    if ($combinedRelations[$i]->first() !== '') {
                        if (substr($r1, 0, strlen($combinedRelations[$i]->first())) === $combinedRelations[$i]->first()) {
                            $combinedRelations[$i]->prepend($r1);
                            continue 2;
                        } elseif (substr($combinedRelations[$i]->first(), 0, strlen($r1)) === $r1) {
                            $combinedRelations[$i]->push($r1);
                            continue 2;
                        }
                    }
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

    public function response($columns, $index, $wantedRelations, $paginator, $class)
    {
        $headers = [];
        foreach ($columns as $fields) {
            $column = key($fields);
            $header = e(ucfirst($column));
            if (isset($fields) && isset($fields[$column]) && !$fields[$column]['fields']->isEmpty()) {
                $header .= '<br>(';
                $last = $fields[$column]['fields']->last()['field'];
                foreach ($fields[$column]['fields'] as $field) {
                    $header .= e(ucfirst($field['field']));
                    if ($last != $field['field']) {
                        $header .= e(', ');
                    } elseif ($last == $field['field']) {
                        $header .= e(')');
                    }
                }
            }
            if (isset($fields[$column]['columns']) && (!$fields[$column]['columns']->isEmpty() || !$fields[$column]['aggregates']->isEmpty())) {
                $header .= '<br>[';
                if (!$fields[$column]['aggregates']->isEmpty()) {
                    $last = $fields[$column]['aggregates']->last();
                    foreach ($fields[$column]['aggregates'] as $col) {
                        $header .= e(ucfirst($col));
                        if ($last != $col || !$fields[$column]['columns']->isEmpty()) {
                            $header .= e(', ');
                        }
                    }
                }
                if (!$fields[$column]['columns']->isEmpty()) {
                    $last = $fields[$column]['columns']->last();
                    foreach ($fields[$column]['columns'] as $col) {
                        $header .= e(ucfirst($col));
                        if ($last != $col) {
                            $header .= e(', ');
                        }
                    }
                }
                $header .= e(']');
            }
            $headers[] = $header;
        }

        $items = [];
        foreach ($index as $entity) {
            $row = [];
            foreach ($columns as $key => $fields) {
                $column = key($fields);
                $rs = Relations::columnColumnToRelations(Relations::classToColumn($class), $column);
                $cell = [];
                foreach ($rs as $r) {
                    if ($wantedRelations[$key]->contains($r)) {
                        //$c = 0;
                        //if (isset($fields[$column]['columns'])) {
                        //    $c = $fields[$column]['columns']->count();
                        //}
                        //if ($c > 1 || ($c == 0 && count($rs) > 1 && isset($entity[$r])))
                        //    echo ucfirst(Relations::classToColumn(Relations::classRelationToMany($class, $r)[1])) . '<br>';
                        if (isset($fields[$column]['aggregates']) && !$fields[$column]['aggregates']->isEmpty()) {
                            foreach ($fields[$column]['aggregates'] as $aggregate) {
                                $cell[] = ['type' => 'aggregate', 'entities' => $entity[$r]];
                                //@include('shared.entities.aggregates.count', ['entities' => $entity[$r], 'aggregate' => $aggregate] )<br >
                            }
                        } else {
                            if (isset($entity[$r])) {
                                foreach ($entity[$r] as $value) {
                                    if (isset($fields[$column]['fields']) && !$fields[$column]['fields']->isEmpty()) {
                                        $lines = [];
                                        foreach ($fields[$column]['fields'] as $field) {
                                            $dbField = Relations::classFieldToField(get_class($value), $field['field']);
                                            $lines[] = e($value->{$dbField});
                                        }
                                        $cell[] = ['type' => 'prerendered', 'lines' => $lines];
                                    } else {
                                        $cell[] = ['type' => 'entity', 'entity' => $value, 'class' => get_class($value)];
                                    }
                                }
                            }
                        }
                    }
                }
                $row[] = $cell;
            }
            $items[] = $row;
        }
        return ['headers' => $headers, 'items' => $items, 'total' => $paginator->total()];
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