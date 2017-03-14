<?php

namespace App\Services;

use App\Models\Auftraege;
use App\Models\BaustellenExtern;
use App\Models\Details;
use App\Models\Einheiten;
use App\Models\Haeuser;
use App\Models\Kaufvertraege;
use App\Models\Lager;
use App\Models\Mietvertraege;
use App\Models\Objekte;
use App\Models\Partner;
use App\Models\Personen;
use App\Models\User;
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
        ],
        Lager::class => [
            'id' => 'LAGER_ID'
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
            'person' => ['', Personen::class],
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
            'person' => ['mieter', Personen::class]
        ],
        'kaufvertrag' => [
            'objekt' => ['einheit.haus.objekt', Objekte::class],
            'haus' => ['einheit.haus', Haeuser::class],
            'einheit' => ['einheit', Einheiten::class],
            'mietvertrag' => ['', Mietvertraege::class],
            'telefon' => ['personen.phones', Details::class],
            'email' => ['personen.emails', Details::class],
            'person' => ['personen', Personen::class]
        ],
        'einheit' => [
            'objekt' => ['haus.objekt', Objekte::class],
            'haus' => ['haus', Haeuser::class],
            'einheit' => ['', Einheiten::class],
            'mietvertrag' => ['mietvertraege', Mietvertraege::class],
            'kaufvertrag' => ['kaufvertraege', Kaufvertraege::class],
            'person' => [['mietvertraege.mieter', 'kaufvertraege.eigentuemer'], Personen::class],
            'detail' => ['details', Details::class]
        ],
        'haus' => [
            'objekt' => ['objekt', Objekte::class],
            'haus' => ['', Haeuser::class],
            'einheit' => ['einheiten', Einheiten::class],
            'mietvertrag' => ['einheiten.mietvertraege', Mietvertraege::class],
            'kaufvertrag' => ['einheiten.kaufvertraege', Kaufvertraege::class],
            'person' => [['einheiten.mietvertraege.mieter','einheiten.kaufvertraege.eigentuemer'], Personen::class],
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
            'an' => ['an', [User::class, Partner::class]],
            'von' => ['von', User::class],
            'kostenträger' => [
                'kostentraeger', [
                    BaustellenExtern::class, Partner::class, User::class,
                    Mietvertraege::class, Kaufvertraege::class, Objekte::class,
                    Haeuser::class, Einheiten::class, Wirtschaftseinheiten::class
                ]
            ],
            'mitarbeiter' => [['anUser', 'von'], User::class]
        ],
        'mitarbeiter' => [
            'mitarbeiter' => ['', User::class]
        ],
        'partner' => [
            'partner' => ['', Partner::class]
        ],
        'baustelle' => [
            'baustelle' => ['', BaustellenExtern::class]
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
        Haeuser::class => [
            'einheiten.mietvertraege.mieter' => [-4, Mietvertraege::class],
            'einheiten.kaufvertraege.eigentuemer' => [-4, Kaufvertraege::class],
        ],
        Auftraege::class => [
            'anUser' => [-4, User::class],
            'von' => [-4, User::class]
        ]
    ];

    static $CLASS_TO_COLUMN = [
        Objekte::class => 'objekt',
        Haeuser::class => 'haus',
        Einheiten::class => 'einheit',
        Mietvertraege::class => 'mietvertrag',
        Kaufvertraege::class => 'kaufvertrag',
        Personen::class => 'person',
        Details::class => 'detail',
        Auftraege::class => 'auftrag',
        User::class => 'mitarbeiter',
        Partner::class => 'partner',
        Auftraege::class => 'auftrag'
    ];

    public function columnColumnToClass($outer, $inner)
    {
        return self::$COLUMN_COLUMN_TO_RELATIONS[$outer][$inner][1];
    }

    public function columnToClass($class)
    {
        return $this->columnColumnToClass($class, $class);
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