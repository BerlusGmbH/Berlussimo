<?php


namespace App\Services\Immoware24;


use Carbon\Carbon;

abstract class Mapper
{
    protected $model;
    protected $config;
    protected $logger;

    public function __construct($model, & $config)
    {
        $this->model = $model;
        $this->config = &$config;
        $this->logger = $this->config['logger'];
    }

    public function get($name)
    {
        if (method_exists($this, 'get' . $name)) {
            return call_user_func([$this, 'get' . $name]);
        }
        return '';
    }

    public function field($collection, $index, $field, $maxLength)
    {
        return $collection->count() > $index ? trim($this->crop($collection[$index]->{$field}, $maxLength)) : "";
    }

    public function crop($string, $maxLength)
    {
        return substr($string, 0, $maxLength);
    }

    public function oneLine($content)
    {
        $content = str_replace("\r\n", "", $content);
        $content = str_replace("\r", "", $content);
        $content = str_replace("\n", "", $content);
        $content = trim($content, "");
        return $content;
    }

    public function date($date)
    {
        return Carbon::parse($date)->format("d.m.Y");
    }

    protected function start($start, $end)
    {
        $reportingDate = $this->config['options']['reporting-date'];
        if (!isset($reportingDate)) {
            $reportingDate = Carbon::today('UTC')->endOfMonth();
        } else {
            $reportingDate = Carbon::parse($reportingDate)->endOfMonth();
        }
        if ($end !== '0000-00-00') {
            $contractEndEndOfMonth = Carbon::parse($end, 'UTC')->endOfMonth();
            if ($contractEndEndOfMonth->lte($reportingDate)) {
                return $contractEndEndOfMonth->firstOfMonth();
            }
        }
        $contractStartEndOfMonth = Carbon::parse($start, 'UTC')->endOfMonth();
        if ($contractStartEndOfMonth->gte($reportingDate)) {
            return $contractStartEndOfMonth->firstOfMonth();
        }
        return $reportingDate->firstOfMonth();
    }

    protected function end($start, $end)
    {
        $reportingDate = $this->config['options']['reporting-date'];
        if (!isset($reportingDate)) {
            $reportingDate = Carbon::today('UTC')->endOfMonth();
        } else {
            $reportingDate = Carbon::parse($reportingDate)->endOfMonth();
        }
        if ($end !== '0000-00-00') {
            $contractEndEndOfMonth = Carbon::parse($end, 'UTC')->endOfMonth();
            if ($contractEndEndOfMonth->lte($reportingDate)) {
                return $contractEndEndOfMonth;
            }
        }
        $contractStartEndOfMonth = Carbon::parse($start, 'UTC')->endOfMonth();
        if ($contractStartEndOfMonth->gte($reportingDate)) {
            return $contractStartEndOfMonth;
        }
        return $reportingDate;
    }
}