<?php

if (request()->input('option')) {
    $option = request()->input('option');
} else {
    $option = '';
}
/* Optionsschalter */
switch ($option) {

    default :
        $t = new todo ();
        $t->my_todo_liste(Auth::user()->id, '0');
        break;

    /* OFFEN */
    case "neue_auftraege" :
        $t = new todo ();
        $t->todo_liste2(Auth::user()->id, '0');
        break;

    /* OFFEN */
    case "offene_auftraege" :
        $t = new todo ();
        $t->todo_liste3(Auth::user()->id, '0');
        break;

    /* OFFEN */
    case "erledigte_auftraege" :
        $t = new todo ();
        $t->todo_liste2(Auth::user()->id, '1');
        break;

    /* Erledigte Projekte */
    case "erledigte_projekte" :
        $t = new todo ();
        $t->todo_liste(Auth::user()->id, '1');
        break;

    /* Neues Projekt */
    case "neues_projekt" :
        $t = new todo ();
        $t->form_neue_aufgabe(request()->input('t_id'), request()->input('typ'));
        break;

    /* Editieren von Aufgaben und Projekten */
    case "edit" :
        if (request()->has('t_id')) {
            $t = new todo ();
            $t->form_edit_aufgabe(request()->input('t_id'));
        } else {
            echo "Aufgabe oder Projekt wählen";
        }
        break;

    /* Projekt oder Aufgabe löschen */
    case "projekt_loeschen" :
        if (request()->has('t_id')) {
            $t = new todo ();
            $t->projekt_aufgabe_loeschen(request()->input('t_id'));
        } else {
            echo "Aufgabe oder Projekt wählen";
        }
        break;

    /* Definitiv löschen */
    case "loeschen" :
        if (request()->has('t_id')) {
            $t = new todo ();
            $t->projekt_aufgabe_loeschen_sql(request()->input('t_id'), request()->input('art'));
        } else {
            echo "Aufgabe oder Projekt wählen";
        }
        break;

    case "rss" :
        $t = new todo ();
        $t->rss_feed(Auth::user()->id);
        break;

    case "pdf_projekt" :
        if (request()->has('proj_id')) {
            $t = new todo ();
            $t->pdf_projekt(intval(request()->input('proj_id')));
        } else {
            echo "Projekt wählen";
        }
        break;

    case "pdf_auftrag" :
        if (request()->has('proj_id')) {
            $t = new todo ();
            $t->pdf_auftrag(intval(request()->input('proj_id')));
        } else {
            echo "Projekt wählen";
        }
        break;

    case "form_neue_baustelle" :
        $t = new todo ();
        $t->form_neue_baustelle();
        break;

    case "neue_baustelle" :
        if (request()->has('bau_bez') && request()->has('p_id')) {
            $t = new todo ();
            if ($t->neue_baustelle_speichern(request()->input('bau_bez'), request()->input('p_id'))) {
                $bau_bez = request()->input('bau_bez');
                hinweis_ausgeben("Baustelle $bau_bez wurde erstellt");
            }
        } else {
            fehlermeldung_ausgeben('Ihre Eingabe zur Baustelle war unvollständig!');
        }
        break;

    case "baustellen_liste" :
        $t = new todo ();
        $t->baustellen_liste();
        break;

    case "baustellen_liste_inaktiv" :
        $t = new todo ();
        $t->baustellen_liste('0');
        break;

    case "baustelle_aktivieren" :
        $bau_id = request()->input('bau_id');
        $t = new todo ();
        $t->baustelle_aktivieren($bau_id, '1');
        weiterleiten(route('legacy::todo::index', ['option' => 'baustellen_liste'], false));
        break;

    case "baustelle_deaktivieren" :
        $bau_id = request()->input('bau_id');
        $t = new todo ();
        $t->baustelle_aktivieren($bau_id, '0');
        weiterleiten(route('legacy::todo::index', ['option' => 'baustellen_liste_inaktiv'], false));
        break;

    case "verschieben" :
        if (request()->has('t_id')) {
            $t_id = request()->input('t_id');
            $t = new todo ();
            $t->form_verschieben($t_id);
        } else {
            fehlermeldung_ausgeben("Aufgaben/Projekt id eingeben");
        }
        break;

    case "verschieben_snd" :
        if (request()->has('t_id') && request()->has('p_id')) {
            $t_id = request()->input('t_id'); // aufgaben_id T_ID
            $p_id = request()->input('p_id'); // projekt_id UE_ID
            $t = new todo ();
            if ($t->verschieben($t_id, $p_id)) {
                weiterleiten(route('legacy::todo::index', [], false));
            } else {
                fehlermeldung_ausgeben("Verschieben gescheitert");
            }
        } else {
            fehlermeldung_ausgeben("Aufgaben/Projekt id eingeben");
        }
        break;

    case "auftrag_haus" :
        if (request()->has('haus_id')) {
            $t = new todo ();
            $t->auftraege_an_haus(request()->input('haus_id'));
        } else {
            fehlermeldung_ausgeben("Haus wählen");
        }
        break;

    case "api_ticket_test":
        /*Export TODO'S'*/
        $config = array(
            'url' => 'http://192.168.2.16/ticket/api/http.php/tickets.json',
            'key' => '1BD9ABDCC4784E1BA3872A5440FD06A2'
        );

        $todo = new todo ();
        $auftraege_arr = $todo->get_alle_auftraege(0);

        $anz_a = count($auftraege_arr);

        for ($a = 0; $a < $anz_a; $a++) {
            $t_id = $auftraege_arr [$a] ['T_ID'];
            $datum = $auftraege_arr [$a] ['ERSTELLT'];
            $text = $auftraege_arr [$a] ['TEXT'];
            $text_k = substr($text, 0, 20);
            echo "TEXTK:$text_k<br>";
            $kos_typ = $auftraege_arr [$a] ['KOS_TYP'];
            $kos_id = $auftraege_arr [$a] ['KOS_ID'];
            $r = new rechnung ();
            $kos_bez = $r->kostentraeger_ermitteln($kos_typ, $kos_id);

            $data ['name'] = 'BerlussimoAPI';
            $data ['email'] = 'sivac@berlus.de';
            $data ['subject'] = $text;
            $data ['message'] = $text;
            $data ['ip'] = $_SERVER ['REMOTE_ADDR'];
            $data ['body'] = 'BODY MANUAL';
            $data ['zuordnung'] = $kos_bez;
            $data ['kos_typ'] = $kos_typ;
            $data ['kos_id'] = $kos_id;
            $data ['created'] = $datum;
            $data ['attachments'] = array();

            function_exists('curl_version') or die ('CURL support required');
            function_exists('json_encode') or die ('JSON support required');

            // set timeout
            set_time_limit(30);

            // curl post
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $config ['url']);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Berlussimo');
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Expect:',
                'X-API-Key: ' . $config ['key']
            ));
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $result = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code != 201)
                throw new \App\Exceptions\MessageException(
                    new \App\Messages\ErrorMessage('Unable to create ticket: ' . $result)
                );

            $ticket_id = ( int )$result;
            echo "Ticket $ticket_id erstellt<br>";
        }
        break;

    case "erledigt_alle" :
        if (request()->has('t_dats')) {
            if (is_array(request()->input('t_dats'))) {
                $anz_markiert = count(request()->input('t_dats'));
                for ($a = 0; $a < $anz_markiert; $a++) {
                    $t_dat = request()->input('t_dats') [$a];
                    $to = new todo ();
                    $to->als_erledigt_markieren($t_dat);
                }
                weiterleiten_in_sec(route('legacy::todo::index', [], false), 2);
            } else {
                fehlermeldung_ausgeben("Projekte und Aufgaben markieren!!!");
            }
        } else {
            fehlermeldung_ausgeben("Projekte und Aufgaben markieren!!!");
        }
        break;

    case "auftraege_an" :

        if (request()->has('typ') && request()->has('id')) {
            $typ = request()->input('typ');
            $id = request()->input('id');
            $to = new todo ();
            /* Offene */
            $to->liste_auftrage_an($typ, $id, 0);
            /* Erledigte */
            $to->liste_auftrage_an($typ, $id, 1);
        }
        break;

    /* Auftragsuche Formular */
    case "auftrag_suche" :
        $t = new todo ();
        if (request()->has('typ_int_ext')) {

            $t->form_suche(request()->input('typ_int_ext'));
        } else {
            $t->form_suche();
        }
        break;

    /* Auftragsuche Formular sendet Suchdaten */
    case "auftrag_suche_send" :
        $t = new todo ();
        if (request()->has('benutzer_typ') && request()->has('benutzer_id')) {
            $typ = request()->input('benutzer_typ');
            $id = request()->input('benutzer_id');
            weiterleiten(route('legacy::todo::index', ['option' => 'auftraege_an', 'typ' => $typ, 'id' => $id], false));
        }
        break;
} // end switch