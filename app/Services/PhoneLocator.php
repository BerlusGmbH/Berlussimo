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
            $this->phone = $config['map'][$this->client];
        }
    }

    protected function hasPhone()
    {
        return key_exists('map', $this->config)
            && key_exists($this->client, $this->config['map'])
            && key_exists('ip', $this->config['map'][$this->client])
            && (
                key_exists('ip', $this->config['map'][$this->client])
                || key_exists('url', $this->config)
            );
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
        if(key_exists('url', $this->phone)) {
            $url = $this->phone['url'];
        } else {
            $url = $this->config['url'];
        }

        $url = str_replace('<ipaddress>', $this->phone['ip'], $url);
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