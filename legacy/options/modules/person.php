<?php

if (request()->has('anzeigen')) {
    $anzeigen = request()->input('anzeigen');
}
if (request()->has('submit_person')) {
    $submit_person = request()->input('submit_person');
}
if (request()->has('submit_person_direkt')) {
    $submit_person_direkt = request()->input('submit_person_direkt');
}
if (request()->has('person_loeschen')) {
    $person_loeschen = request()->input('person_loeschen');
}
if (request()->has('person_aendern')) {
    $person_aendern = request()->input('person_aendern');
}
if (request()->has('submit_person_aendern')) {
    $submit_person_aendern = request()->input('submit_person_aendern');
}
switch ($anzeigen) {

    case "alle_personen" :
        erstelle_abschnitt("Liste aller vorhandenen Personen");
        $p = new personen ();
        $p->personen_liste_alle();
        ende_abschnitt();
        break;

    case "person_erfassen_alt" :
        personen_liste(); // seitlicher block
        $form = new mietkonto ();
        $form->erstelle_formular("Neue Person erfassen", NULL);

        iframe_start();
        if (!isset ($submit_person)) {
            person_erfassen_form();
        }
        if (isset ($submit_person)) {
            check_fields();
        }
        if (isset ($submit_person_direkt)) {
            person_in_db_eintragen_direkt();
        }
        if (request()->exists('person_speichern')) {
            person_in_db_eintragen();
        }
        iframe_end();
        $form->ende_formular();
        break;

    /* Neues Personeneingabeformular mit Geschlechteingabe in die Details */
    case "person_erfassen" :
        $p = new personen ();
        $p->form_person_erfassen();
        break;

    /* Prüfen der Eingabe im Formular */
    case "person_erfassen_check" :
        $f = new formular ();
        $f->erstelle_formular('Überprüfen', '');
        $f->fieldset("Daten überprüfen", 'p_pruefen');
        $geb_dat = request()->input('geburtsdatum');
        $nachname = request()->input('nachname');
        $vorname = request()->input('vorname');
        $geschlecht = request()->input('geschlecht');
        $telefon = request()->input('telefon');
        $handy = request()->input('handy');
        $email = request()->input('email');
        if (empty ($nachname) or empty ($vorname) or empty ($geb_dat)) {
            fehlermeldung_ausgeben("<br>Name, Vorname oder Geburtsdatum unvollständig");
        } else {

            echo "Eingegebene Daten überprüfen<hr>";
            echo "Nachname:$nachname<br>";
            echo "Vorname: $vorname<br>";
            echo "Geschlecht: $geschlecht<br>";
            echo "Geburtsdatum: $geb_dat<br>";
            echo "Telefon: $telefon<br>";
            echo "Handy: $handy<br><br>";
            echo "Email: $email<br><br>";
            $p = new personen ();
            if ($p->person_exists($vorname, $nachname, $geb_dat)) {
                echo "$nachname $vorname geb. am $geb_dat existiert bereits, trotzdem speichern???";
            }
            $f->hidden_feld("nachname", "$nachname");
            $f->hidden_feld("vorname", "$vorname");
            $f->hidden_feld("geburtsdatum", "$geb_dat");
            $f->hidden_feld("geschlecht", "$geschlecht");
            $f->hidden_feld("telefon", "$telefon");
            $f->hidden_feld("handy", "$handy");
            $f->hidden_feld("email", "$email");
            $f->hidden_feld("anzeigen", "person_erfassen_save");
            $f->send_button("submit_kostenkonto", "Speichern");
        }
        $f->fieldset_ende();
        $f->ende_formular();
        break;

    /* Neue Person nach Prüfung speichern */
    case "person_erfassen_save" :
        $f = new formular ();
        $f->fieldset("Person/Mieter speichern", 'p_save');
        $geb_dat = request()->input('geburtsdatum');
        $nachname = request()->input('nachname');
        $vorname = request()->input('vorname');
        $geschlecht = request()->input('geschlecht');
        $telefon = request()->input('telefon');
        $handy = request()->input('handy');
        $email = request()->input('email');
        if (empty ($nachname) or empty ($vorname) or empty ($geb_dat)) {
            fehlermeldung_ausgeben("<br>Name, Vorname oder Geburtsdatum unvollständig");
        } else {
            echo "Eingegebene Daten überprüfen<hr>";
            echo "Nachname:$nachname<br>";
            echo "Vorname: $vorname<br>";
            echo "Geburtsdatum: $geb_dat<br>";
            echo "Telefon: $telefon<br>";
            echo "Handy: $handy<br><br>";
            echo "Email: $email<br><br>";
            $p = new personen ();
            $p->save_person($nachname, $vorname, $geb_dat, $geschlecht, $telefon, $handy, $email);
        }

        $f->fieldset_ende();
        break;

    case "alle_mieter" :
        $form = new mietkonto ();
        $form->erstelle_formular("Liste aller Mieter", NULL);
        iframe_start();
        // mieternamen_liste_alle();
        alle_mieter_arr(); // funct hier
        iframe_end();
        $form->ende_formular();
        break;

    case "person_loeschen" :
        $form = new mietkonto ();
        $form->erstelle_formular("Person löschen", NULL);
        iframe_start();
        if (request()->has('person_dat')) {
            // person_loeschen($_REQUEST["person_dat"]);
            hinweis_ausgeben("Löschfunktion deaktiviert!!!");
            weiterleiten("javascript:history.back()");
        }
        iframe_end();
        $form->ende_formular();
        break;

    case "person_aendern" :
        $form = new mietkonto ();
        $form->erstelle_formular("Person ändern", NULL);
        if (request()->has('person_id') && !isset ($submit_person_aendern) && !request()->exists('person_definitiv_speichern')) {
            person_aendern_from(request()->input('person_id'));
        }
        if (isset ($submit_person_aendern)) {
            check_fields_nach_aenderung(); // prüfen und eintragen
        }
        if (request()->exists('person_definitiv_speichern')) {
            check_fields_nach_aenderung(); // Änderung anzeigen prüfen und eintragen
        }
        $form->ende_formular();
        break;

    case "person_hinweis" :
        $p = new personen ();
        $p->get_person_hinweis();
        break;

    case "person_anschrift" :
        $p = new personen ();
        /* Verzugs und Zustelladressen */
        $p->get_person_anschrift();
        break;
}

