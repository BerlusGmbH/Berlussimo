<?php


namespace App\Services\Immoware24;


use App\Models\Bankkonten;
use App\Models\Details;
use App\Models\Objekte;

class ContactFromPartnerMapper extends Mapper
{
    protected $phones;
    protected $faxs;
    protected $emails;

    public function __construct($model, & $config)
    {
        parent::__construct($model, $config);
        $this->phones = $this->model->phones;
        $this->faxs = $this->model->faxs;
        $this->emails = $this->model->emails;
    }

    public function getK_ID()
    {
        $partnerOffset = $this->config['options']['partner-offset'];
        return $this->model->PARTNER_ID + $partnerOffset;
    }

    public function getGläubigerID()
    {
        $bankAccounts = $this->model->bankaccounts;

        if (!$bankAccounts->isEmpty()) {
            $bankAccountIds = $bankAccounts->pluck('KONTO_ID');

            if (!$bankAccountIds->isEmpty()) {
                $gIds = Details::where('DETAIL_NAME', 'GLAEUBIGER_ID')
                    ->where('DETAIL_ZUORDNUNG_TABELLE', 'GELD_KONTEN')
                    ->whereIn('DETAIL_ZUORDNUNG_ID', $bankAccountIds)
                    ->pluck('DETAIL_INHALT');

                if (!$gIds->isEmpty()) {
                    return $gIds[0];
                }
            }
        }

        $id = $this->model->PARTNER_ID;

        $propertyIds = Objekte::whereHas('eigentuemer', function ($query) use ($id) {
            $query->where('PARTNER_ID', $id);
        })->pluck('OBJEKT_ID');

        if ($propertyIds->isEmpty()) {
            return "";
        }

        $bankAccountIds = Bankkonten::whereHas('objekte', function ($query) use ($propertyIds) {
            $query->whereIn('OBJEKT_ID', $propertyIds);
        })->pluck('KONTO_ID');

        if ($bankAccountIds->isEmpty()) {
            return "";
        }

        $gIds = Details::where('DETAIL_NAME', 'GLAEUBIGER_ID')
            ->where('DETAIL_ZUORDNUNG_TABELLE', 'GELD_KONTEN')
            ->whereIn('DETAIL_ZUORDNUNG_ID', $bankAccountIds)
            ->pluck('DETAIL_INHALT');

        if ($gIds->isEmpty()) {
            return "";
        } else {
            return $gIds[0];
        }
    }

    public function getAnrede()
    {
        return "";
    }

    public function getFirma()
    {
        return "";
    }

    public function getTitel1()
    {
        return "";
    }

    public function getVorname1()
    {
        return "";
    }

    public function getNachname1()
    {
        return $this->model->name_one_line;
    }

    public function getStraße()
    {
        $street = trim($this->model->STRASSE) . " " . trim($this->model->NUMMER);
        return empty(trim($street)) ? "" : $street;
    }

    public function getPLZ()
    {
        return $this->model->PLZ;
    }

    public function getOrt()
    {
        return $this->model->ORT;
    }

    public function getZustellhinweis()
    {
        return "";
    }

    public function getTelPrivat()
    {
        return $this->phones->count() > 2 ? trim($this->crop($this->phones[2]->detail_inhalt_one_line, 48)) : "";
    }

    public function getTelDienst()
    {
        return $this->phones->count() > 0 ? trim($this->crop($this->phones[0]->detail_inhalt_one_line, 48)) : "";
    }

    public function getTelHandy()
    {
        return $this->phones->count() > 1 ? trim($this->crop($this->phones[1]->detail_inhalt_one_line, 48)) : "";
    }

    public function getFax()
    {
        return $this->faxs->count() > 0 ? trim($this->crop($this->faxs[0]->detail_inhalt_one_line, 48)) : "";
    }

    public function getEmail()
    {
        return $this->emails->count() > 0 ? trim($this->crop($this->emails[0]->detail_inhalt_one_line, 64)) : "";
    }

    public function getNotizen()
    {
        $value = "";
        if ($this->phones->count() > 3) {
            $count = $this->phones->count();
            for ($i = 3; $i < $count; $i++) {
                if ($i > 3) {
                    $value .= ' | ';
                }
                $value .= 'phone: '
                    . $this->field($this->phones, $i, 'detail_inhalt_one_line', 128);
                $comment = $this->field($this->phones, $i, 'detail_bemerkung_one_line', 128);
                if ($comment !== "") {
                    $value .= ' (' . $comment . ')';
                }
            }
        }
        if ($this->emails->count() > 1) {
            if ($value !== "") {
                $value .= ' | ';
            }
            $count = $this->emails->count();
            for ($i = 1; $i < $count; $i++) {
                if ($i > 1) {
                    $value .= ' | ';
                }
                $value .= 'email: '
                    . $this->field($this->emails, $i, 'detail_inhalt_one_line', 128);
                $comment = $this->field($this->emails, $i, 'detail_bemerkung_one_line', 128);
                if ($comment !== "") {
                    $value .= ' (' . $comment . ')';
                }
            }
        }
        return $this->crop($value, 1024);
    }
}