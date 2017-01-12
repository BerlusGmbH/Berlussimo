<?php

namespace App\Services;

use App\Models\Details;
use App\Models\Einheiten;
use App\Models\Haeuser;
use App\Models\Kaufvertraege;
use App\Models\Mietvertraege;
use App\Models\Objekte;
use App\Models\Personen;


class RelationsService
{
    const CLASS_FIELD_TO_FIELD = [
        Mietvertraege::class => [
            'id' => 'MIETVERTRAG_ID',
            'von' => 'MIETVERTRAG_VON',
            'bis' => 'MIETVERTRAG_BIS'
        ],
        Kaufvertraege::class => [
            'id' => 'ID',
            'von' => 'VON',
            'bis' => 'BIS'
        ],
        Einheiten::class => [
            'id' => 'EINHEIT_ID',
            'lage' => 'EINHEIT_LAGE',
            'name' => 'EINHEIT_KURZNAME',
            'typ' => 'TYP',
            'qm' => 'EINHEIT_QM'
        ],
        Haeuser::class => [
            'id' => 'HAUS_ID',
            'str' => 'HAUS_STRASSE',
            'nr' => 'HAUS_NUMMER',
            'ort' => 'HAUS_STADT',
            'plz' => 'HAUS_PLZ'
        ],
        Objekte::class => [
            'id' => 'OBJEKT_ID',
            'name' => 'OBJEKT_KURZNAME'
        ],
        Details::class => [
            'inhalt' => 'DETAIL_INHALT',
            'bemerkung' => 'DETAIL_BEMERKUNG'
        ],
        Personen::class => [
            'id' => 'PERSON_ID',
            'vorname' => 'PERSON_VORNAME',
            'name' => 'PERSON_NACHNAME',
            'geb' => 'PERSON_GEBURTSTAG'
        ]
    ];

    const CLASS_COLUMN_TO_RELATIONSHIP = [
        Personen::class => [
            'objekt' => ['mietvertraege.einheit.haus.objekt', 'kaufvertraege.einheit.haus.objekt'],
            'haus' => ['mietvertraege.einheit.haus','kaufvertraege.einheit.haus'],
            'einheit' => ['mietvertraege.einheit', 'kaufvertraege.einheit'],
            'mietvertrag' => 'mietvertraege',
            'kaufvertrag' => 'kaufvertraege',
            'telefon' => 'phones',
            'email' => 'emails',
            'hinweis' => 'hinweise',
            'person' => '',
            'detail' => 'commonDetails',
            'adresse' => 'adressen'
        ],
        Mietvertraege::class => [
            'objekt' => 'einheit.haus.objekt',
            'haus' => 'einheit.haus',
            'einheit' => 'einheit',
            'mietvertrag' => '',
            'telefon' => 'personen.phones',
            'email' => 'personen.emails',
            'person' => 'mieter'
        ],
        Kaufvertraege::class => [
            'objekt' => 'einheit.haus.objekt',
            'haus' => 'einheit.haus',
            'einheit' => 'einheit',
            'mietvertrag' => '',
            'telefon' => 'personen.phones',
            'email' => 'personen.emails',
            'person' => 'personen'
        ],
        Einheiten::class => [
            'objekt' => 'haus.objekt',
            'haus' => 'haus',
            'einheit' => '',
            'mietvertrag' => 'mietvertraege',
            'kaufvertrag' => 'kaufvertraege'
        ],
        Haeuser::class => [
            'objekt' => 'objekt',
            'haus' => '',
            'einheit' => 'einheiten',
            'mietvertrag' => 'einheiten.mietvertraege',
            'kaufvertrag' => 'einheiten.kaufvertraege'
        ],
        Objekte::class => [
            'objekt' => '',
            'haus' => 'haueser',
            'einheit' => 'haeuser.einheiten',
            'mietvertrag' => 'haeuser.einheiten.mietvertraege',
            'kaufvertrag' => 'haeuser.einheiten.kaufvertraege'
        ],
    ];

    const COLUMN_TO_CLASS = [
        'objekt' => Objekte::class,
        'haus' => Haeuser::class,
        'einheit' => Einheiten::class,
        'mietvertrag' => Mietvertraege::class,
        'kaufvertrag' => Kaufvertraege::class,
        'hinweis' => Details::class,
        'telefon' => Details::class,
        'email' => Details::class,
        'person' => Personen::class,
        'detail' => Details::class,
        'adresse' => Details::class
    ];

    public function columnFieldToField($column, $field)
    {
        $class = $this->columnToClass($column);
        return $this->classFieldToField($class, $field);
    }

    public function classFieldToField($class, $field)
    {
        return self::CLASS_FIELD_TO_FIELD[$class][$field];
    }

    public function classColumnToRelations($baseclass, $column)
    {
        $relationships = self::CLASS_COLUMN_TO_RELATIONSHIP[$baseclass][$column];
        if (!is_array($relationships)) {
            $relationships = [ $relationships ];
        }
        return $relationships;
    }

    public function columnToClass($column)
    {
        return self::COLUMN_TO_CLASS[$column];
    }
}