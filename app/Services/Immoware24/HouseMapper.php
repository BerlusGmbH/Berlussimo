<?php


namespace App\Services\Immoware24;


class HouseMapper extends Mapper
{
    public function getOBJ_ID()
    {
        return $this->model->OBJEKT_ID;
    }

    public function getGEB_ID()
    {
        return $this->model->HAUS_ID;
    }

    public function getStraße()
    {
        return $this->getName();
    }

    public function getName()
    {
        return $this->model->name;
    }

    public function getBauart()
    {
        $year = $this->getBaujahr();
        if (!empty($year) && $year > 1800) {
            if ($year > 1949) {
                return "Neubau";
            }
        }
        return "Altbau";
    }

    public function getBaujahr()
    {
        $property = $this->model->objekt;
        if (empty($property)) {
            return "";
        }
        $details = $property->commonDetails()->where('DETAIL_NAME', 'Baujahr')->get();
        if (!$details->isEmpty()) {
            return trim($details[0]->DETAIL_INHALT);
        }
        return "";
    }

    public function getGebäudeart()
    {
        return "Haus";
    }

    public function getDenkmalschutz()
    {

    }
}