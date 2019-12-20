<?php


namespace App\Services\Immoware24;

use Exception;


class UnitMapper extends Mapper
{
    public function getOBJ_ID()
    {
        $house = $this->model->haus;
        if (empty($house)) {
            throw new Exception();
        }
        $property = $house->objekt;
        if (empty($property)) {
            throw new Exception();
        }
        return $property->OBJEKT_ID;
    }

    public function getGEB_ID()
    {
        return $this->model->HAUS_ID;
    }

    public function getBezeichnung()
    {
        return trim($this->model->EINHEIT_KURZNAME);
    }

    public function getLage()
    {
        return trim($this->model->EINHEIT_LAGE);
    }

    public function getVENr()
    {
        return $this->getVE_ID();
    }

    public function getVE_ID()
    {
        return $this->model->EINHEIT_ID;
    }

    public function getArt()
    {
        $type = $this->model->TYP;
        if (isset($type)) {
            switch ($type) {
                case "Wohnraum":
                case "Wohneigentum":
                    return "WE";
                case "Gewerbe":
                    return "GE";
                case "Garage":
                    return "GA";
                case "Stellplatz":
                    return "ST";
                case "Keller":
                    return "KE";
                case "Freiflaeche":
                    return "FR";
                case "Werbeflaeche":
                    return "WF";
                case "Kinderwagenbox":
                    return "BO";
                case "Zimmer (möbliert)":
                    return "ZI";
            }
        }
        return "";
    }

    public function getZimmer()
    {
        return trim($this->model->room_count);
    }

    public function getU_Wohnfläche()
    {
        return $this->getGesamtfläche();
    }

    public function getGesamtfläche()
    {
        try {
            return number_format(trim($this->model->EINHEIT_QM), 2, ',', '.');
        } catch (Exception $e) {
            return "";
        }
    }

    public function getU_Heizfläche()
    {
        return $this->getGesamtfläche();
    }

    public function getU_MEA()
    {
        $detail = $this->model->commonDetails()->where('DETAIL_NAME', 'WEG-Anteile')->first();
        if (!is_null($detail)) {
            return trim(preg_replace('/MEA$/s', '', $detail->DETAIL_INHALT));
        }
        return "";
    }
}