<?php

if (request()->has('daten')) {
    $daten = request()->input('daten');
}
if (request()->has('option')) {
    $option = request()->input('option');
}
if (request()->has('objekt_id')) {
    $objekt_id = request()->input('objekt_id');
}
if (request()->has('haus_id')) {
    $haus_id = request()->input('haus_id');
}

if (isset ($option)) {
    switch ($option) {

        case "objekt" :
            $bg = new berlussimo_global();
            $bg->objekt_auswahl_liste();
            if (session()->has('objekt_id')) {
                leerstand_objekt(session()->get('objekt_id'));
            }
            break;

        case "objekt_pdf" :
            if (session()->has('objekt_id')) {
                $objekt_id = session()->get('objekt_id');
                if (request()->has('monat')) {
                    $monat = request()->input('monat');
                } else {
                    $monat = date("m");
                }

                if (request()->has('jahr')) {
                    $jahr = request()->input('jahr');
                } else {
                    $jahr = date("Y");
                }
                $l = new leerstand ();
                $l->leerstand_objekt_pdf($objekt_id, $monat, $jahr);
            }
            break;

        case "test" :
            $a = new miete ();
            $a->berechnen();
            break;

        default :
            break;

        case "projekt_pdf" :
            if (request()->has('einheit_id')) {
                $l = new leerstand ();
                $l->pdf_projekt(request()->input('einheit_id'));
            } else {
                echo "Einheit wählen";
            }
            break;

        case "form_interessenten" :
            $l = new leerstand ();
            $l->form_interessent();
            break;

        case "interessent_send" :
            echo "<form>";
            if (request()->has('name') && request()->input('anschrift') && request()->input('w_datum')) {
                if (!request()->has('tel') && !request()->has('email')) {
                    die ('Telefonnr oder Email notwendig');
                }
                $name = request()->input('name');
                $anschrift = request()->input('anschrift');
                $tel = request()->input('tel');
                $email = request()->input('email');
                $w_datum = request()->input('w_datum');
                $zimmer = request()->input('zimmer');
                $hinweis = request()->input('hinweis');
                $l = new leerstand ();
                if ($l->interessenten_speichern($name, $anschrift, $tel, $email, $w_datum, $zimmer, $hinweis)) {
                    hinweis_ausgeben("$name gespeichert");
                }
            } else {
                fehlermeldung_ausgeben('Name, Anschrift und Wunschdatum sind notwendig!!!');
            }
            echo "</form>";
            break;

        case "pdf_interessenten" :
            $l = new leerstand ();
            $l->pdf_interessentenliste();
            break;

        case "interessentenliste" :
            $l = new leerstand ();
            $l->interessentenliste();
            break;

        case "expose_pdf" :
            $einheit_id = request()->input('einheit_id');
            if ($einheit_id) {
                $l = new leerstand ();
                $l->pdf_expose($einheit_id);
            } else {
                fehlermeldung_ausgeben('Einheit wählen');
            }
            break;

        case "termine" :
            $l = new leerstand ();
            if (!request()->exists('vergangen')) {
                $l->liste_wohnungen_mit_termin();
            } else {
                $l->liste_wohnungen_mit_termin('<');
            }
            break;

        case "einladungen" :
            $einheit_id = request()->input('einheit_id');
            $l = new leerstand ();
            $l->einladungen($einheit_id);
            break;

        case "form_expose" :
            $einheit_id = request()->input('einheit_id');
            if ($einheit_id) {
                $l = new leerstand ();
                $l->form_exposedaten($einheit_id);
            } else {
                fehlermeldung_ausgeben('Einheit wählen');
            }
            break;

        case "expose_speichern" :
            $l = new leerstand ();
            if (request()->has('einheit_id')
                && request()->has('zimmer')
                && request()->has('balkon')
                && request()->has('expose_bk')
                && request()->has('expose_km')
                && request()->has('expose_hk')
                && request()->has('heizungsart')
                && request()->has('expose_frei')
                && request()->has('besichtigungsdatum')
                && request()->has('uhrzeit')
            ) {
                $l->expose_aktualisieren(request()->input('einheit_id'), request()->input('zimmer'), request()->input('balkon'), request()->input('expose_bk'), request()->input('expose_km'), request()->input('expose_hk'), request()->input('heizungsart'), request()->input('expose_frei'), request()->input('besichtigungsdatum'), request()->input('uhrzeit'));
            } else {
                fehlermeldung_ausgeben("Dateneingabe unvollständig");
            }
            break;

        /* Emails mit PDF-Expose versenden */
        case "sendpdfs" :
            echo "<form>";
            $l = new leerstand ();
            $einheit_id = request()->input('einheit_id');
            if ($einheit_id) {

                $pdf_object = $l->pdf_expose($einheit_id, 1); // Rückgabe PDF-Object
                $b = new buchen ();
                $e = new einheit ();
                $e->get_einheit_info($einheit_id);
                $content = $pdf_object->output();
                $monat = date("m");
                $jahr = date("Y");
                $storage = Storage::disk('fotos');
                if (!$storage->exists("EINHEIT/$e->einheit_kurzname")) {
                    $storage->makeDirectory("EINHEIT/$e->einheit_kurzname");
                }
                $b->save_file("$e->einheit_kurzname" . "-Expose", $storage->fullPath("EINHEIT"), "$e->einheit_kurzname", $content, $monat, $jahr);
                $pfad = $storage->fullPath("EINHEIT/$e->einheit_kurzname/" . "$e->einheit_kurzname" . "-Expose_" . $monat . "_" . $jahr . ".pdf");

                $mails = request()->input('emails');
                $anz = count($mails);
                $files [] = $pfad;
                for ($a = 0; $a < $anz; $a++) {
                    $email = $mails [$a];
                    // $l->mail_att("$email","Einladung zur Wohnungsbesichtigung","Im Anhang ist eine Exposedatei",$anhang);
                    $l->multi_attach_mail($email, $files, 'sivac@berlus.de', 'hausverwaltung.de - Einladung zur Wohnungsbesichtigung', "Wir laden Sie zur Wohnungsbesichtigung ein.\nIn der Anlage finden Sie das Exposé mit dem Besichtigunstermin.\n\nIhre Berlus Hausverwaltung\nFontanestr. 1\n14193 Berlin\nwww.hausverwaltung.de\n\nTel.: 030 89 78 44 77\nFax: 030 89 78 44 79\nEmail: info@berlus.de", 'Berlus HV');
                    echo "Email gesendet an $email<br>";
                }
            } else {
                fehlermeldung_ausgeben('Einheit wählen');
            }
            echo "</form>";
            break;

        case "interessenten_edit" :
            if (request()->has('id')) {
                $l = new leerstand ();
                $id = request()->input('id');
                $l->form_edit_interessent($id);
            } else {
                hinweis_ausgeben("Bitte Namen wählen");
            }
            break;

        case "interessenten_update" :
            echo "<form>";
            if (request()->has('delete')) {
                $id = request()->input('delete');
                $l = new leerstand ();

                if ($l->interessenten_deaktivieren($id)) {
                    hinweis_ausgeben("Interessen gelöscht");
                } else {
                    fehlermeldung_ausgeben("Interessent konnte nicht gelöscht werden!");
                }
            } else {
                if (request()->has('id') && request()->has('name') && request()->has('anschrift') && request()->has('tel') && request()->has('email') && request()->has('einzug') && request()->has('zimmer')) {
                    $id = request()->input('id');
                    $name = request()->input('name');
                    $anschrift = request()->input('anschrift');
                    $tel = request()->input('tel');
                    $email = request()->input('email');
                    $einzug = date_german2mysql(request()->input('einzug'));
                    $zimmer = request()->input('zimmer');
                    $hinweis = request()->input('hinweis');
                    $l = new leerstand ();
                    if ($l->interessenten_updaten($id, $name, $anschrift, $tel, $email, $einzug, $zimmer, $hinweis)) {
                        echo "$name wurde aktualisiert!";
                        weiterleiten_in_sec(route('legacy::leerstand::index', ['option' => 'interessentenliste'], false), 2);
                    } else {
                        fehlermeldung_ausgeben("$name konnte nicht aktualisiert werden.");
                    }
                } else {
                    echo "Bitte alle Datein eingeben!";
                    weiterleiten_in_sec(route('legacy::leerstand::index', ['option' => 'interessentenliste'], false), 3);
                }
            }
            echo "</form>";
            break;

        case "expose_foto_upload" :
            $einheit_id = request()->input('einheit_id');
            if ($einheit_id) {
                $l = new leerstand ();
                $l->form_foto_upload($einheit_id);
            }
            break;

        case "expose_foto_upload_check" :
            $e = new einheit ();
            $e->get_einheit_info(request()->input('einheit_id'));
            define("MAX_SIZE", "10000");
            $errors = 0;

            if (request()->exists('btn_sbm')) {
                // reads the name of the file the user submitted for uploading
                $images = request()->allFiles();
                // if it is not empty
                if (is_array($images)) {
                    $anz = count($images);
                    for ($a = 0; $a < $anz; $a++) {
                        $dateiname = request()->file($images[$a])->getFilename();
                        if (!$dateiname) {
                            $datzahl = $a + 1;
                            die ("$datzahl Datei nicht gewählt!");
                        }
                        $extension = strtolower(getExtension($dateiname));
                        if (($extension != "jpg") && ($extension != "jpeg")) {
                            fehlermeldung_ausgeben("Dateiformat von $dateiname muss JPG oder JPEG sein!");
                            $errors = 1;
                        } else {

                            $size = filesize(request()->file($images[$a])->getSize());

                            if ($size > MAX_SIZE * 1024) {
                                fehlermeldung_ausgeben("Maximal 10000kb!");
                                $errors = 1;
                            }

                            $datzahl = $a + 1;
                            $storage = Storage::disk('fotos');
                            $newname = "EINHEIT/$e->einheit_kurzname/expose$datzahl.$extension";
                            if (!$storage->exists("EINHEIT/$e->einheit_kurzname")) {
                                $storage->makeDirectory("EINHEIT/$e->einheit_kurzname");
                            }
                            $kopiert = $storage->put($newname, file_get_contents(request()->file($images[$a])));
                            if (!$kopiert) {
                                fehlermeldung_ausgeben("Datei $newname konnte nicht kopiert werden");
                                $errors = 1;
                            }
                            if (request()->has('btn_sbm') && !$errors) {
                                echo "<h1>Dateien wurden erfolgreich hochgeladen!</h1>";
                            }
                        }
                    } // end for
                } else {
                    fehlermeldung_ausgeben("Keine Dateien übermitelt");
                }
            }

            break;

        case "sanierung" :
            $bg = new berlussimo_global();
            $bg->objekt_auswahl_liste();
            if (!session()->has('objekt_id')) {
                fehlermeldung_ausgeben("Objekt wählen");
            } else {
                $le = new leerstand ();
                $le->sanierungsliste(session()->get('objekt_id'), 11, 250, 200);
            }
            break;

        case "sanierung_wedding" :
            $le = new leerstand ();
            $le->sanierungsliste(1, 11, 250, 200); // BLOCK II
            $le->sanierungsliste(2, 11, 250, 200); // BLOCK III
            $le->sanierungsliste(3, 11, 250, 200); // BLOCK V

            break;

        case "fotos_upload" :
            if (!request()->has('einheit_id')) {
                fehlermeldung_ausgeben("Einheit wählen");
            } else {
                $l = new leerstand ();
                $l->form_fotos_upload(request()->input('einheit_id'));
                $l->fotos_anzeigen_wohnung(request()->input('einheit_id'), 'ANZEIGE', '10');
            }

            break;

        case "foto_send_ajax" :
            ob_clean();
            if (request()->hasFile('upload_file')) {
                $einheit_id = request()->input('einheit_id_foto');
                $e = new einheit ();
                $e->get_einheit_info($einheit_id);
                $orig_filename = request()->file('upload_file')->getClientOriginalName();
                $orig_file = request()->file('upload_file')->getRealPath();
                $newname = "EINHEIT/$e->einheit_kurzname/ANZEIGE/$orig_filename";
                $storage = Storage::disk('fotos');
                if (!$storage->exists("EINHEIT/$e->einheit_kurzname")) {
                    $storage->makeDirectory("EINHEIT/$e->einheit_kurzname/ANZEIGE");
                }

                /* Bisher alles ok */

                if ($storage->exists("EINHEIT/$e->einheit_kurzname/ANZEIGE")) {
                    if (file_exists($orig_file)) {
                        $thumbnail = new thumbnail ();
                        $thumbnail->create($orig_file);
                        $thumbnail->setQuality(80);
                        $thumbnail->resize("1024");
                        $thumbnail->save($orig_file, true);
                        if (!$storage->put($newname, file_get_contents($orig_file))) {
                            fehlermeldung_ausgeben("Datei $orig_file $newname konnte nicht kopiert werden");
                        } else {
                            echo $storage->fullPath($newname) . " hochgeladen";
                        }
                    }
                }
            } else {
                echo "No file sent ...";
            }
            die();

            break;

        case "foto_loeschen" :
            ob_clean();
            if (request()->has('filename')) {
                $filename = request()->input('filename');
                if (unlink($filename)) {
                    echo "$filename geöscht";
                } else {
                    echo "nicht gelöscht!";
                }
            }
            die ();

            break;

        case "fotos_f_anzeige" :
            if (!request()->has('einheit_id')) {
                fehlermeldung_ausgeben("Einheit wählen");
            } else {
                $le = new leerstand ();
                $le->fotos_anzeigen_wohnung(request()->input('einheit_id'), 'ANZEIGE', '10');
            }
            break;

        case "vermietung_wedding" :
            $le = new leerstand ();
            $le->vermietungsliste(40, 11);
            echo "<br><br><hr><br><br>";
            $le->vermietungsliste(1, 11);
            echo "<br><br><hr><br><br>";
            $le->vermietungsliste(2, 11);
            echo "<br><br><hr><br><br>";
            $le->vermietungsliste(3, 11);

            break;

        case "vermietung" :
            $bg = new berlussimo_global();
            $bg->objekt_auswahl_liste();
            if (session()->has('objekt_id')) {
                $le = new leerstand ();
                $le->vermietungsliste(session()->get('objekt_id'), 11);
            }

            break;

        case "filter_setzen" :
            session()->forget('aktive_filter');

            if (request()->has('Zimmer')) {
                $anz = count(request()->input('Zimmer'));
                for ($a = 0; $a < $anz; $a++) {
                    $wert = request()->input('Zimmer')[$a];
                    session()->push('aktive_filter.zimmer', $wert);
                }
            }

            if (request()->has('Balkon')) {
                $anz = count(request()->input('Balkon'));
                for ($a = 0; $a < $anz; $a++) {
                    $wert = request()->input('Balkon')[$a];
                    session()->push('aktive_filter.balkon', $wert);
                }
            }

            if (request()->has('Heizung')) {
                $anz = count(request()->input('Heizung'));
                for ($a = 0; $a < $anz; $a++) {
                    $wert = request()->input('Heizung')[$a];
                    session()->push('aktive_filter.heizung', $wert);
                }
            }
            $url = parse_url(URL::previous());
            weiterleiten($url['path'].'?'.$url['query']);
            break;

        case "kontrolle_preise" :
            $l = new leerstand ();
            $l->kontrolle_preise();
            break;
    }
}
function leerstand_finden($objekt_id)
{
    $result = DB::select("SELECT OBJEKT_KURZNAME, EINHEIT_ID, EINHEIT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, EINHEIT_QM, EINHEIT_LAGE
FROM `EINHEIT`
RIGHT JOIN (
HAUS, OBJEKT
) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && OBJEKT.OBJEKT_ID='$objekt_id' )
WHERE EINHEIT_AKTUELL='1' && EINHEIT_ID NOT
IN (

SELECT EINHEIT_ID
FROM MIETVERTRAG
WHERE MIETVERTRAG_AKTUELL = '1' && ( MIETVERTRAG_BIS > CURdate( )
OR MIETVERTRAG_BIS = '0000-00-00' )
)
ORDER BY EINHEIT_KURZNAME ASC");
    return $result;
}

