<?php

namespace App\Models\Traits;

trait ExternalKey
{
    public function getExternalKeyName()
    {
        return isset($this->externalKey) ? $this->externalKey : null;
    }
}