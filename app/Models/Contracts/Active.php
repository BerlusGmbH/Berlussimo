<?php

namespace App\Models\Contracts;


interface Active
{
    public function getStartDateFieldName();

    public function getEndDateFieldName();
}