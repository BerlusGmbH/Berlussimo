<?php

namespace App\Services;

use App\Models\Auftraege;
use App\Models\BaustellenExtern;
use App\Models\Details;
use App\Models\Einheiten;
use App\Models\Haeuser;
use App\Models\Job;
use App\Models\Kaufvertraege;
use App\Models\Lager;
use App\Models\Mietvertraege;
use App\Models\Objekte;
use App\Models\Partner;
use App\Models\Person;
use App\Models\Wirtschaftseinheiten;


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
        Person::class => [
            'id' => 'id',
            'vorname' => 'first_name',
            'name' => 'name',
            'geburtstag' => 'birthday',
        ],
        Auftraege::class => [
            'id' => 'T_ID',
            'text' => 'TEXT',
            'akut' => 'AKUT',
            'erstellt' => 'ERSTELLT',
            'erledigt' => 'ERLEDIGT'
        ],
        Partner::class => [
            'id' => 'PARTNER_ID',
            'name' => 'PARTNER_NAME'
        ],
        BaustellenExtern::class => [
            'id' => 'ID'
        ],
        Lager::class => [
            'id' => 'LAGER_ID'
        ],
        Job::class => [
            'von' => 'join_date',
            'bis' => 'leave_date',
            'stundensatz' => 'hourly_rate',
            'wochenstunden' => 'hours_per_week',
            'urlaubstage' => 'holidays'
        ]
    ];

    static $COLUMN_COLUMN_TO_RELATIONS = [
        'person' => [
            'objekt' => [['mietvertraege.einheit.haus.objekt', 'kaufvertraege.einheit.haus.objekt'], Objekte::class],
            'haus' => [['mietvertraege.einheit.haus', 'kaufvertraege.einheit.haus'], Haeuser::class],
            'einheit' => [['mietvertraege.einheit', 'kaufvertraege.einheit'], Einheiten::class],
            'mietvertrag' => ['mietvertraege', Mietvertraege::class],
            'kaufvertrag' => ['kaufvertraege', Kaufvertraege::class],
            'telefon' => ['phones', Details::class],
            'email' => ['emails', Details::class],
            'hinweis' => ['hinweise', Details::class],
            'person' => ['', Person::class],
            'detail' => ['commonDetails', Details::class],
            'adresse' => ['adressen', Details::class]
        ],
        'mietvertrag' => [
            'objekt' => ['einheit.haus.objekt', Objekte::class],
            'haus' => ['einheit.haus', Haeuser::class],
            'einheit' => ['einheit', Einheiten::class],
            'mietvertrag' => ['', Mietvertraege::class],
            'telefon' => ['personen.phones', Details::class],
            'email' => ['personen.emails', Details::class],
            'person' => ['mieter', Person::class]
        ],
        'kaufvertrag' => [
            'objekt' => ['einheit.haus.objekt', Objekte::class],
            'haus' => ['einheit.haus', Haeuser::class],
            'einheit' => ['einheit', Einheiten::class],
            'kaufvertrag' => ['', Kaufvertraege::class],
            'telefon' => ['personen.phones', Details::class],
            'email' => ['personen.emails', Details::class],
            'person' => ['personen', Person::class]
        ],
        'einheit' => [
            'objekt' => ['haus.objekt', Objekte::class],
            'haus' => ['haus', Haeuser::class],
            'einheit' => ['', Einheiten::class],
            'mietvertrag' => ['mietvertraege', Mietvertraege::class],
            'kaufvertrag' => ['kaufvertraege', Kaufvertraege::class],
            'person' => [['mietvertraege.mieter', 'kaufvertraege.eigentuemer'], Person::class],
            'detail' => [['mietvertraege.mieter.commonDetails', 'kaufvertraege.eigentuemer.commonDetails', 'details'], Details::class],
            'telefon' => [['mietvertraege.mieter.phones', 'kaufvertraege.eigentuemer.phones'], Details::class],
            'email' => [['mietvertraege.mieter.emails', 'kaufvertraege.eigentuemer.emails'], Details::class]
        ],
        'haus' => [
            'objekt' => ['objekt', Objekte::class],
            'haus' => ['', Haeuser::class],
            'einheit' => ['einheiten', Einheiten::class],
            'mietvertrag' => ['einheiten.mietvertraege', Mietvertraege::class],
            'kaufvertrag' => ['einheiten.kaufvertraege', Kaufvertraege::class],
            'person' => [['einheiten.mietvertraege.mieter','einheiten.kaufvertraege.eigentuemer'], Person::class],
            'detail' => ['details', Details::class]
        ],
        'objekt' => [
            'objekt' => ['', Objekte::class],
            'haus' => ['haeuser', Haeuser::class],
            'einheit' => ['haeuser.einheiten', Einheiten::class],
            'mietvertrag' => ['haeuser.einheiten.mietvertraege', Mietvertraege::class],
            'kaufvertrag' => ['haeuser.einheiten.kaufvertraege', Kaufvertraege::class],
            'detail' => ['details', Details::class]
        ],
        'auftrag' => [
            'auftrag' => ['', Auftraege::class],
            'an' => ['an', [Person::class, Partner::class]],
            'von' => ['von', Person::class],
            'kostenträger' => [
                'kostentraeger', [
                    BaustellenExtern::class, Partner::class, Person::class,
                    Mietvertraege::class, Kaufvertraege::class, Objekte::class,
                    Haeuser::class, Einheiten::class, Wirtschaftseinheiten::class
                ]
            ],
            'mitarbeiter' => [['anUser', 'von'], Person::class]
        ],
        'mitarbeiter' => [
            'mitarbeiter' => ['', Person::class],
            'partner' => ['arbeitgeber', Partner::class],
            'job' => ['job', Job::class]
        ],
        'partner' => [
            'partner' => ['', Partner::class]
        ],
        'baustelle' => [
            'baustelle' => ['', BaustellenExtern::class]
        ]
    ];

    static $CLASS_RELATION_TO_MANY = [
        Person::class => [
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
            'mietvertraege.mieter.commonDetails' => [-2, Mietvertraege::class],
            'kaufvertraege.eigentuemer.commonDetails' => [-2, Kaufvertraege::class],
            'mietvertraege.mieter.phones' => [-1, Mietvertraege::class],
            'kaufvertraege.eigentuemer.phones' => [-1, Kaufvertraege::class],
            'details' => [-1, Einheiten::class]
        ],
        Haeuser::class => [
            'einheiten.mietvertraege.mieter' => [-4, Mietvertraege::class],
            'einheiten.kaufvertraege.eigentuemer' => [-4, Kaufvertraege::class],
            'einheiten.mietvertraege.mieter.commonDetails' => [-4, Mietvertraege::class],
            'einheiten.kaufvertraege.eigentuemer.commonDetails' => [-4, Kaufvertraege::class],
            'details' => [-4, Haeuser::class]
        ],
        Auftraege::class => [
            'anUser' => [-4, Person::class],
            'von' => [-4, Person::class]
        ]
    ];

    static $CLASS_TO_COLUMN = [
        Objekte::class => 'objekt',
        Haeuser::class => 'haus',
        Einheiten::class => 'einheit',
        Mietvertraege::class => 'mietvertrag',
        Kaufvertraege::class => 'kaufvertrag',
        Person::class => 'person',
        Details::class => 'detail',
        Auftraege::class => 'auftrag',
        Partner::class => 'partner',
        Auftraege::class => 'auftrag'
    ];

    public function columnToClass($class)
    {
        return $this->columnColumnToClass($class, $class);
    }

    public function columnColumnToClass($outer, $inner)
    {
        if (is_null($inner) || is_null($outer)) {
            return null;
        }
        return self::$COLUMN_COLUMN_TO_RELATIONS[$outer][$inner][1];
    }

    public function classFieldToField($class, $field)
    {
        return self::$CLASS_FIELD_TO_FIELD[$class][$field];
    }

    public function columnColumnToRelations($outer, $inner)
    {
        $relationships = self::$COLUMN_COLUMN_TO_RELATIONS[$outer][$inner][0];
        if(is_null($relationships)) {
            return [];
        }
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