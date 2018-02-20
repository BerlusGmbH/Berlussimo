<?php

namespace App\Services;


class PhoneLocator
{
    protected $config, $client, $phone;

    public function __construct($config = [])
    {
        $this->config = $config;
        $this->client = request()->ip();
        if ($this->workplaceHasPhone()) {
            $this->phone = $config['map'][$this->client];
        }
    }

    public function workplaceHasPhone()
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
        if ($this->workplaceHasPhone()) {
            return $this->decoratePhoneUrl($number);
        } else {
            return false;
        }
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
}