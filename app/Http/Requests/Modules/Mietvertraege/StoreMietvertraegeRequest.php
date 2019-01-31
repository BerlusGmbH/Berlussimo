<?php

namespace App\Http\Requests\Modules\Mietvertraege;


use App\Http\Requests\Legacy\MietvertraegeRequest;
use DB;
use Validator;

class StoreMietvertraegeRequest extends MietvertraegeRequest
{
    public function rules()
    {
        $move_in_date_rule = 'required|date';
        $v = Validator::make($this->all(), ['unit' => 'required|integer']);
        if ($v->valid()) {
            $units = DB::select(
                "SELECT EINHEIT.id, TYP, EINHEIT_KURZNAME, 
                  IF(MIN(MIETVERTRAG_BIS) = '0000-00-00', '9999-12-31', MAX(MIETVERTRAG_BIS)) AS MIETVERTRAG_BIS 
                FROM MIETVERTRAG RIGHT JOIN EINHEIT ON (EINHEIT.id = MIETVERTRAG.EINHEIT_ID) 
                WHERE MIETVERTRAG_AKTUELL = '1' AND EINHEIT_AKTUELL = '1' 
                  AND EINHEIT.id = ? GROUP BY EINHEIT.id",
                [$this->get('unit')]
            );
            if (!empty($units)) {
                $move_in_date_rule .= '|after:' . $units[0]['MIETVERTRAG_BIS'];
            }
        }

        return [
            'tenants' => 'required|array',
            'unit' => 'required|integer',
            'move-in-date' => $move_in_date_rule,
            'move-out-date' => 'date|after:move-in-date|nullable',
            'rent' => 'required|numeric|min:0',
            'deposit' => 'numeric|min:0',
            'bk-advance' => 'numeric|min:0',
            'hk-advance' => 'numeric|min:0'
        ];
    }

}