function leerstand_finden_monat($objekt_id, $datum)
{
    $result = DB::select("SELECT OBJEKT_KURZNAME, EINHEIT_ID, EINHEIT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, EINHEIT_QM, EINHEIT_LAGE, TYP
FROM `EINHEIT`
RIGHT JOIN (
HAUS, OBJEKT
) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && OBJEKT.OBJEKT_ID='$objekt_id' )
WHERE EINHEIT_AKTUELL='1' && EINHEIT_ID NOT
IN (

SELECT EINHEIT_ID
FROM MIETVERTRAG
WHERE MIETVERTRAG_AKTUELL = '1' && DATE_FORMAT( MIETVERTRAG_VON, '%Y-%m-%d' ) <= '$datum' && ( DATE_FORMAT( MIETVERTRAG_BIS, '%Y-%m-%d' ) >= '$datum'
OR MIETVERTRAG_BIS = '0000-00-00' )
)
GROUP BY EINHEIT_ID ORDER BY EINHEIT_KURZNAME ASC");
    return $result;
}

function leerstand_objekt($objekt_id)
{
    $form = new formular ();
    $form->erstelle_formular("Leerstände", NULL);
    $b = new berlussimo_global ();
    $link = route('legacy::leerstand::index', ['option' => 'objekt', 'objekt_id' => $objekt_id], false);

    if (request()->has('monat')) {
        $monat = request()->input('monat');
    } else {
        $monat = date("m");
    }
    if (request()->has('jahr')) {
        $jahr = request()->input('jahr');
    } else {
        $jahr = date("Y");
    }
    if ($monat && $jahr) {
        $l_tag = letzter_tag_im_monat($monat, $jahr);
        $datum = "$jahr-$monat-$l_tag";
    }

    $b->monate_jahres_links($jahr, $link);

    if (empty ($datum)) {
        $leerstand = leerstand_finden($objekt_id);
    } else {
        $leerstand = leerstand_finden_monat($objekt_id, $datum);
    }
    $monat_name = monat2name($monat);
    echo "<table class=\"sortable\">";
    $link_pdf = "<a href='" . route('legacy::leerstand::index', ['option' => 'objekt_pdf', 'objekt_id' => $objekt_id, 'monat' => $monat, 'jahr' => $jahr]) . "'>PDF-Ansicht</a>";
    echo "<tr><td colspan=\"6\">$link_pdf</td></tr>";
    echo "<tr><td colspan=\"6\">Leerstand $monat_name $jahr</td></tr>";
    echo "</table>";
    echo "<table class=\"sortable\">";
    echo "<tr><th>Objekt</th><th>Einheit</th><th>TYP</th><th>Lage</th><th>Fläche</th><th>Link</th><th>Anschrift</th><th>PDF</th></tr>";

    $anzahl_leer = count($leerstand);
    $summe_qm = 0;
    for ($a = 0; $a < $anzahl_leer; $a++) {
        $einheit_id = $leerstand [$a] ['EINHEIT_ID'];
        $lage = $leerstand [$a] ['EINHEIT_LAGE'];
        $qm = $leerstand [$a] ['EINHEIT_QM'];
        $typ = $leerstand [$a] ['TYP'];
        $link_einheit = "<a href='" . route('legacy::uebersicht::index', ['anzeigen' => 'einheit', 'einheit_id' => $einheit_id]) . "'>" . $leerstand [$a] ['EINHEIT_KURZNAME'] . "</a>";
        $link_projekt_pdf = "<a href='" . route('legacy::leerstand::index', ['option' => 'projekt_pdf', 'einheit_id' => $einheit_id]) . "'><img src=\"images/pdf_light.png\"></a>";
        $link_expose_pdf = "<a href='" . route('legacy::leerstand::index', ['option' => 'expose_pdf', 'einheit_id' => $einheit_id]) . "'><img src=\"images/pdf_dark.png\"></a>";
        $link_expose_eingabe = "<a href='" . route('legacy::leerstand::index', ['option' => 'form_expose', 'einheit_id' => $einheit_id]) . "'>Bearbeiten</a>";
        $link_fotos = "<a href='" . route('legacy::leerstand::index', ['option' => 'expose_foto_upload', 'einheit_id' => $einheit_id]) . "'>Fotos hochladen</a>";
        echo "<tr><td>" . $leerstand [$a] ['OBJEKT_KURZNAME'] . "</td><td>$link_einheit</td><td>$typ</td><td>$lage</td><td>$qm m²</td><td><a href='" . route('legacy::mietvertraege::index', ['mietvertrag_raus' => 'mietvertrag_neu']) . "'>Vermieten</td></td><td>" . $leerstand [$a] ['HAUS_STRASSE'] . " " . $leerstand [$a] ['HAUS_NUMMER'] . "</td><td>$link_projekt_pdf Projekt<br>$link_expose_pdf Expose</td></tr>";
        $summe_qm += $qm;
    }
    echo "<tr><td></td><td></td><td></td><td></td><td>$summe_qm m²</td><td></td><td></td><td></td></tr>";
    echo "</table>";
    $form->ende_formular();
}

/*
 * abgelaufen
 * SELECT OBJEKT_KURZNAME, EINHEIT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER
 * FROM `EINHEIT`
 * RIGHT JOIN (
 * HAUS, OBJEKT
 * ) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID )
 * WHERE EINHEIT_ID NOT
 * IN (
 *
 * SELECT EINHEIT_ID
 * FROM MIETVERTRAG
 * WHERE MIETVERTRAG_AKTUELL = '1' && MIETVERTRAG_BIS < CURdate( )
 * )
 * ORDER BY EINHEIT_KURZNAME ASC
 * LIMIT 0 , 30
 *
 */
