<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoicesInterimPool extends Model
{
    public function finalBill()
    {
        $this->bills()->where('RECHNUNGSTYP', 'Schlussrechnung');
    }

    public function bills()
    {
        $this->hasMany(Invoice::class);
    }

    public function interimBills()
    {
        $this->where('RECHNUNGSTYP', 'Teilrechnung');
    }
}