function check_fields()
{
    foreach (request()->request->all() as $key => $value) {

        if (($key == "person_nachname") && empty ($value)) {
            fehlermeldung_ausgeben("Bitte tragen Sie einen Familiennamen ein!");
            backlink();
            $myerror = true;
            break;
        } elseif (($key == "person_vorname") && empty ($value)) {
            fehlermeldung_ausgeben("Bitte tragen Sie einen Vornamen ein!");
            backlink();
            $myerror = true;
            break;
        } elseif (($key == "person_geburtstag") && empty ($value)) {
            fehlermeldung_ausgeben("Bitte tragen Sie einen Geburtstag ein!");
            backlink();
            $myerror = true;
            break;
        } elseif (($key == "person_geburtstag") && isset ($value)) {
            $datum = request()->input('person_geburtstag');

            $tmp = explode(".", $datum);
            if (checkdate($tmp [1], $tmp [0], $tmp [2])) {
            } else {
                fehlermeldung_ausgeben("Falsches Datumsformat, bitte überprüfen!");
                backlink();
                $myerror = true;
                break;
            }
        }
    } // end for
    if (!isset ($myerror)) {
        erstelle_formular(NULL, NULL); // name, action
        echo "<tr><td><h1>Folgende Daten wurden übermittelt:\n</h1></td></tr>\n";
        echo "<tr><td><h2>Personendaten: $objekt_kurzname</h2></td></tr>\n";
        echo "<tr><td>";
        warnung_ausgeben("Sind Sie sicher, daß Sie die Person " . request()->input('person_nachname') . " " . request()->input('person_vorname') . " geb. am " . request()->input('person_geburtstag') . " speichern wollen?");
        echo "</td></tr>";
        erstelle_hiddenfeld("person_nachname", request()->input('person_nachname'));
        erstelle_hiddenfeld("person_vorname", request()->input('person_vorname'));
        erstelle_hiddenfeld("person_geburtstag", request()->input('person_geburtstag'));
        erstelle_submit_button("person_speichern", "Speichern"); // name, wert
        ende_formular();
    }
}

