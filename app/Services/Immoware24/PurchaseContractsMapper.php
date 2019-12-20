<?php


namespace App\Services\Immoware24;


use App\Models\HOAFeeDefinition;
use Carbon\Carbon;
use Exception;

class PurchaseContractsMapper extends Mapper
{
    protected $owners;
    protected $phones;
    protected $faxs;
    protected $emails;
    protected $SEPAMandate;
    protected $unit;
    protected $start, $end;

    public function __construct($model, & $config)
    {
        parent::__construct($model, $config);
        $this->owners = $this->model->eigentuemer;
        $this->phones = $this->owners->pluck('phones')->collapse()->unique();
        $this->faxs = $this->owners->pluck('faxs')->collapse()->unique();
        $this->emails = $this->owners->pluck('emails')->collapse()->unique();
        $this->SEPAMandate = $this->currentSEPAMandate();
        $this->unit = $this->model->einheit;
        $this->start = $this->start($this->model->VON, $this->model->BIS);
        $this->end = $this->end($this->model->VON, $this->model->BIS);
    }

    protected function currentSEPAMandate()
    {
        $until = $this->model->BIS;
        if ($until === '0000-00-00') {
            return $this->model->SEPAMandates()->active('=', Carbon::today())->first();
        } else {
            return $this->model->SEPAMandates()->active('=', $until)->first();
        }
    }

    public function getOBJ_ID()
    {
        return $this->unit->haus->objekt->OBJEKT_ID;
    }

    public function getVE_ID()
    {
        return $this->unit->EINHEIT_ID;
    }

    public function getK_ID()
    {
        $ownersId = implode(':', $this->owners->pluck('id')->sort()->values()->all());
        if (array_has($this->config['person-to-contact-ids'], $ownersId)) {
            return $this->config['person-to-contact-ids'][$ownersId];
        } else {
            throw new Exception();
        }
    }

    public function getTyp()
    {
        return "E";
    }

    public function getvon()
    {
        return $this->date($this->model->VON);
    }

    public function getbis()
    {
        if ($this->model->BIS === '0000-00-00') {
            return "";
        }
        return $this->date($this->model->BIS);
    }

    public function getGewerbe()
    {
        $type = $this->unit->TYP;
        switch ($type) {
            case "Gewerbe":
                return "G";
            default:
                return "N";
        }
    }

    public function getLastschrift()
    {
        if (!empty($this->SEPAMandate)) {
            return "1";
        }
        return "0";
    }

    public function getMahnsperre()
    {
        return "";
    }

    public function getUmlAusfWagnis()
    {
        return "";
    }

    public function getNotizen()
    {
        return "";
    }

    public function getSV_Brutto()
    {
        return "";
    }

    public function getSV_Fälligkeit()
    {
        return "";
    }

    public function getZ_Miete()
    {
        return "";
    }

    public function getZ_BKV()
    {
        return "";
    }

    public function getZ_HKV()
    {
        return "";
    }

    public function getZ_Garage()
    {
        return "";
    }

    public function getZ_Stellplatz()
    {
        return "";
    }

    public function getZ_sonstMiete()
    {
        return "";
    }

    public function getZ_Hausgeld()
    {
        $hoaFeeDefinitions = $this->model->hoaFeeDefinitions($this->start, $this->end)
            ->where('GRUPPE', 'Hausgeld')
            ->where('E_KONTO', '!=', 6050);
        $hoaFees = HOAFeeDefinition::sumDefinitions(
            $hoaFeeDefinitions,
            $this->start,
            $this->end
        );
        return number_format($hoaFees, 2, ',', '.');
    }

    public function getZ_Rücklage1()
    {
        return "";
    }

    public function getZ_Rücklage2()
    {
        return "";
    }

    public function getU_Wohnfläche()
    {
        if (!empty($this->unit)) {
            return number_format(trim($this->unit->EINHEIT_QM), 2, ',', '.');
        }
        return "";
    }

    public function getU_Heizfläche()
    {
        if (!empty($this->unit)) {
            return number_format(trim($this->unit->EINHEIT_QM), 2, ',', '.');
        }
        return "";
    }

    public function getU_KabelTV()
    {
        return "";
    }

    public function getU_Personen()
    {
        return $this->owners->count();
    }

    public function getU_Einheiten()
    {
        return "";
    }

    public function getU_Garagen()
    {
        return "";
    }

    public function getU_Stellplätze()
    {
        return "";
    }

    public function getU_MEA()
    {
        if (!empty($this->unit)) {
            $detail = $this->unit->commonDetails()->where('DETAIL_NAME', 'WEG-Anteile')->first();
            if (!is_null($detail)) {
                return trim(preg_replace('/MEA$/s', '', $detail->DETAIL_INHALT));
            }
        }
        return "";
    }

    public function getBK_ID()
    {
        if (!empty($this->SEPAMandate)) {
            $id = 'sepa:'
                . implode(':', $this->owners->pluck('id')->sort()->values()->all())
                . '|' . $this->SEPAMandate->M_ID;
            if (array_has($this->config['bank-account-ids'], $id)) {
                return $this->config['bank-account-ids'][$id];
            } else {
                $this->logger->error(
                    'Missing BK_ID for contract (id: '
                    . $this->model->ID . ' unit: ' . $this->model->einheit->EINHEIT_KURZNAME . ').'
                );
                throw new Exception();
            }
        }
        return "";
    }

    public function getGläubigerID()
    {
        if (!empty($this->SEPAMandate)) {
            return trim($this->SEPAMandate->GLAEUBIGER_ID);
        }
        return "";
    }

    public function getMandatRef()
    {
        if (!empty($this->SEPAMandate)) {
            return trim($this->SEPAMandate->M_REFERENZ);
        }
        return "";
    }

    public function getMandatVon()
    {
        if (!empty($this->SEPAMandate)) {
            return $this->date($this->SEPAMandate->M_ADATUM);
        }
        return "";
    }

    public function getMandatBis()
    {
        if (!empty($this->SEPAMandate)
            && $this->SEPAMandate->M_EDATUM !== '9999-12-31') {
            return $this->date($this->SEPAMandate->M_EDATUM);
        }
        return "";
    }

    public function getMandatUnterschrift()
    {
        if (!empty($this->SEPAMandate)) {
            return $this->date($this->SEPAMandate->M_UDATUM);
        }
        return "";
    }
}