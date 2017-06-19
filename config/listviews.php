<?php


return [
    'listviews' => [
        [
            'action' => \App\Http\Controllers\Legacy\BenutzerController::class . '@index',
            'parameter' => 'v',
            'views' => [
                '(ohne)' => '',
                'Mitarbeiterliste' => 'mitarbeiter !mitarbeiter[name:asc] mitarbeiter[id] mitarbeiter[email] mitarbeiter[geburtstag] gewerk mitarbeiter[von bis] mitarbeiter[stundensatz] mitarbeiter[wochenstunden] mitarbeiter[urlaubstage] partner'
            ],
            'default' => 'Mitarbeiterliste'
        ],
        [
            'action' => \App\Http\Controllers\Legacy\BenutzerController::class . '@index',
            'parameter' => 's',
            'views' => [
                '5' => 5,
                '10' => 10,
                '20' => 20,
                '50' => 50,
                '100' => 100
            ],
            'default' => '20'
        ],
        [
            'action' => \App\Http\Controllers\Legacy\EinheitenController::class . '@index',
            'parameter' => 'v',
            'views' => [
                '(ohne)' => '',
                'Listenansicht' => 'einheit !einheit[name] mietvertrag person[mietvertrag] einheit[typ] einheit[qm] einheit[lage] haus objekt',
                'Mieterkontakte' => 'objekt haus !einheit[name] einheit einheit[lage] mietvertrag person[mietvertrag] telefon[mietvertrag] email[mietvertrag]',
                'Leerstand' => '!einheit(!vermietet)[name] haus einheit[lage] einheit einheit[qm]'
            ],
            'default' => 'Listenansicht'
        ],
        [
            'action' => \App\Http\Controllers\Legacy\EinheitenController::class . '@index',
            'parameter' => 's',
            'views' => [
                '5' => 5,
                '10' => 10,
                '20' => 20,
                '50' => 50,
                '100' => 100
            ],
            'default' => '20'
        ],
        [
            'action' => \App\Http\Controllers\Legacy\HaeuserController::class . '@index',
            'parameter' => 'v',
            'views' => [
                '(ohne)' => '',
                'Listenansicht' => 'haus !haus[str:asc nr:asc] haus[plz] haus[ort] detail[count] einheit[count] objekt'
            ],
            'default' => 'Listenansicht'
        ],
        [
            'action' => \App\Http\Controllers\Legacy\HaeuserController::class . '@index',
            'parameter' => 's',
            'views' => [
                '5' => 5,
                '10' => 10,
                '20' => 20,
                '50' => 50,
                '100' => 100
            ],
            'default' => '20'
        ],
        [
            'action' => \App\Http\Controllers\Legacy\ObjekteController::class . '@index',
            'parameter' => 'v',
            'views' => [
                '(ohne)' => '',
                'Listenansicht' => 'objekt haus[count] einheit[count] detail[count]'
            ],
            'default' => 'Listenansicht'
        ],
        [
            'action' => \App\Http\Controllers\Legacy\ObjekteController::class . '@index',
            'parameter' => 's',
            'views' => [
                '5' => 5,
                '10' => 10,
                '20' => 20,
                '50' => 50,
                '100' => 100
            ],
            'default' => '20'
        ],
        [
            'action' => \App\Http\Controllers\Legacy\PersonenController::class . '@index',
            'parameter' => 'v',
            'views' => [
                '(ohne)' => '',
                'Mieter' => 'person mietvertrag einheit[mietvertrag] haus[mietvertrag] objekt[mietvertrag] detail[count]',
                'Arbeitnehmer' => 'person job job[von bis] job[urlaubstage] job[wochenstunden] job[stundensatz]',
                'Personen mit Hinweisen' => 'person hinweis',
                'Personen mit Anschriften' => 'person adresse'
            ],
            'default' => 'Mieter'
        ],
        [
            'action' => \App\Http\Controllers\Legacy\PersonenController::class . '@index',
            'parameter' => 'c',
            'views' => [
                'Mieter' => '!person(mietvertrag)',
                'WEG-Eigentümer' => '!person(kaufvertrag)',
                'Arbeitnehmer' => '!person(arbeitgeber)',
                'Mit Hinweisen' => '!person(hinweis)',
                'Mit Anschriften' => '!person(adresse)'
            ],
            'default' => null
        ],
        [
            'action' => \App\Http\Controllers\Legacy\PersonenController::class . '@index',
            'parameter' => 'f1',
            'views' => [
                '(leer)' => '',
                'Ja' => '!person(job(aktiv))',
                'Nein' => '!person(job(!aktiv))'
            ],
            'dependsOn' => ['c' => 'Arbeitnehmer'],
            'default' => 'Ja'
        ],
        [
            'action' => \App\Http\Controllers\Legacy\PersonenController::class . '@index',
            'parameter' => 'f2',
            'views' => function () {
                $views = ['(leer)' => ''];
                foreach (\App\Models\Partner::has('arbeitnehmer')->get() as $arbeitgeber) {
                    $views = array_add($views, str_limit($arbeitgeber->PARTNER_NAME, 40), '!person(arbeitgeber(id=' . $arbeitgeber->PARTNER_ID . '))');
                }
                return $views;
            },
            'dependsOn' => ['c' => 'Arbeitnehmer'],
            'default' => null
        ],
        [
            'action' => \App\Http\Controllers\Legacy\PersonenController::class . '@index',
            'parameter' => 'f3',
            'views' => [
                '(leer)' => '',
                'Ja' => '!person(mietvertrag(aktiv))',
                'Nein' => '!person(mietvertrag(!aktiv))'
            ],
            'dependsOn' => ['c' => 'Mieter'],
            'default' => null
        ],
        [
            'action' => \App\Http\Controllers\Legacy\PersonenController::class . '@index',
            'parameter' => 's',
            'views' => [
                '5' => 5,
                '10' => 10,
                '20' => 20,
                '50' => 50,
                '100' => 100
            ],
            'default' => '20'
        ],
        [
            'action' => \App\Http\Controllers\Legacy\ToDoController::class . '@index',
            'parameter' => 'v',
            'views' => [
                '(ohne)' => '',
                'Aufgabenliste' => 'auftrag auftrag[erstellt:desc] auftrag[text] von an kostenträger'
            ],
            'default' => 'Aufgabenliste'
        ],
        [
            'action' => \App\Http\Controllers\Legacy\ToDoController::class . '@index',
            'parameter' => 'f',
            'views' => [
                'Eigene' => function () {
                    return '!auftrag(person(id=' . Auth::id() . '))';
                },
                'Von Mir' => function () {
                    return '!auftrag(von(id=' . Auth::id() . '))';
                },
                'An Mich' => function () {
                    return '!auftrag(an(person(id=' . Auth::id() . ')))';
                },
                'Akut' => '!auftrag(akut=JA)',
                'Nicht Akut' => '!auftrag(akut=NEIN)',
                'Erledigt' => '!auftrag(erledigt="1")',
                'Nicht Erledigt' => '!auftrag(erledigt="0")'
            ],
            'default' => null
        ],
        [
            'action' => \App\Http\Controllers\Legacy\ToDoController::class . '@index',
            'parameter' => 's',
            'views' => [
                '5' => 5,
                '10' => 10,
                '20' => 20,
                '50' => 50,
                '100' => 100
            ],
            'default' => '20'
        ],
    ]
];