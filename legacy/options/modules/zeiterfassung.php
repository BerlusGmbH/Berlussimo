<?php

$option = request()->input('option');

/* Optionsschalter */
switch ($option) {

    default :
        break;

    case "eigene_zettel" :
        $benutzer_id = Auth::user()->id;
        $benutzer_name = Auth::user()->email;
        // echo "Sie sind $benutzer_name<br>";
        $ze = new zeiterfassung ();
        $ze->eigene_stundenzettel_anzeigen();
        break;

    case "zettel_ansehen" :
        $benutzer_id = Auth::user()->id;
        $benutzer_name = Auth::user()->email;
        $zettel_id = request()->input('zettel_id');
        $ze = new zeiterfassung ();
        $ze->stundenzettel_anzeigen($zettel_id);
        break;

    case "neuer_zettel" :
        $benutzer_id = Auth::user()->id;
        $benutzer_name = Auth::user()->email;
        $ze = new zeiterfassung ();
        $ze->stundenzettel_anlegen($benutzer_id);
        break;

    case "zettel_anlegen" :
        $benutzer_id = request()->input('benutzer_id');
        $beschreibung = request()->input('beschreibung');
        $ze = new zeiterfassung ();
        if (isset ($benutzer_id) && isset ($beschreibung)) {
            $ze->stundenzettel_speichern($benutzer_id, $beschreibung);
        } else {
            fehlermeldung_ausgeben("Bitte füllen Sie alle Felder aus");
        }
        break;

    case "zettel_eingabe" :
        $zettel_id = request()->input('zettel_id');
        $ze = new zeiterfassung ();
        $ze->stundenzettel_erfassen($zettel_id);
        break;

    case "zettel_eingabe1" :
        $ze = new zeiterfassung ();
        $datum = request()->input('datum');
        $zettel_id = request()->input('zettel_id');
        $benutzer_id = request()->input('benutzer_id');
        $leistung_id = request()->input('leistung_id');
        $kostentraeger_typ = request()->input('kostentraeger_typ');
        $kostentraeger_bez = request()->input('kostentraeger_id');
        $dauer_min = request()->input('dauer_min');
        $leistungs_beschreibung = request()->input('leistungs_beschreibung');
        $hinweis = request()->input('hinweis');
        $beginn = request()->input('beginn');
        $ende = request()->input('ende');

        if (!empty ($datum) && !empty ($zettel_id) && !empty ($benutzer_id) && !empty ($kostentraeger_typ) && !empty ($kostentraeger_bez) && !empty ($beginn) && !empty ($ende)) {
            session()->put('beginn', $beginn);
            session()->put('ende', $ende);
            if ($ende == '15:15') {
                session()->forget('beginn');
                session()->forget('ende');
            }
            $d = check_datum($datum);
            if (!$d) {
                fehlermeldung_ausgeben("DATUMSEINGABE FEHLERHAFT");
                return;
            }

            if (empty ($leistung_id) && empty ($leistungs_beschreibung)) {
                echo "Wählen Sie bitte ein Leistung aus, oder geben Sie manuell Ihre Leistungsbeschreibung ein";
                return;
            }
            if (empty ($leistungs_beschreibung) && !empty ($leistung_id)) {
                $ze->zettel_pos_speichern($datum, $benutzer_id, $leistung_id, $zettel_id, $dauer_min, $kostentraeger_typ, $kostentraeger_bez, $hinweis, $beginn, $ende);
            }
            if (!empty ($leistungs_beschreibung) && empty ($leistung_id)) {
                $ze->leistung_in_katalog($datum, $benutzer_id, $leistungs_beschreibung, $zettel_id, $dauer_min, $kostentraeger_typ, $kostentraeger_bez, $hinweis, $beginn, $ende);
            }

            if (!empty ($leistungs_beschreibung) && !empty ($leistung_id)) {
                echo "Entweder Leistung aussuchen oder Leistungsbeschreibung eintragen";
            }
        } else {
            echo "EINGABE UNVOLLSTÄNDIG";
        }
        break;

    case "loeschen" :
        $zettel_id = request()->input('zettel_id');
        $pos_id = request()->input('pos_id');
        if (!empty ($zettel_id) && !empty ($pos_id)) {
            $ze = new zeiterfassung ();
            $ze->pos_loeschen($zettel_id, $pos_id);
        } else {
            hinweis_ausgeben("FEHLER BEIM LÖSCHEN");
            weiterleiten_in_sec(route('web::zeiterfassung::legacy', ['option' => 'zettel_eingabe', 'zettel_id' => $zettel_id], false), 2);
        }
        break;

    case "aendern" :
        $zettel_id = request()->input('zettel_id');
        $pos_id = request()->input('pos_id');
        if (!empty ($zettel_id) && !empty ($pos_id)) {
            $ze = new zeiterfassung ();
            $ze->form_zeile_aendern($zettel_id, $pos_id);
        } else {
            hinweis_ausgeben("FEHLER BEIM ÄNDERN");
            weiterleiten_in_sec(route('web::zeiterfassung::legacy', ['option' => 'zettel_eingabe', 'zettel_id' => $zettel_id], false), 2);
        }
        break;

    case "zettel_zeile_aendern" :
        $ze = new zeiterfassung ();
        $datum = request()->input('datum');
        $zettel_id = request()->input('zettel_id');
        $pos_dat = request()->input('pos_dat');
        $benutzer_id = request()->input('benutzer_id');
        $leistung_id = request()->input('leistung_id');
        $kostentraeger_typ = request()->input('kostentraeger_typ');
        $kostentraeger_bez = request()->input('kostentraeger_id');
        $dauer_min = request()->input('dauer_min');
        $leistungs_beschreibung = request()->input('leistungs_beschreibung');
        $hinweis = request()->input('hinweis');
        $beginn = request()->input('beginn');
        $ende = request()->input('ende');

        if (!empty ($datum) && !empty ($zettel_id) && !empty ($benutzer_id) && !empty ($kostentraeger_typ) && !empty ($kostentraeger_bez) && !empty ($beginn) && !empty ($ende)) {
            $d = check_datum($datum);
            if (!$d) {
                throw new \App\Exceptions\MessageException(
                    new \App\Messages\ErrorMessage("DATUMSEINGABE FEHLERHAFT")
                );
            }
            if (empty ($leistung_id) && empty ($leistungs_beschreibung)) {
                throw new \App\Exceptions\MessageException(
                    new \App\Messages\ErrorMessage("Wählen Sie bitte ein Leistung aus, oder geben Sie Ihre Leistungsbeschreibung ein.")
                );
            }
            if (empty ($leistungs_beschreibung) && !empty ($leistung_id)) {
                $ze->pos_deaktivieren($zettel_id, $pos_dat);
                $ze->zettel_pos_speichern($datum, $benutzer_id, $leistung_id, $zettel_id, $dauer_min, $kostentraeger_typ, $kostentraeger_bez, $hinweis, $beginn, $ende);
            }
            if (!empty ($leistungs_beschreibung) && empty ($leistung_id)) {
                $ze->pos_deaktivieren($zettel_id, $pos_dat);
                $ze->leistung_in_katalog($datum, $benutzer_id, $leistungs_beschreibung, $zettel_id, $dauer_min, $kostentraeger_typ, $kostentraeger_bez, $hinweis, $beginn, $ende);
            }

            if (!empty ($leistungs_beschreibung) && !empty ($leistung_id)) {
                throw new \App\Exceptions\MessageException(
                    new \App\Messages\ErrorMessage("Entweder Leistung aussuchen oder Leistungsbeschreibung eintragen")
                );
            }
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("EINGABE UNVOLLSTÄNDIG")
            );
        }

        break;

    case "zettel_zu_beleg" :
        $zettel_id = request()->input('zettel_id');
        $ze = new zeiterfassung ();
        $ze->zettel2beleg($zettel_id);
        break;

    case "zettel2pdf" :
        $zettel_id = request()->input('zettel_id');
        $ze = new zeiterfassung ();
        $ze->zettel2pdf($zettel_id);
        break;

    case "stundennachweise" :
        $ze = new zeiterfassung ();
        $ze->mitarbeiter_auswahl();
        break;

    case "stundennachweise_ex" :
        $ze = new zeiterfassung ();
        $ze->mitarbeiter_auswahl(1);
        break;

    case "nachweisliste" :
        $m_id = request()->input('mitarbeiter_id');
        $ze = new zeiterfassung ();
        $ze->nachweisliste($m_id);
        break;

    case "einheitenliste" :
        $bg = new berlussimo_global ();
        $link = route('web::zeiterfassung::legacy', ['option' => 'einheitenliste'], false);
        break;

    case "zettel_loeschen" :
        if (request()->filled('zettel_id')) {
            $z = new zeiterfassung ();
            $zettel_id = request()->input('zettel_id');
            $benutzer_id = $z->get_userid($zettel_id);
            if ($benutzer_id == Auth::user()->id or Auth::user()->hasAnyRole([
                    \App\Libraries\Role::ROLE_BUCHHALTER, \App\Libraries\Role::ROLE_ADMINISTRATOR
                ])
            ) {
                $z->zettel_loeschen_voll($zettel_id);
                weiterleiten(route('web::zeiterfassung::legacy', ['option' => 'nachweisliste', 'mitarbeiter_id' => $benutzer_id], false));
            } else {
                throw new \App\Exceptions\MessageException(
                    new \App\Messages\ErrorMessage("Sie haben keine Berechtigung fremde Stundennachweise zu löschen, da sie keine Vollrechte haben.")
                );
            }
        } else {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage("Zettel auswählen")
            );
        }
        break;

    case "stunden" :
        $z = new zeiterfassung ();
        $z->form_stunden_anzeigen();
        break;

    case "suchen_std" :
        if (!request()->filled('adatum')) {
            throw new \App\Exceptions\MessageException(
                new \App\Messages\ErrorMessage('Anfangsdatum notwendig.')
            );
        }

        if (!request()->filled('edatum')) {
            $edatum = date("d.m.Y");
        } else {
            $edatum = request()->input('edatum');
        }

        $z = new zeiterfassung ();
        $adatum = request()->input('adatum');
        $benutzer_id = request()->input('benutzer_id');
        $gewerk_id = request()->input('g_id');
        $kos_typ = request()->input('kostentraeger_typ');
        $kos_bez = request()->input('kostentraeger_id'); // bez später zu id machen nicht vergessen!

        $z->stunden_suchen($benutzer_id, $gewerk_id, $kos_typ, $kos_bez, $adatum, $edatum);
        break;
} // end switch