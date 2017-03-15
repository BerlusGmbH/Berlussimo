<?php

namespace App\Services;


class PhoneLocator
{
    protected $config, $client, $phone;

    public function __construct($config = [])
    {
        $this->config = $config;
        $this->client = request()->ip();
        if ($this->hasPhone()) {
            $this->phone = $config[$this->client];
        }
    }

    protected function hasPhone()
    {
        return !empty($this->config[$this->client])
        && !empty($this->config[$this->client]['ip'])
        && !empty($this->config[$this->client]['url']);
    }

    public function url($number)
    {
        if ($this->hasPhone()) {
            return $this->dialUrl($number);
        } else {
            return $this->defaultUrl($number);
        }
    }

    protected function dialUrl($number)
    {
        $url = $this->decoratePhoneUrl($number);
        $attributes = 'href="tel: ' . $number . '" onclick="$.get(\'' . $url . '\'); return false;"';
        return $this->renderUrl($number, $attributes);
    }

    protected function decoratePhoneUrl($number)
    {
        $url = str_replace('<ipaddress>', $this->phone['ip'], $this->phone['url']);
        $url = str_replace('<number>', $number, $url);
        return $url;
    }

    protected function renderUrl($number, $attributes)
    {
        return '<a ' . $attributes . '>' . $number . '</a>';
    }

    protected function defaultUrl($number)
    {
        $attributes = 'href="tel: ' . $number . '"';
        return $this->renderUrl($number, $attributes);
    }
}