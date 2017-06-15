<?php

namespace App\Services;

use Closure;
use Route;

class ListViewsService
{
    protected $views = [];

    protected $parameters = [];

    public function __construct($config)
    {
        if (isset($config['listviews']) && is_array($config['listviews']))
            foreach ($config['listviews'] as $listview) {
                $this->registerViews($listview['parameter'], $listview['views'], $listview['default'], $listview['action']);
            }
    }


    public function registerViews($parameter, $views, $default = null, $action = null)
    {
        $action = $this->resolveAction($action);
        $this->views[$action][$parameter]['default'] = $default;
        $this->views[$action][$parameter]['views'] = $views;
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

    public function getViewNames($parameter, $action = null)
    {
        $action = $this->resolveAction($action);

        $views = $this->resolveViews($action, $parameter);

        return array_keys($views);
    }

    public function getParameters($action = null)
    {
        $action = $this->resolveAction($action);
        return $this->parameters[$action];
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
}