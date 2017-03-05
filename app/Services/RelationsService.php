<?php

namespace App\Services;

use App\Models\Auftraege;
use App\Models\BaustellenExtern;
use App\Models\Details;
use App\Models\Einheiten;
use App\Models\Haeuser;
use App\Models\Kaufvertraege;
use App\Models\Mietvertraege;
use App\Models\Objekte;
use App\Models\Partner;
use App\Models\Personen;
use App\Models\User;


class RelationsService
{
    static $CLASS_FIELD_TO_FIELD = [
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
            'id' => 'DETAIL_ID',
            'name' => 'DETAIL_NAME',
            'inhalt' => 'DETAIL_INHALT',
            'bemerkung' => 'DETAIL_BEMERKUNG'
        ],
        Personen::class => [
            'id' => 'PERSON_ID',
            'vorname' => 'PERSON_VORNAME',
            'name' => 'PERSON_NACHNAME',
            'geb' => 'PERSON_GEBURTSTAG'
        ],
        Auftraege::class => [
            'id' => 'T_ID',
            'text' => 'TEXT',
            'akut' => 'AKUT',
            'erstellt' => 'ERSTELLT',
            'erledigt' => 'ERLEDIGT'
        ],
        User::class => [
            'id' => 'id',
            'name' => 'name'
        ],
        Partner::class => [
            'id' => 'PARTNER_ID',
            'name' => 'PARTNER_NAME'
        ],
        BaustellenExtern::class => [
            'id' => 'ID'
        ]
    ];

    static $CLASS_COLUMN_TO_RELATIONS = [
        Personen::class => [
            'objekt' => ['mietvertraege.einheit.haus.objekt', 'kaufvertraege.einheit.haus.objekt'],
            'haus' => ['mietvertraege.einheit.haus', 'kaufvertraege.einheit.haus'],
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
            'kaufvertrag' => 'kaufvertraege',
            'person' => ['mietvertraege.mieter', 'kaufvertraege.eigentuemer'],
            'detail' => 'details'
        ],
        Haeuser::class => [
            'objekt' => 'objekt',
            'haus' => '',
            'einheit' => 'einheiten',
            'mietvertrag' => 'einheiten.mietvertraege',
            'kaufvertrag' => 'einheiten.kaufvertraege',
            'detail' => 'details'
        ],
        Objekte::class => [
            'objekt' => '',
            'haus' => 'haeuser',
            'einheit' => 'haeuser.einheiten',
            'mietvertrag' => 'haeuser.einheiten.mietvertraege',
            'kaufvertrag' => 'haeuser.einheiten.kaufvertraege',
            'detail' => 'details'
        ],
        Auftraege::class => [
            'auftrag' => '',
            'an' => 'an',
            'von' => 'von',
            'kostenträger' => 'kostentraeger'
        ],
        User::class => [
            'mitarbeiter' => '',
            'name' => 'name'
        ],
        Partner::class => [
            'partner' => ''
        ]
    ];

    static $CLASS_RELATION_TO_MANY = [
        Personen::class => [
            'mietvertraege.einheit.haus.objekt' => [-4, Mietvertraege::class],
            'kaufvertraege.einheit.haus.objekt' => [-4, Kaufvertraege::class],
            'mietvertraege.einheit.haus' => [-3, Mietvertraege::class],
            'kaufvertraege.einheit.haus' => [-3, Kaufvertraege::class],
            'mietvertraege.einheit' => [-2, Mietvertraege::class],
            'kaufvertraege.einheit' => [-2, Kaufvertraege::class],
        ],
        Einheiten::class => [
            'mietvertraege.mieter' => [-4, Mietvertraege::class],
            'kaufvertraege.eigentuemer' => [-4, Kaufvertraege::class],
        ],
    ];

    static $CLASS_COLUMN_TO_CLASS = [
        Auftraege::class => [
            'an' => Auftraege::class,
            'von' => User::class,
            'kostenträger' => Auftraege::class
        ],
        'default' => [
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
            'adresse' => Details::class,
            'auftrag' => Auftraege::class,
            'kostenträger' => Einheiten::class,
            'mitarbeiter' => User::class,
            'partner' => Partner::class,
            'baustelle' => BaustellenExtern::class
        ]
    ];

    static $CLASS_COLUMN_TO_POLY = [
        Auftraege::class => [
            'an' => true,
            'kostenträger' => true
        ]
    ];

    static $CLASS_TO_COLUMN = [
        Objekte::class => 'objekt',
        Haeuser::class => 'haus',
        Einheiten::class => 'einheit',
        Mietvertraege::class => 'mietvertrag',
        Kaufvertraege::class => 'kaufvertrag',
        Details::class => 'hinweis',
        Details::class => 'telefon',
        Details::class => 'email',
        Personen::class => 'person',
        Details::class => 'detail',
        Auftraege::class => 'auftrag',
        User::class => 'mitarbeiter',
        Partner::class => 'partner'
    ];

    public function classColumnFieldToField($class, $column, $field)
    {
        $class = $this->classColumnToClass($class, $column);
        return $this->classFieldToField($class, $field);
    }

    public function classColumnToClass($class, $column)
    {
        return is_null(self::$CLASS_COLUMN_TO_CLASS[$class][$column]) ? self::$CLASS_COLUMN_TO_CLASS['default'][$column] : self::$CLASS_COLUMN_TO_CLASS[$class][$column];
    }

    public function classColumnToPoly($class, $column)
    {
        return is_null(self::$CLASS_COLUMN_TO_POLY[$class][$column]) ? false : self::$CLASS_COLUMN_TO_POLY[$class][$column];
    }

    public function classFieldToField($class, $field)
    {
        return self::$CLASS_FIELD_TO_FIELD[$class][$field];
    }

    public function classColumnToRelations($baseclass, $column)
    {
        $relationships = self::$CLASS_COLUMN_TO_RELATIONS[$baseclass][$column];
        if (!is_array($relationships)) {
            $relationships = [$relationships];
        }
        return $relationships;
    }

    public function classRelationToMany($class, $relation)
    {
        $toMany = self::$CLASS_RELATION_TO_MANY[$class][$relation];
        return isset($toMany) ? $toMany : [0, $class];
    }

    public function classToColumn($column)
    {
        return self::$CLASS_TO_COLUMN[$column];
    }
}