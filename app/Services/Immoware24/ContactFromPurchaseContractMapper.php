<?php


namespace App\Services\Immoware24;


use Exception;

class ContactFromPurchaseContractMapper extends Mapper
{
    protected $owners;
    protected $phones;
    protected $faxs;
    protected $emails;

    public function __construct($model, & $config)
    {
        parent::__construct($model, $config);
        $this->owners = $this->model->eigentuemer;
        $this->phones = $this->owners->pluck('phones')->collapse()->unique();
        $this->faxs = $this->owners->pluck('faxs')->collapse()->unique();
        $this->emails = $this->owners->pluck('emails')->collapse()->unique();
    }

    public function getK_ID()
    {
        $ownersId = implode(':', $this->owners->pluck('id')->sort()->values()->all());
        if (array_has($this->config['person-to-contact-ids'], $ownersId)) {
            throw new Exception();
        }
        if ($this->owners->count() > 1) {
            $nextPersonId = $this->config['next-person-id'];
            $this->config['person-to-contact-ids'][$ownersId] = $nextPersonId;
            $this->config['next-person-id'] = $nextPersonId + 1;
            return $nextPersonId;
        } else {
            $this->config['person-to-contact-ids'][$ownersId] = $this->owners[0]->id;
            return $this->owners[0]->id;
        }
    }

    public function getGläubigerID()
    {
        return "";
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
        return $this->field($this->owners, 0, 'first_name', 128);
    }

    public function getNachname1()
    {
        return $this->field($this->owners, 0, 'name', 128);
    }

    public function getTitel2()
    {
        return "";
    }

    public function getVorname2()
    {
        return $this->field($this->owners, 1, 'first_name', 128);
    }

    public function getNachname2()
    {
        return $this->field($this->owners, 1, 'name', 128);
    }

    public function getTitel3()
    {
        return "";
    }

    public function getVorname3()
    {
        return $this->field($this->owners, 2, 'first_name', 128);
    }

    public function getNachname3()
    {
        return $this->field($this->owners, 2, 'name', 128);
    }

    public function getTitel4()
    {
        return "";
    }

    public function getVorname4()
    {
        return $this->field($this->owners, 3, 'first_name', 128);
    }

    public function getNachname4()
    {
        return $this->field($this->owners, 3, 'name', 128);
    }

    public function getStraße()
    {
        return "";
    }

    public function getPLZ()
    {
        return "";
    }

    public function getOrt()
    {
        return "";
    }

    public function getZustellhinweis()
    {
        return "";
    }

    public function getTelPrivat()
    {
        return $this->field($this->phones, 2, 'detail_inhalt_one_line', 48);
    }

    public function getTelDienst()
    {
        return $this->field($this->phones, 0, 'detail_inhalt_one_line', 48);
    }

    public function getTelHandy()
    {
        return $this->field($this->phones, 1, 'detail_inhalt_one_line', 48);
    }

    public function getFax()
    {
        return $this->field($this->faxs, 0, 'detail_inhalt_one_line', 48);
    }

    public function getEmail()
    {
        return $this->field($this->emails, 0, 'detail_inhalt_one_line', 64);
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