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

    public function url($number, $comment)
    {
        if ($this->hasPhone()) {
            return $this->dialUrl($number, $comment);
        } else {
            return $this->defaultUrl($number, $comment);
        }
    }

    protected function dialUrl($number, $comment)
    {
        $url = $this->decoratePhoneUrl($number);
        $attributes = 'href="tel: ' . $number . '" onclick="$.get(\'' . $url . '\'); return false;"';
        return $this->renderUrl($number, $attributes, $comment);
    }

    protected function decoratePhoneUrl($number)
    {
        $url = str_replace('<ipaddress>', $this->phone['ip'], $this->phone['url']);
        $url = str_replace('<number>', $number, $url);
        return $url;
    }

    protected function renderUrl($number, $attributes, $comment)
    {
        $url = '<a ' . $attributes . '>' . $number;
        if ($comment !== '') {
            $url .= ', ' . $comment;
        }
        $url .= '</a>';
        return $url;
    }

    protected function defaultUrl($number, $comment)
    {
        $attributes = 'href="tel: ' . $number . '"';
        return $this->renderUrl($number, $attributes, $comment);
    }
}