function alle_mieter_arr()
{
    $abfrage = "SELECT  DISTINCT MIETVERTRAG.EINHEIT_ID, MIETVERTRAG.MIETVERTRAG_ID, PERSON_MIETVERTRAG.PERSON_MIETVERTRAG_MIETVERTRAG_ID, PERSON_MIETVERTRAG.PERSON_MIETVERTRAG_PERSON_ID";
    $abfrage .= " FROM `MIETVERTRAG`, PERSON_MIETVERTRAG WHERE ((MIETVERTRAG_BIS = '0000-00-00')OR (MIETVERTRAG_BIS >'2008-06-10'))";
    $abfrage .= " && ( MIETVERTRAG.MIETVERTRAG_ID = PERSON_MIETVERTRAG.PERSON_MIETVERTRAG_MIETVERTRAG_ID) ";

    $result = DB::select($abfrage);

    echo "<pre>";
    print_r($result);
    echo "</pre>";
}

function check_fields_nach_aenderung()
{
    foreach (request()->request->all() as $key => $value) {

        if (($key == "person_nachname") && empty ($value)) {
            fehlermeldung_ausgeben("Bitte tragen Sie einen Familiennamen ein!");
            backlink();
            $myerror = true;
            break;
        } elseif (($key == "person_vorname") && empty ($value)) {
            fehlermeldung_ausgeben("Bitte tragen Sie einen Vornamen ein!");
            backlink();
            $myerror = true;
            break;
        } elseif (($key == "person_geburtstag") && empty ($value)) {
            fehlermeldung_ausgeben("Bitte tragen Sie einen Geburtstag ein!");
            backlink();
            $myerror = true;
            break;
        } elseif (($key == "person_geburtstag") && isset ($value)) {
            $datum = request()->input('person_geburtstag');

            $tmp = explode(".", $datum);
            if (checkdate($tmp [1], $tmp [0], $tmp [2])) {
            } else {
                fehlermeldung_ausgeben("Falsches Datumsformat, bitte überprüfen!");
                backlink();
                $myerror = true;
                break;
            }
        }
    } // end for
    if (!isset ($myerror)) {
        if (!request()->exists('person_definitiv_speichern')) {
            echo "<h5>Sind Sie sicher, daß Sie die Person " . request()->input('person_nachname') . " " . request()->input('person_vorname') . " geb. am " . request()->input('person_geburtstag') . " ändern wollen?</h5>";
            erstelle_hiddenfeld("person_nachname", request()->input('person_nachname'));
            erstelle_hiddenfeld("person_vorname", request()->input('person_vorname'));
            erstelle_hiddenfeld("person_geburtstag", request()->input('person_geburtstag'));
            erstelle_submit_button("person_definitiv_speichern", "Speichern"); // name, wert
        }
    }
    if (request()->has('person_definitiv_speichern')) {
        person_aendern_in_db(request()->input('person_id'));
        hinweis_ausgeben("Person: " . request()->input('person_nachname') . " " . request()->input('person_vorname') . " wurde geändert !");
        hinweis_ausgeben("Sie werden weitergeleitet.");
        weiterleiten(route('legacy::personen::index', ['anzeigen' => 'alle_personen'], false));
    }
}

function personen_liste()
{
    $db_abfrage = "SELECT PERSON_NACHNAME, PERSON_VORNAME, PERSON_GEBURTSTAG FROM PERSON WHERE PERSON_AKTUELL='1' ORDER BY PERSON_NACHNAME, PERSON_VORNAME ASC";
    $result = DB::select($db_abfrage);
    echo "<div class=\"tabelle_personen\"><table>\n";
    echo "<tr class=\"feldernamen\"><td colspan=3>Personenliste</td></tr>\n";
    echo "<tr class=\"feldernamen\"><td>Nachname</td><td>Vorname</td><td>Geb. am</td></tr>\n";
    $counter = 0;
    foreach ($result as $row) {
        $counter++;
        if ($counter == 1) {
            echo "<tr class=\"zeile1\"><td>$row[PERSON_NACHNAME]</td><td>$row[PERSON_VORNAME]</td><td>$row[PERSON_GEBURTSTAG]</td></tr>\n";
        }
        if ($counter == 2) {
            echo "<tr class=\"zeile2\"><td>$row[PERSON_NACHNAME]</td><td>$row[PERSON_VORNAME]</td><td>$row[PERSON_GEBURTSTAG]</td></tr>\n";
            $counter = 0;
        }
    }
    echo "</table></div>";
}