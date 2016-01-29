<?php
/**
 * BERLUSSIMO
 *
 * Hausverwaltungssoftware
 *
 *
 * @copyright    Copyright (c) 2010, Berlus GmbH, Fontanestr. 1, 14193 Berlin
 * @link         http://www.berlus.de
 * @author       Sanel Sivac & Wolfgang Wehrheim
 * @contact		 software(@)berlus.de
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 *
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/ajax/ajax_info.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 *
 */

define("ROOT_PATH", dirname(__FILE__));
define("BERLUS_PATH", '');
//wegen include bei AJAX anders
define("HAUPT_PATH", dirname(__DIR__));
define("PROG_PATH", dirname(__FILE__));
define("DOC_ROOT", $_SERVER['DOCUMENT_ROOT']);
#echo HAUPT_PATH;
#echo BERLUS_PATH;

/*neu*/
/*KONFIG*/
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/classes/config.inc.php");
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/includes/config.php");
/*KLASSEN*/
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/pdfclass/class.ezpdf.php");
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/classes/class_bpdf.php");
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/classes/class_person.php");
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/classes/class_details.php");
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/classes/class_weg.php");
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/classes/class_sepa.php");
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/classes/berlussimo_class.php");
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/includes/allgemeine_funktionen.php");
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/classes/class_sepa.php");
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/classes/class_buchen.php");
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/classes/class_mietvertrag.php");
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/classes/mietzeit_class.php");
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/classes/mietkonto_class.php");

include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/classes/class_formular.php");
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/classes/class_benutzer.php");
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/classes/class_mietentwicklung.php");
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/classes/class_geldkonten.php");
include_once (HAUPT_PATH . '/' . BERLUS_PATH . "/classes/class_kautionen.php");

session_start();
header('Content-Type: text/plain; charset=ISO-8859-1');
//wichtig für die Umlaute in Dropdownfeldern
#ini_set('display_errors','On');
/*Allgemeine Funktionsdatei laden*/
include_once ("../includes/allgemeine_funktionen.php");
include_once ("../classes/config.inc.php");
include_once ("../classes/berlussimo_class.php");
include_once ("../classes/class_rechnungen.php");
include_once ("../classes/class_partners.php");
include_once ("../classes/class_mietvertrag.php");
include_once ("../classes/class_details.php");
include_once ("../classes/class_person.php");
include_once ("../classes/class_weg.php");
include_once ("../classes/class_buchen.php");
include_once ("../classes/class_formular.php");
include_once ("../classes/class_werkzeug.php");
include_once ("../classes/class_mietvertrag.php");

connectToBase();
$option = $_REQUEST["option"];
/*Optionsschalter*/
switch($option) {

    /*default:
     echo "<script>";
     echo "alert('Fehler')";
     echo "</script>";
     break;*/

    case "wb_hinzufuegen" :
        $beleg_id = $_REQUEST['beleg_id'];
        $pos = $_REQUEST['pos'];
        $rr = new rechnungen();
        $pos_info = $rr -> get_position($beleg_id, $pos);
        $art_nr = $pos_info['ARTIKEL_NR'];
        $menge = $pos_info['MENGE'];
        $wb = new werkzeug();
        $menge_erl = $wb -> get_anzahl_werkzeuge($art_nr, $beleg_id);
        $menge_hinzu = $menge - $menge_erl;
        for ($a = 0; $a < $menge_hinzu; $a++) {
            $l_id = last_id2('WERKZEUGE', 'ID') + 1;
            $db_abfrage = "INSERT INTO WERKZEUGE VALUES(NULL, '$l_id', '$beleg_id', '$pos', '$art_nr', '1', '', NULL, NULL, '1')";
            $result = mysql_query($db_abfrage) or die(mysql_error());
        }

        break;

    case "update_rechnung_rabatt" :
        $rabatt = nummer_komma2punkt($_REQUEST['prozent']);
        $belegnr = $_REQUEST['belegnr'];
        if (empty($rabatt) or empty($belegnr)) {
            die('Kein Beleg oder Rabattprozente');
        }
        $rr = new rechnungen();
        $pos_arr = $rr -> rechnungs_positionen_arr($belegnr);
        if (is_array($pos_arr)) {
            $anz = count($pos_arr);
            for ($a = 0; $a < $anz; $a++) {
                $pos = $pos_arr[$a]['POSITION'];
                $preis = $pos_arr[$a]['PREIS'];
                $menge = $pos_arr[$a]['MENGE'];
                #$rabatt = $pos_arr[$a]['RABATT_SATZ'];
                $gpreis = ($menge * $preis / 100) * (100 - $rabatt);

                /*Update Rechnung Positionen*/
                $rabatt = nummer_punkt2komma($rabatt);
                $db_abfrage = "UPDATE RECHNUNGEN_POSITIONEN SET GESAMT_NETTO='$gpreis', RABATT_SATZ='$rabatt' WHERE POSITION='$pos' && BELEG_NR='$belegnr' && AKTUELL='1'";
                $resultat = mysql_query($db_abfrage);

            }
        } else {
            echo "error:Keine Position verändert, da keine Pos im Beleg vorhanden!";
        }

        break;

    case "update_rechnung_skonti" :
        $skonto = $_REQUEST['prozent'];
        $belegnr = $_REQUEST['belegnr'];
        if (empty($skonto) or empty($belegnr)) {
            die('Kein Beleg oder Skontiprozente!');
        }
        $rr = new rechnungen();
        $pos_arr = $rr -> rechnungs_positionen_arr($belegnr);
        if (is_array($pos_arr)) {
            $anz = count($pos_arr);
            for ($a = 0; $a < $anz; $a++) {
                $pos = $pos_arr[$a]['POSITION'];
                $preis = $pos_arr[$a]['PREIS'];
                $menge = $pos_arr[$a]['MENGE'];
                $rabatt = $pos_arr[$a]['RABATT_SATZ'];
                $gpreis = number_format(($menge * $preis / 100) * (100 - $rabatt), 2);

                /*Update Rechnung Positionen*/
                $db_abfrage = "UPDATE RECHNUNGEN_POSITIONEN SET GESAMT_NETTO='$gpreis', SKONTO='$skonto' WHERE POSITION='$pos' && BELEG_NR='$belegnr' && AKTUELL='1'";
                $resultat = mysql_query($db_abfrage);

            }
        } else {
            echo "error:Keine Position verändert, da keine Pos im Beleg vorhanden!";
        }

        break;

    case "register_var" :
        $var = $_REQUEST['var'];
        $value = $_REQUEST['value'];
        $_SESSION[$var] = $value;
        break;

    case "kostenkonto" :
        $konto_id = $_REQUEST["konto_id"];
        #$db_abfrage = "SELECT BEZEICHNUNG FROM KONTENRAHMEN_KONTEN WHERE KONTO='$konto_id' ORDER BY KONTENRAHMEN_KONTEN_DAT DESC LIMIT 0,1";
        $db_abfrage = "SELECT KONTENRAHMEN_KONTEN.BEZEICHNUNG, KONTENRAHMEN_GRUPPEN.BEZEICHNUNG AS GRUPPE, KONTENRAHMEN_KONTOARTEN.KONTOART
FROM KONTENRAHMEN_KONTEN
RIGHT JOIN (
KONTENRAHMEN_GRUPPEN, KONTENRAHMEN_KONTOARTEN
) ON ( KONTENRAHMEN_KONTEN.GRUPPE = KONTENRAHMEN_GRUPPEN.KONTENRAHMEN_GRUPPEN_ID && KONTENRAHMEN_KONTOARTEN.KONTENRAHMEN_KONTOART_ID = KONTENRAHMEN_KONTEN.KONTO_ART )
WHERE KONTO = '$konto_id'
ORDER BY KONTENRAHMEN_KONTEN_DAT DESC
LIMIT 0 , 1";

        $resultat = mysql_query($db_abfrage) or die(mysql_error());
        while (list($BEZEICHNUNG, $GRUPPE, $KONTOART) = mysql_fetch_row($resultat))
            echo "$BEZEICHNUNG|$GRUPPE|$KONTOART";
        break;

    case "finde_partner" :
        #header("Content-Type: text/html; charset=ISO-8859-1");
        #header('Content-Type: text/plain; charset=UTF-8'); //wichtig f�r die Umlaute in Dropdownfeldern
        if (isset($_REQUEST['suchstring']) && !empty($_REQUEST['suchstring'])) {
            $suchstring = mysql_real_escape_string($_REQUEST['suchstring']);
            if (strlen($suchstring) > 2) {
                #echo "SANEL $suchstring";
                #ob_clean();
                $db_abfrage = "SELECT PARTNER_NAME, PARTNER_ID, STRASSE, NUMMER, PLZ, ORT, LAND FROM PARTNER_LIEFERANT WHERE AKTUELL='1' && PARTNER_NAME LIKE '%$suchstring%' ORDER BY PARTNER_NAME ASC";
                #echo $db_abfrage;
                #die();
                $resultat = mysql_query($db_abfrage) or die(mysql_error());
                $numrows = mysql_numrows($resultat);
                if ($numrows) {
                    echo "<h2>GEFUNDEN!!!</h2>";
                    echo "<table>";
                    $z = 0;
                    while (list($PARTNER_NAME, $PARTNER_ID, $STRASSE, $NUMMER, $PLZ, $ORT) = mysql_fetch_row($resultat)) {
                        $z++;
                        $PARTNER_NAME1 = str_replace('<br>', ' ', $PARTNER_NAME);
                        echo "<tr class=\"zeile$z\"><td>$PARTNER_NAME1</td><td>$STRASSE</td><td>$NUMMER</td><td>$PLZ</td><td>$ORT</td></tr>";
                        if ($z == 2) {
                            $z = 0;
                        }
                    }
                    echo "</table>";
                }
            }
        }
        break;

    case "list_kostentraeger" :
        ob_clean();
        $typ = $_REQUEST["typ"];
        if ($typ == 'Objekt') {
            if (!isset($_SESSION['geldkonto_id'])) {
                $db_abfrage = "SELECT OBJEKT_KURZNAME, OBJEKT_ID FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC";
                $resultat = mysql_query($db_abfrage) or die(mysql_error());
                #echo "<meta content=\"text/html; charset=ISO-8859-1\" http-equiv=\"content-type\">";
                while (list($OBJEKT_KURZNAME, $OBJEKT_ID) = mysql_fetch_row($resultat)) {
                    echo "$OBJEKT_KURZNAME*$OBJEKT_ID*|";
                }
            } else {
                #check_zuweisung_kos_typ($geldkonto_id, $kos_typ, $kos_id)
                $db_abfrage = "SELECT OBJEKT_KURZNAME, OBJEKT_ID FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC";
                $resultat = mysql_query($db_abfrage) or die(mysql_error());
                #echo "<meta content=\"text/html; charset=ISO-8859-1\" http-equiv=\"content-type\">";
                while (list($OBJEKT_KURZNAME, $OBJEKT_ID) = mysql_fetch_row($resultat)) {
                    $gk = new gk;
                    if ($gk -> check_zuweisung_kos_typ($_SESSION['geldkonto_id'], 'Objekt', $OBJEKT_ID)) {
                        echo "$OBJEKT_KURZNAME*$OBJEKT_ID*|";
                    }
                }
            }
        }

        if ($typ == 'Wirtschaftseinheit') {
            ob_clean();
            $db_abfrage = "SELECT LTRIM(RTRIM(W_NAME)) FROM WIRT_EINHEITEN WHERE AKTUELL='1' ORDER BY W_NAME ASC";
            $resultat = mysql_query($db_abfrage) or die(mysql_error());
            #echo "<meta content=\"text/html; charset=ISO-8859-1\" http-equiv=\"content-type\">";
            while (list($W_NAME) = mysql_fetch_row($resultat)) {
                echo "$W_NAME|";
            }
        }

        /*if($typ == 'Haus'){
         ob_clean();
         $db_abfrage = "SELECT HAUS_ID, HAUS_STRASSE, HAUS_NUMMER, OBJEKT_ID FROM HAUS WHERE HAUS_AKTUELL='1' ORDER BY HAUS_STRASSE,  0+HAUS_NUMMER, OBJEKT_ID ASC";
         echo $db_abfrage;
         $resultat = mysql_query($db_abfrage) or
         die(mysql_error());

         while (list ($HAUS_ID, $HAUS_STRASSE, $HAUS_NUMMER, $OBJEKT_ID) = mysql_fetch_row($resultat))
         #$hh = new haus();
         #$hh->get_haus_info($HAUS_ID);
         #print_r($hh);
         echo "$HAUS_STRASSE $HAUS_NUMMER|$hh->objekt_name";
         }*/
        if ($typ == 'Haus') {
            ob_clean();
            $db_abfrage = "SELECT HAUS_ID, HAUS_STRASSE, HAUS_NUMMER, OBJEKT_ID FROM HAUS WHERE HAUS_AKTUELL='1' ORDER BY HAUS_STRASSE,  0+HAUS_NUMMER, OBJEKT_ID ASC";
            #echo $db_abfrage;
            $result = mysql_query($db_abfrage) or die(mysql_error());
            $numrows = mysql_numrows($result);
            if ($numrows) {
                while ($row = mysql_fetch_assoc($result)) {
                    $h_id = $row['HAUS_ID'];
                    $h_str = $row['HAUS_STRASSE'];
                    $h_nr = $row['HAUS_NUMMER'];
                    $hh = new haus();
                    $hh -> get_haus_info($h_id);
                    if (!isset($_SESSION['geldkonto_id'])) {
                        echo "$h_str $h_nr*$h_id*$hh->objekt_name|";
                    } else {
                        $gk = new gk;
                        if ($gk -> check_zuweisung_kos_typ($_SESSION['geldkonto_id'], 'Objekt', $hh -> objekt_id)) {
                            echo "$h_str $h_nr*$h_id*$hh->objekt_name|";
                        }
                    }
                }
            }
        }

        if ($typ == 'EinheitOK') {
            ob_clean();
            $db_abfrage = "SELECT EINHEIT_KURZNAME, EINHEIT_ID FROM EINHEIT WHERE EINHEIT_AKTUELL='1' ORDER BY EINHEIT_KURZNAME ASC";
            $resultat = mysql_query($db_abfrage) or die(mysql_error());
            while (list($EINHEIT_KURZNAME, $EINHEIT_ID) = mysql_fetch_row($resultat))
                if (!isset($_SESSION['geldkonto_id'])) {
                    echo "$EINHEIT_KURZNAME|";
                } else {
                    $eee = new einheit();
                    $eee -> get_einheit_info($EINHEIT_ID);
                    $gk = new gk;
                    if ($gk -> check_zuweisung_kos_typ($_SESSION['geldkonto_id'], 'Objekt', $eee -> objekt_id)) {
                        echo "$EINHEIT_KURZNAME|";
                    }
                }

        }

        if ($typ == 'Einheit') {
            ob_clean();
            $db_abfrage = "SELECT EINHEIT_ID, EINHEIT_KURZNAME, HAUS.OBJEKT_ID AS OBJEKT_ID
FROM  `EINHEIT` 
RIGHT JOIN (
HAUS, OBJEKT
) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID ) 
WHERE EINHEIT_AKTUELL =  '1'
GROUP BY EINHEIT_ID
ORDER BY LPAD( EINHEIT_KURZNAME, LENGTH( EINHEIT_KURZNAME ) ,  '1' ) ASC ";

            $resultat = mysql_query($db_abfrage) or die(mysql_error());
            while (list($EINHEIT_ID, $EINHEIT_KURZNAME, $OBJEKT_ID) = mysql_fetch_row($resultat))
                if (!isset($_SESSION['geldkonto_id'])) {
                    echo "$EINHEIT_KURZNAME*$EINHEIT_ID*|";
                } else {
                    $gk = new gk;
                    if ($gk -> check_zuweisung_kos_typ($_SESSION['geldkonto_id'], 'Objekt', $OBJEKT_ID)) {
                        echo "$EINHEIT_KURZNAME*$EINHEIT_ID*|";
                    }
                }

        }

        if ($typ == 'Partner') {
            ob_clean();
            $db_abfrage = "SELECT PARTNER_NAME, PARTNER_ID FROM PARTNER_LIEFERANT WHERE AKTUELL='1' ORDER BY PARTNER_NAME ASC";
            $resultat = mysql_query($db_abfrage) or die(mysql_error());
            while (list($PARTNER_NAME, $PARTNER_ID) = mysql_fetch_row($resultat)) {
                $PARTNER_NAME1 = str_replace('<br>', ' ', $PARTNER_NAME);
                echo "$PARTNER_NAME1*$PARTNER_ID*|";
            }
        }
        /*NEU SCHNELL ENDE 2014*/
        if ($typ == 'Mietvertrag') {
            ob_clean();

            $gk_arr_objekt = get_objekt_arr_gk($_SESSION['geldkonto_id']);
            if (is_array($gk_arr_objekt)) {

                $db_abfrage = "SELECT  HAUS.OBJEKT_ID, OBJEKT_KURZNAME, MIETVERTRAG.EINHEIT_ID, EINHEIT_KURZNAME, MIETVERTRAG_ID FROM `EINHEIT` RIGHT JOIN (HAUS, OBJEKT, MIETVERTRAG) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && EINHEIT.EINHEIT_ID=MIETVERTRAG.EINHEIT_ID)
WHERE  HAUS_AKTUELL='1' && EINHEIT_AKTUELL='1' && OBJEKT_AKTUELL='1' && MIETVERTRAG_AKTUELL='1' ";

                $anz_gk = count($gk_arr_objekt);
                for ($go = 0; $go < $anz_gk; $go++) {
                    $oo_id = $gk_arr_objekt[$go];
                    $db_abfrage .= "&& OBJEKT_ID=$oo_id ";
                }

                $db_abfrage .= "GROUP BY MIETVERTRAG_ID ORDER BY LPAD(EINHEIT_KURZNAME, LENGTH(EINHEIT_KURZNAME), '1') ASC";

            } else {

                $db_abfrage = "SELECT  HAUS.OBJEKT_ID, OBJEKT_KURZNAME, MIETVERTRAG.EINHEIT_ID, EINHEIT_KURZNAME, MIETVERTRAG_ID FROM `EINHEIT` RIGHT JOIN (HAUS, OBJEKT, MIETVERTRAG) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && EINHEIT.EINHEIT_ID=MIETVERTRAG.EINHEIT_ID)
WHERE  HAUS_AKTUELL='1' && EINHEIT_AKTUELL='1' && OBJEKT_AKTUELL='1' && MIETVERTRAG_AKTUELL='1' GROUP BY MIETVERTRAG_ID ORDER BY LPAD(EINHEIT_KURZNAME, LENGTH(EINHEIT_KURZNAME), '1') ASC";
            }
            $result = mysql_query($db_abfrage) or die(mysql_error());
            while ($row = mysql_fetch_assoc($result)) {
                $mv_id = $row['MIETVERTRAG_ID'];
                $objekt_id = $row['OBJEKT_ID'];
                $mv = new mietvertraege;
                $mv -> get_mietvertrag_infos_aktuell($mv_id);

                if (!isset($_SESSION['geldkonto_id'])) {
                    if ($mv -> mietvertrag_aktuell == 1) {
                        echo "$mv->einheit_kurzname*$mv_id*$mv->personen_name_string|";
                    } else {
                        echo "ALTMIETER:$mv->einheit_kurzname*$mv_id*$mv->personen_name_string|";
                    }
                } else {
                    #$eee = new einheit();
                    #$eee->get_einheit_info($mv->einheit_id);
                    $gk = new gk;
                    if ($gk -> check_zuweisung_kos_typ($_SESSION['geldkonto_id'], 'Objekt', $objekt_id)) {
                        if ($mv -> mietvertrag_aktuell == 1) {
                            echo "$mv->einheit_kurzname*$mv_id*$mv->personen_name_string|";
                        } else {
                            echo "$mv->einheit_kurzname ###ALTMIETER###:*$mv_id*$mv->personen_name_string|";
                        }
                    }

                }

            }
        }

        if ($typ == 'GELDKONTO') {
            ob_clean();
            $db_abfrage = "SELECT KONTO_ID, BEZEICHNUNG  FROM `GELD_KONTEN`  WHERE AKTUELL='1' ORDER BY BEZEICHNUNG ASC";
            $resultat = mysql_query($db_abfrage) or die(mysql_error());
            while (list($KONTO_ID, $BEZEICHNUNG) = mysql_fetch_row($resultat)) {
                echo "$BEZEICHNUNG*$KONTO_ID*|";
            }
        }

        if ($typ == 'Lager') {
            ob_clean();
            $db_abfrage = "SELECT LAGER_ID, LAGER_NAME  FROM `LAGER`  WHERE AKTUELL='1' ORDER BY LAGER_NAME ASC";
            $resultat = mysql_query($db_abfrage) or die(mysql_error());
            while (list($LAGER_ID, $LAGER_NAME) = mysql_fetch_row($resultat)) {
                #echo "$LAGER_NAME|";
                echo "$LAGER_NAME*$LAGER_ID*|";
            }
        }

        if ($typ == 'Baustelle_ext') {
            ob_clean();
            $db_abfrage = "SELECT ID, BEZ  FROM `BAUSTELLEN_EXT`  WHERE AKTUELL='1' && AKTIV='1' ORDER BY BEZ ASC";
            $resultat = mysql_query($db_abfrage) or die(mysql_error());
            while (list($ID, $BEZ) = mysql_fetch_row($resultat)) {
                #echo "$BEZ|";
                echo "$BEZ*$ID*|";
            }
        }

        /*if($typ == 'Eigentuemer'){
         ob_clean();
         $db_abfrage = "SELECT ID, EINHEIT_ID FROM `WEG_MITEIGENTUEMER`  WHERE AKTUELL='1'";
         $resultat = mysql_query($db_abfrage) or
         die(mysql_error());
         while (list ( $ID, $EINHEIT_ID) = mysql_fetch_row($resultat)){
         $weg = new weg;
         $eig_bez[] = $weg->get_eigentumer_id_infos2($ID).'*'. $ID;
         }
         asort($eig_bez);
         $anz = count($eig_bez);
         if($anz>0){
         for($a=0;$a<$anz;$a++){
         $eig_bez1 = $eig_bez[$a];
         echo "$eig_bez1|";
         }
         }
         }*/

        if ($typ == 'Eigentuemer') {
            ob_clean();
            #$db_abfrage = "SELECT ID, EINHEIT_ID FROM `WEG_MITEIGENTUEMER`  WHERE AKTUELL='1'";
            $db_abfrage = "SELECT ID, WEG_MITEIGENTUEMER.EINHEIT_ID, EINHEIT_KURZNAME FROM `WEG_MITEIGENTUEMER` , EINHEIT WHERE EINHEIT_AKTUELL = '1' && AKTUELL = '1' && EINHEIT.EINHEIT_ID = WEG_MITEIGENTUEMER.EINHEIT_ID GROUP BY ID ORDER BY EINHEIT_KURZNAME ASC";
            $result = mysql_query($db_abfrage) or die(mysql_error());
            while ($row = mysql_fetch_assoc($result)) {
                $weg = new weg;
                #$eig_bez[] = $weg->get_eigentumer_id_infos2($ID).'*'. $ID;
                $ID = $row['ID'];
                $einheit_id = $row['EINHEIT_ID'];
                $weg -> get_eigentuemer_namen($row['ID']);
                //$weg->eigentuemer_name_str
                #$e = new einheit();
                #$e->get_einheit_info($EINHEIT_ID);
                $einheit_kn = $row['EINHEIT_KURZNAME'];

                if (!isset($_SESSION['geldkonto_id'])) {
                    echo "$einheit_kn*$ID*$weg->eigentuemer_name_str|";
                    #echo "$mv->einheit_kurzname*$mv_id*$mv->personen_name_string|";
                } else {
                    $eee = new einheit();
                    $eee -> get_einheit_info($einheit_id);
                    $gk = new gk;
                    if ($gk -> check_zuweisung_kos_typ($_SESSION['geldkonto_id'], 'Objekt', $eee -> objekt_id)) {
                        #echo "$einheit_kn*$weg->eigentuemer_name_str iiii*".$row['ID']."|";
                        echo "$einheit_kn*$ID*$weg->eigentuemer_name_str|";
                    }
                }

                #echo "$einheit_kn $bezxx|";
            }

        }

        if ($typ == 'ALLE') {
            ob_clean();
            echo "ALLE|";
        }

        if ($typ == 'Benutzer') {
            ob_clean();
            $db_abfrage = "SELECT benutzer_id, benutzername  FROM `BENUTZER` ORDER BY benutzername ASC";
            $resultat = mysql_query($db_abfrage) or die(mysql_error());
            while (list($benutzer_id, $benutzername) = mysql_fetch_row($resultat)) {
                echo "$benutzername*$benutzer_id*|";
            }
        }

        break;

    case "list_kostentraeger2" :
        $typ = $_REQUEST["typ"];
        if ($typ == 'Objekt') {
            $db_abfrage = "SELECT OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' ORDER BY OBJEKT_KURZNAME ASC";
            $resultat = mysql_query($db_abfrage) or die(mysql_error());
            #echo "<meta content=\"text/html; charset=ISO-8859-1\" http-equiv=\"content-type\">";
            while (list($OBJEKT_KURZNAME) = mysql_fetch_row($resultat)) {
                echo "$OBJEKT_KURZNAME|";
            }
        }

        if ($typ == 'Wirtschaftseinheit') {
            $db_abfrage = "SELECT W_NAME FROM WIRT_EINHEITEN WHERE AKTUELL='1' ORDER BY W_NAME ASC";
            $resultat = mysql_query($db_abfrage) or die(mysql_error());
            #echo "<meta content=\"text/html; charset=ISO-8859-1\" http-equiv=\"content-type\">";
            while (list($W_NAME) = mysql_fetch_row($resultat)) {
                echo "$W_NAME|";
            }
        }

        if ($typ == 'Haus') {
            $db_abfrage = "SELECT HAUS_STRASSE, HAUS_NUMMER FROM HAUS WHERE HAUS_AKTUELL='1' ORDER BY HAUS_STRASSE,  0+HAUS_NUMMER, OBJEKT_ID ASC";
            $resultat = mysql_query($db_abfrage) or die(mysql_error());

            while (list($HAUS_STRASSE, $HAUS_NUMMER) = mysql_fetch_row($resultat))
                echo "$HAUS_STRASSE $HAUS_NUMMER|";
        }

        if ($typ == 'Einheit') {
            $db_abfrage = "SELECT EINHEIT_KURZNAME FROM EINHEIT WHERE EINHEIT_AKTUELL='1' ORDER BY EINHEIT_KURZNAME ASC";
            $resultat = mysql_query($db_abfrage) or die(mysql_error());
            while (list($EINHEIT_KURZNAME) = mysql_fetch_row($resultat))
                echo "$EINHEIT_KURZNAME|";
        }

        if ($typ == 'Partner') {
            $db_abfrage = "SELECT PARTNER_NAME FROM PARTNER_LIEFERANT WHERE AKTUELL='1' ORDER BY PARTNER_NAME ASC";
            $resultat = mysql_query($db_abfrage) or die(mysql_error());
            while (list($PARTNER_NAME) = mysql_fetch_row($resultat)) {
                $PARTNER_NAME1 = str_replace('<br>', '', $PARTNER_NAME);
                echo "$PARTNER_NAME1|";
            }
        }

        if ($typ == 'Mietvertrag') {
            ob_clean();
            //alt OK	$db_abfrage = "SELECT MIETVERTRAG_ID, EINHEIT_KURZNAME  FROM `MIETVERTRAG` JOIN EINHEIT ON (MIETVERTRAG.EINHEIT_ID=EINHEIT.EINHEIT_ID) WHERE MIETVERTRAG_AKTUELL='1' ORDER BY EINHEIT_KURZNAME ASC";

            $db_abfrage = "SELECT OBJEKT_KURZNAME, MIETVERTRAG.EINHEIT_ID, EINHEIT_KURZNAME, MIETVERTRAG_ID FROM `EINHEIT` RIGHT JOIN (HAUS, OBJEKT, MIETVERTRAG) ON ( EINHEIT.HAUS_ID = HAUS.HAUS_ID && HAUS.OBJEKT_ID = OBJEKT.OBJEKT_ID && EINHEIT.EINHEIT_ID=MIETVERTRAG.EINHEIT_ID)
WHERE  HAUS_AKTUELL='1' && EINHEIT_AKTUELL='1' && OBJEKT_AKTUELL='1' && MIETVERTRAG_AKTUELL='1' GROUP BY MIETVERTRAG_ID ORDER BY LPAD(EINHEIT_KURZNAME, LENGTH(EINHEIT_KURZNAME), '1') ASC";
            $result = mysql_query($db_abfrage) or die(mysql_error());
            while ($row = mysql_fetch_assoc($result)) {
                $mv_id = $row['MIETVERTRAG_ID'];
                $mv = new mietvertraege;
                $mv -> get_mietvertrag_infos_aktuell($mv_id);
                #HAUS#### echo "$h_str $h_nr*$h_id*$hh->objekt_name|";
                echo "$mv->einheit_kurzname*$mv_id*$mv->personen_name_string|";
            }
        }

        /*if($typ == 'Mietvertrag'){
         $db_abfrage = "SELECT MIETVERTRAG_ID, EINHEIT_KURZNAME  FROM `MIETVERTRAG` JOIN EINHEIT ON (MIETVERTRAG.EINHEIT_ID=EINHEIT.EINHEIT_ID) WHERE MIETVERTRAG_AKTUELL='1' ORDER BY EINHEIT_KURZNAME ASC";
         $resultat = mysql_query($db_abfrage) or
         die(mysql_error());
         while (list ( $MIETVERTRAG_ID, $EINHEIT_KURZNAME) = mysql_fetch_row($resultat)){
         $mv = new mietvertraege;
         $mv->get_mietvertrag_infos_aktuell($MIETVERTRAG_ID);

         echo " $EINHEIT_KURZNAME * $mv->personen_name_string * $MIETVERTRAG_ID|";
         }
         }*/

        if ($typ == 'GELDKONTO') {
            $db_abfrage = "SELECT KONTO_ID, BEZEICHNUNG  FROM `GELD_KONTEN`  WHERE AKTUELL='1' ORDER BY BEZEICHNUNG ASC";
            $resultat = mysql_query($db_abfrage) or die(mysql_error());
            while (list($KONTO_ID, $BEZEICHNUNG) = mysql_fetch_row($resultat)) {
                echo "$BEZEICHNUNG|";
            }
        }

        if ($typ == 'Lager') {
            $db_abfrage = "SELECT LAGER_ID, LAGER_NAME  FROM `LAGER`  WHERE AKTUELL='1' ORDER BY LAGER_NAME ASC";
            $resultat = mysql_query($db_abfrage) or die(mysql_error());
            while (list($LAGER_ID, $LAGER_NAME) = mysql_fetch_row($resultat)) {
                echo "$LAGER_NAME|";
            }
        }

        if ($typ == 'Baustelle_ext') {
            $db_abfrage = "SELECT ID, BEZ  FROM `BAUSTELLEN_EXT`  WHERE AKTUELL='1' ORDER BY BEZ ASC";
            $resultat = mysql_query($db_abfrage) or die(mysql_error());
            while (list($ID, $BEZ) = mysql_fetch_row($resultat)) {
                echo "$BEZ|";
            }
        }

        /*
         if($typ == 'Eigentuemer'){
         $db_abfrage = "SELECT ID, EINHEIT_ID FROM `WEG_MITEIGENTUEMER`  WHERE AKTUELL='1'";
         $resultat = mysql_query($db_abfrage) or
         die(mysql_error());
         while (list ( $ID, $EINHEIT_ID) = mysql_fetch_row($resultat)){
         $weg = new weg;
         $eig_bez[] = $weg->get_eigentumer_id_infos2($ID).'*'. $ID;
         }
         asort($eig_bez);
         $anz = count($eig_bez);
         if($anz>0){
         for($a=0;$a<$anz;$a++){
         $eig_bez1 = $eig_bez[$a];
         echo "$eig_bez1|";
         }
         }

         }*/

        if ($typ == 'Eigentuemer') {
            ob_clean();
            #$db_abfrage = "SELECT ID, EINHEIT_ID FROM `WEG_MITEIGENTUEMER`  WHERE AKTUELL='1'";
            $db_abfrage = "SELECT ID, WEG_MITEIGENTUEMER.EINHEIT_ID, EINHEIT_KURZNAME FROM `WEG_MITEIGENTUEMER` , EINHEIT WHERE EINHEIT_AKTUELL = '1' && AKTUELL = '1' && EINHEIT.EINHEIT_ID = WEG_MITEIGENTUEMER.EINHEIT_ID GROUP BY ID ORDER BY  EINHEIT_KURZNAME ASC";
            $result = mysql_query($db_abfrage) or die(mysql_error());
            while ($row = mysql_fetch_assoc($result)) {
                $weg = new weg;
                #$eig_bez[] = $weg->get_eigentumer_id_infos2($ID).'*'. $ID;
                $ID = $row['ID'];
                $weg -> get_eigentuemer_namen($row['ID']);
                //$weg->eigentuemer_name_str
                #$e = new einheit();
                #$e->get_einheit_info($EINHEIT_ID);
                $einheit_kn = $row['EINHEIT_KURZNAME'];
                echo "$einheit_kn $weg->eigentuemer_name_str*$ID|";
                #echo "$einheit_kn $bezxx|";
            }

        }

        if ($typ == 'ALLE') {
            echo "ALLE|";
        }

        break;

    case "get_iban_bic" :
        #echo "IBAN NO HERE";
        $kto = utf8_decode($_REQUEST["kto"]);
        $blz = utf8_decode($_REQUEST["blz"]);
        #echo "$kto $blz";
        $sep = new sepa();
        $sep -> get_iban_bic($kto, $blz);
        echo "$sep->IBAN1|$sep->BIC|$sep->BANKNAME_K";
        break;

    case "check_artikels" :
        #echo "$artikel_nr";
        #$artikel_nr= utf8_decode($_REQUEST["artikel_nr"]);
        #ini_set('display_errors','On');
        #error_reporting(E_ALL|E_STRICT);

        $artikel_nr = $_REQUEST["artikel_nr"];

        $lieferant_id = $_REQUEST["lieferant_id"];
        $db_abfrage = "SELECT ARTIKEL_NR, BEZEICHNUNG, LISTENPREIS, RABATT_SATZ, EINHEIT, MWST_SATZ, SKONTO FROM POSITIONEN_KATALOG WHERE AKTUELL='1' && ART_LIEFERANT='$lieferant_id' && ARTIKEL_NR='$artikel_nr' ORDER BY KATALOG_DAT DESC LIMIT 0,1";
        ob_clean();
        $resultat = mysql_query($db_abfrage) or die(mysql_error());
        $numrows = mysql_numrows($resultat);
        if (!$numrows) {
            echo '';
            die();
        }
        while (list($ARTIKEL_NR, $BEZEICHNUNG, $LISTENPREIS, $RABATT_SATZ, $EINHEIT, $MWST_SATZ, $SKONTO) = mysql_fetch_row($resultat))
            echo "$ARTIKEL_NR|$BEZEICHNUNG|$LISTENPREIS|$RABATT_SATZ|$EINHEIT|$MWST_SATZ|$SKONTO";
        break;

    case "display_positionen" :
        $belegnr = $_REQUEST['belegnr'];
        $result = mysql_query("SELECT * FROM RECHNUNGEN_POSITIONEN WHERE BELEG_NR='$belegnr' && AKTUELL='1' ORDER BY POSITION ASC");
        $numrows = mysql_numrows($result);
        if ($numrows < 1) {
            echo '';
        } else {
            while ($row = mysql_fetch_assoc($result))
                $rechnungs_positionen_arr[] = $row;

            #$this->anzahl_positionen = $numrows;
            header('Content-Type: text/html; charset=ISO-8859-1');
            echo "<table id=\"positionen_tab\">\n";
            echo "<tr>";
            echo "<th scopr=\"col\">Ändern</th>";
            echo "<th scopr=\"col\">Pos</th>";
            echo "<th scopr=\"col\">Art.</th>";
            echo "<th scopr=\"col\">Bezeichnung</th>";
            echo "<th scopr=\"col\">Menge</th>";
            echo "<th scopr=\"col\">Einheit</th>";
            echo "<th scopr=\"col\">LP</th>";
            echo "<th scopr=\"col\">EP</th>";
            echo "<th scopr=\"col\">Rabatt</th>";
            echo "<th scopr=\"col\">MWSt</th>";
            echo "<th scopr=\"col\">Skonto</th>";
            echo "<th scopr=\"col\">Netto</th>";
            echo "</tr>";
            $g_netto = 0;
            $g_mwst = 0;
            $g_brutto = 0;
            for ($a = 0; $a < count($rechnungs_positionen_arr); $a++) {

                $position = $rechnungs_positionen_arr[$a]['POSITION'];
                $menge = $rechnungs_positionen_arr[$a]['MENGE'];
                $einzel_preis = $rechnungs_positionen_arr[$a]['PREIS'];
                $mwst_satz = $rechnungs_positionen_arr[$a]['MWST_SATZ'];
                $rabatt = $rechnungs_positionen_arr[$a]['RABATT_SATZ'];
                $gesamt_netto = $rechnungs_positionen_arr[$a]['GESAMT_NETTO'];
                $gesamt_netto_ausgabe = nummer_punkt2komma($gesamt_netto, 2, '.', '');
                $art_lieferant = $rechnungs_positionen_arr[$a]['ART_LIEFERANT'];
                $artikel_nr = $rechnungs_positionen_arr[$a]['ARTIKEL_NR'];
                $pos_skonto = $rechnungs_positionen_arr[$a]['SKONTO'];

                /*Infos aus Katalog zu Artikelnr*/
                $artikel_info_arr = artikel_info($art_lieferant, $rechnungs_positionen_arr[$a]['ARTIKEL_NR']);
                for ($i = 0; $i < count($artikel_info_arr); $i++) {
                    if (!empty($artikel_info_arr[$i]['BEZEICHNUNG'])) {
                        $bezeichnung = $artikel_info_arr[$i]['BEZEICHNUNG'];
                        $listenpreis = $artikel_info_arr[$i]['LISTENPREIS'];
                        $v_einheit = $artikel_info_arr[$i]['EINHEIT'];
                    } else {
                        $bezeichnung = 'Unbekannt';
                        $listenpreis = '0,00';
                    }
                    $menge = nummer_punkt2komma($menge);
                    $einzel_preis = nummer_punkt2komma($einzel_preis);
                    $listenpreis = nummer_punkt2komma($listenpreis);
                    #$rabatt = nummer_punkt2komma($rabatt);
                    $gesamt_preis = nummer_punkt2komma($gesamt_preis);
                    $aendern_link = "<a href=\"?daten=rechnungen&option=position_aendern&pos=$position&belegnr=$belegnr\">Ändern</a>";
                    $loeschen_link = "<a href=\"?daten=rechnungen&option=position_loeschen&pos=$position&belegnr=$belegnr\">Löschen</a>";
                    echo "<tr><td valign=top>$aendern_link $loeschen_link</td><td valign=top>$position.</td><td valign=top>$artikel_nr</td><td valign=top>$bezeichnung</td><td align=right valign=top>$menge</td><td>$v_einheit</td><td align=right valign=top>$listenpreis €</td><td align=right valign=top>$einzel_preis €</td><td align=right valign=top>$rabatt %</td><td align=right valign=top>$mwst_satz %</td><td align=right valign=top>$pos_skonto %</td><td align=right valign=top>$gesamt_netto_ausgabe €</td></tr>\n";
                    $g_netto = $g_netto + $gesamt_netto;
                    $g_mwst1 = ($gesamt_netto / 100) * (100 + $mwst_satz);
                    $g_mwst2 = $g_mwst1 - $gesamt_netto;

                    $g_brutto = $g_brutto + $g_mwst1;
                    $g_mwst = $g_mwst + $g_mwst2;
                }//end for 2

            }//end for 1

            $g_netto = sprintf("%0.2f", $g_netto);
            $g_mwst = sprintf("%0.2f", $g_mwst);
            $g_brutto = sprintf("%0.2f", $g_brutto);
            $g_netto = nummer_punkt2komma($g_netto, 2, '.', '');
            $g_mwst = nummer_punkt2komma($g_mwst, 2, '.', '');
            $g_brutto = nummer_punkt2komma($g_brutto, 2, '.', '');

            echo "<tr><td valign=top colspan=12><hr></td></tr>\n";
            echo "<tr><td></td><td valign=top></td><td valign=top></td><td valign=top></td><td valign=top></td><td align=right valign=top></td><td align=right valign=top></td><td align=right valign=top></td><td align=right valign=top></td><td valign=top></td><td align=right valign=top>ERRECHNET</td><td align=right valign=top>Netto: $g_netto €</td></tr>\n";
            echo "<tr><td></td><td valign=top></td><td valign=top></td><td valign=top></td><td valign=top></td><td valign=top></td><td align=right valign=top></td><td align=right valign=top></td><td align=right valign=top></td><td align=right valign=top></td><td align=right valign=top></td><td align=right valign=top>Mwst: $g_mwst €</td></tr>\n";
            echo "<tr><td valign=top></td><td valign=top></td><td valign=top></td><td valign=top></td><td valign=top></td><td align=right valign=top></td><td align=right valign=top></td><td align=right valign=top></td><td valign=top></td><td align=right valign=top></td><td align=right valign=top></td><td align=right valign=top>Brutto: $g_brutto €</td></tr>\n";

            /*$ges_brutto = ($ges_netto / 100) * (100+$mwst_satz);
             $ges_brutto = number_format($ges_brutto,2, ",", "");
             */
            echo "</table>";
        }
        break;

    case "insert_position" :
        #header("Content-Type: text/html; charset=ISO-8859-1");
        #header('Content-Type: text/plain; charset=UTF-8'); //wichtig für die Umlaute in Dropdownfeldern
        $belegnr = $_REQUEST["belegnr"];
        $position = $_REQUEST["pos"];
        $artikel_nr = utf8_decode($_REQUEST["artikel_nr"]);
        #$bez = $_REQUEST["bez"];
        $bez = trim(addslashes(htmlspecialchars(rawurldecode(utf8_decode($_REQUEST["bez"])))));
        #$artikel_nr= utf8_decode($artikel_nr);
        #$bez= utf8_decode($bez);
        $lieferant_id = utf8_decode($_REQUEST["lieferant_id"]);
        $menge = utf8_decode($_REQUEST["menge"]);
        $einheit = utf8_decode($_REQUEST["einheit"]);
        $preis = utf8_decode($_REQUEST["listenpreis"]);
        $rabatt = utf8_decode($_REQUEST["rabatt"]);
        $pos_mwst = utf8_decode($_REQUEST["pos_mwst"]);
        $pos_skonto = utf8_decode($_REQUEST["pos_skonto"]);
        $g_netto = utf8_decode($_REQUEST["g_netto"]);

        $r = new rechnung;
        #$r->rechnung_grunddaten_holen($belegnr);
        $letzte_rech_pos_id = $r -> get_last_rechnung_pos_id();
        $letzte_rech_pos_id = $letzte_rech_pos_id + 1;

        if (preg_match("/,/i", "$pos_skonto")) {
            $pos_skonto = nummer_komma2punkt($pos_skonto);
        }

        if (preg_match("/,/i", "$pos_mwst")) {
            $pos_mwst = nummer_komma2punkt($pos_mwst);
        }

        if (preg_match("/,/i", "$preis")) {
            $preis = nummer_komma2punkt($preis);
        }

        if (preg_match("/,/i", "$g_netto")) {
            $g_netto = nummer_komma2punkt($g_netto);
        }

        if (preg_match("/,/i", "$menge")) {
            $menge = nummer_komma2punkt($menge);
        }

        $lieferant_id = mysql_real_escape_string($lieferant_id);
        $artikel_nr = mysql_real_escape_string($artikel_nr);
        $preis = mysql_real_escape_string($preis);
        $rabatt = mysql_real_escape_string($rabatt);
        $pos_skonto = mysql_real_escape_string($pos_skonto);
        #$bez = addslashes($bez);

        $db_abfrage = "select * from POSITIONEN_KATALOG where ART_LIEFERANT='$lieferant_id' && ARTIKEL_NR='$artikel_nr' && AKTUELL='1' && LISTENPREIS='$preis' && RABATT_SATZ='$rabatt' && SKONTO='$pos_skonto' && BEZEICHNUNG='$bez'";
        $result = mysql_query($db_abfrage) or die(mysql_error());
        $numrows = mysql_numrows($result);

        if (!$numrows) {
            #$r->artikel_leistung_speichern($lieferant_id, $bez, $preis, $rabatt, $einheit, $pos_mwst);
            $r -> artikel_leistung_mit_artikelnr_speichern($lieferant_id, $bez, $preis, $artikel_nr, $rabatt, $einheit, $pos_mwst, $pos_skonto);
        }

        $r2 = new rechnungen;
        $last_pos = $r2 -> rechnung_last_position($belegnr);
        $last_pos = $last_pos + 1;

        $db_abfrage = "INSERT INTO RECHNUNGEN_POSITIONEN VALUES (NULL, '$letzte_rech_pos_id', '$last_pos', '$belegnr','$belegnr','$lieferant_id','$artikel_nr', '$menge','$preis','$pos_mwst', '$rabatt', '$pos_skonto', '$g_netto','1')";
        #echo  "$letzte_rech_pos_id $pos $belegnr $lieferant_id";
        $resultat = mysql_query($db_abfrage) or die(mysql_error());
        /*Protokollieren*/
        #	$last_dat = mysql_insert_id();
        #protokollieren('RECHNUNGEN_POSITIONEN', $last_dat, '0');
        #echo "Position $pos wurde gespeichert <br>\n";

        /*Nach Einf�gen einer Position den Skontogesamtbetrag updaten*/
        #$r2->update_skontobetrag($belegnr);
        ob_clean();
        break;

    case "aendern_position" :
        header('Content-Type: text/plain; charset=UFT-8');
        //wichtig für die Umlaute in Dropdownfeldern
        $belegnr = $_REQUEST["belegnr"];
        $pos = $_REQUEST["pos"];
        $artikel_nr = $_REQUEST["artikel_nr"];
        $bez = $_REQUEST["bez"];
        #$artikel_nr= utf8_decode($artikel_nr);
        #$bez= utf8_decode($bez);

        $lieferant_id = $_REQUEST["lieferant_id"];
        $menge = $_REQUEST["menge"];
        $einheit = $_REQUEST["einheit"];
        $preis = $_REQUEST["listenpreis"];
        $rabatt = $_REQUEST["rabatt"];
        $pos_mwst = $_REQUEST["pos_mwst"];
        $g_netto = $_REQUEST["g_netto"];
        $pos_skonto = $_REQUEST["pos_skonto"];

        $r = new rechnung;
        #$r->rechnung_grunddaten_holen($belegnr);
        $letzte_rech_pos_id = $r -> get_last_rechnung_pos_id();
        $letzte_rech_pos_id = $letzte_rech_pos_id + 1;

        /*Abfragen ob Artikel im Katalog "so" vorhanden */
        $db_abfrage = "select * from POSITIONEN_KATALOG where ART_LIEFERANT='$lieferant_id' && ARTIKEL_NR='$artikel_nr' && AKTUELL='1' && LISTENPREIS='$preis' && RABATT_SATZ='$rabatt' && BEZEICHNUNG='$bez' && EINHEIT='$einheit' && MWST_SATZ='$pos_mwst' && SKONTO='$pos_skonto'";

        echo $db_abfrage;
        $result = mysql_query($db_abfrage) or die(mysql_error());
        $numrows = mysql_numrows($result);

        /*Falls nicht so vorhanden, artikel speichern*/
        if (!$numrows) {
            $r -> artikel_leistung_mit_artikelnr_speichern($lieferant_id, $bez, $preis, $artikel_nr, $rabatt, $einheit, $pos_mwst, $pos_skonto);
            /*Falls vorhanden, deaktivieren und als neuen Datensatz speichern*/
        } else {
            /*Deaktiviert weil falsch
             $db_abfrage = "UPDATE POSITIONEN_KATALOG SET AKTUELL='0' where ART_LIEFERANT='$lieferant_id' && ARTIKEL_NR='$artikel_nr' && AKTUELL='1' && LISTENPREIS='$preis' && RABATT_SATZ='$rabatt' && BEZEICHNUNG='$bez' && EINHEIT='$einheit' && MWST_SATZ='$pos_mwst' && SKONTO='$pos_skonto'";
             $result = mysql_query($db_abfrage) or
             die(mysql_error());
             */
        }

        $r2 = new rechnungen;
        /*Alte Position aus der Rechnung deaktivieren*/
        $r -> position_deaktivieren($pos, $belegnr);
        /*Psition neu speichern*/
        $db_abfrage = "INSERT INTO RECHNUNGEN_POSITIONEN VALUES (NULL, '$letzte_rech_pos_id', '$pos', '$belegnr', '$belegnr', '$lieferant_id','$artikel_nr', '$menge','$preis','$pos_mwst', '$rabatt', '$pos_skonto', '$g_netto','1')";
        #echo  "$letzte_rech_pos_id $pos $belegnr $lieferant_id";
        $resultat = mysql_query($db_abfrage) or die(mysql_error());
        /*Protokollieren*/
        #	$last_dat = mysql_insert_id();
        #protokollieren('RECHNUNGEN_POSITIONEN', $last_dat, '0');
        #echo "Position $pos wurde gespeichert <br>\n";
        #echo "POSITION WURDE GEÄNDERT";
        #weiterleiten_in_sec("?daten=rechnungen&option=positionen_erfassen&belegnr=$belegnr", 2);
        break;

    case "get_kontierungs_infos" :
        $r = new rechnungen;
        $belegnr = $_REQUEST["belegnr"];
        $r -> rechnung_grunddaten_holen($belegnr);
        $buchungsbetrag = $_REQUEST["buchungsbetrag"];
        //netto, brutto, skonto, keine summe oder betrag
        $result = mysql_query("SELECT KONTIERUNG_ID, sum( GESAMT_SUMME - ( GESAMT_SUMME /100 * RABATT_SATZ ) ) AS NETTO, sum( (
(
GESAMT_SUMME - ( GESAMT_SUMME /100 * RABATT_SATZ ) ) /100
) * ( 100 + MWST_SATZ )
) AS BRUTTO, sum( (
(
(
GESAMT_SUMME - ( GESAMT_SUMME /100 * RABATT_SATZ ) ) /100
) * ( 100 + MWST_SATZ ) /100
) * ( 100 - SKONTO )
) AS SKONTO_BETRAG, MENGE, POSITION, KONTENRAHMEN_KONTO, KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID
FROM `KONTIERUNG_POSITIONEN`
WHERE BELEG_NR = '$belegnr' && AKTUELL = '1'
GROUP BY KOSTENTRAEGER_TYP, KOSTENTRAEGER_ID, KONTENRAHMEN_KONTO") or die(mysql_error());

        $numrows = mysql_numrows($result);
        if ($numrows >= 1) {
            $str = "<b>Kontierung:</b><br>";
            $g_betrag = 0;
            while ($row = mysql_fetch_assoc($result)) {

                $netto = $row['NETTO'];
                $brutto = $row['BRUTTO'];
                $skonto = $row['SKONTO_BETRAG'];
                $netto_a = nummer_punkt2komma($row['NETTO']);
                $brutto_a = nummer_punkt2komma($row['BRUTTO']);
                $skonto_a = nummer_punkt2komma($row['SKONTO_BETRAG']);

                $kostenkonto = $row['KONTENRAHMEN_KONTO'];
                $k_typ = $row['KOSTENTRAEGER_TYP'];
                $k_id = $row['KOSTENTRAEGER_ID'];
                $k_bez = get_kostentraeger_infos($k_typ, $k_id);

                if ($buchungsbetrag == 'Nettobetrag') {
                    $str = $str . "$netto_a €| $kostenkonto | $k_bez<br>";
                    $g_betrag = $g_betrag + $netto;
                }
                if ($buchungsbetrag == 'Bruttobetrag') {
                    $str = $str . "$brutto_a €| $kostenkonto | $k_bez<br>";
                    $g_betrag = $g_betrag + $brutto;
                }
                if ($buchungsbetrag == 'Skontobetrag') {
                    $str = $str . "$skonto_a €| $kostenkonto | $k_bez<br>";
                    $g_betrag = $g_betrag + $skonto;
                }

            }
            $g_betrag = nummer_punkt2komma($g_betrag);
            $str = $str . "<br><b>Gesamtbetrag $g_betrag €</b>";
            echo $str;
        }//end if $numrows
        else {
            echo "Keine Kontierung";
        }

        break;

    case "get_kostentraeger_name" :
        $id = $_REQUEST["id"];
        $typ = $_REQUEST["typ"];
        $kostentraeger_bez = get_kostentraeger_infos($typ, $id);
        echo $kostentraeger_bez;

        /*
         $partner_id = $_REQUEST["id"];
         $p = new partners;
         $p->get_partner_name($partner_id);
         echo $p->partner_name;
         */
        break;

    case "get_detail_inhalt" :
        $tab = $_REQUEST["tab"];
        $id = $_REQUEST["id"];
        $det_name = $_REQUEST["det_name"];
        $d = new detail;
        $inhalt = $d -> finde_detail_inhalt($tab, $id, $det_name);
        if ($inhalt) {
            echo $inhalt;
        } else {
            echo "Detail $det_name in $tab fehlt!";
        }
        break;

    case "get_mv_infos" :
        $mv_id = $_REQUEST["mv_id"];
        $mvs = new mietvertraege();
        $mvs -> get_mietvertrag_infos_aktuell($mv_id);
        echo "<pre>";
        #print_r($mvs);
        echo "Einheit-TYP: $mvs->einheit_typ<br>";
        echo "Einheit: $mvs->einheit_kurzname<br>";
        echo "Anschrift: $mvs->haus_strasse $mvs->haus_nr, $mvs->haus_plz $mvs->haus_stadt<br>";
        echo "Mieter:<br>$mvs->personen_name_string_u<br>";
        echo "Einzug: $mvs->mietvertrag_von_d<br>";
        echo "Auszug: $mvs->mietvertrag_bis_d<br>";
        echo "</pre>";
        break;

    case "get_gk_infos" :
        $gk_id = $_REQUEST["gk_id"];
        $var = $_REQUEST["var"];
        $geld_konto_info = new geldkonto_info;
        $geld_konto_info -> geld_konto_details($gk_id);
        #echo "<pre>";
        #print_r($geld_konto_info);
        #echo "</pre>";
        $value = eval('return $geld_konto_info->' . $var . ';');
        echo $value;
        break;

    case "get_detail_ukats" :
        $kat_id = $_REQUEST["kat_id"];
        if (isset($kat_id)) {
            $abfrage = "SELECT UNTERKATEGORIE_NAME FROM `DETAIL_UNTERKATEGORIEN` WHERE `KATEGORIE_ID` = '$kat_id' AND `AKTUELL` = '1' ORDER BY UNTERKATEGORIE_NAME ASC";

            $resultat = mysql_query($abfrage) or die(mysql_error());
            $numrows = mysql_numrows($resultat);
            if ($numrows) {
                while (list($UNTERKATEGORIE_NAME) = mysql_fetch_row($resultat)) {
                    echo "$UNTERKATEGORIE_NAME;";
                }
            }
        } else {
            echo "AJAX FEHLER 2004";
        }
        break;

    case "autovervollst" :
        $string = $_REQUEST["string"];
        $string = utf8_decode($string);
        $lieferant_id = $_REQUEST["l_id"];
        if (isset($string) && strlen($string) > 0) {
            $abfrage = "SELECT LTRIM(RTRIM(ARTIKEL_NR)), BEZEICHNUNG, LISTENPREIS FROM `POSITIONEN_KATALOG`
WHERE `ART_LIEFERANT` = '$lieferant_id' AND (`ARTIKEL_NR` LIKE '$string%' OR `BEZEICHNUNG` LIKE '$string%') GROUP BY ARTIKEL_NR ORDER BY ARTIKEL_NR ASC LIMIT 0,5";

            $resultat = mysql_query($abfrage) or die(mysql_error());
            $numrows = mysql_numrows($resultat);
            if ($numrows) {
                while (list($ARTIKEL_NR, $BEZEICHNUNG, $LISTENPREIS) = mysql_fetch_row($resultat)) {
                    echo "$ARTIKEL_NR??$BEZEICHNUNG??$LISTENPREIS||";
                    #$f = "$ARTIKEL_NR??$BEZEICHNUNG??$LISTENPREIS||";
                    #print(utf8_encode($f));
                }
            }
        } else {
            echo "AJAX FEHLER 20041";
        }
        break;

    case "autovervollst2" :
        $string = $_REQUEST["string"];
        $string = utf8_decode($string);
        $lieferant_id = $_REQUEST["l_id"];
        //aktueller partner d.h. eigener preis
        if (isset($string) && strlen($string) > 0) {
            $abfrage = "SELECT * FROM (SELECT ARTIKEL_NR, BEZEICHNUNG, LISTENPREIS, ART_LIEFERANT, FORMAT((LISTENPREIS - (LISTENPREIS/100)* RABATT_SATZ)/100*(100+MWST_SATZ),2) AS BRUTTO FROM `POSITIONEN_KATALOG`
WHERE (`ARTIKEL_NR` LIKE '$string%' OR `BEZEICHNUNG` LIKE '$string%') ORDER BY ART_LIEFERANT ASC, LISTENPREIS DESC, KATALOG_ID DESC) AS AB1 GROUP BY ART_LIEFERANT, ARTIKEL_NR ORDER BY ARTIKEL_NR ASC";

            $resultat = mysql_query($abfrage) or die(mysql_error());
            $numrows = mysql_numrows($resultat);
            if ($numrows) {
                while (list($ARTIKEL_NR, $BEZEICHNUNG, $LISTENPREIS, $ART_LIEFERANT, $BRUTTO) = mysql_fetch_row($resultat)) {
                    $p = new partners();
                    $p -> get_partner_name($ART_LIEFERANT);
                    echo "$ARTIKEL_NR??$BEZEICHNUNG??$BRUTTO??$p->partner_name??$ART_LIEFERANT||";
                    #$f = "$ARTIKEL_NR??$BEZEICHNUNG??$LISTENPREIS||";
                    #print(utf8_encode($f));
                }
            }
        } else {
            echo "AJAX FEHLER 20041";
        }

        break;

    /*Betriebskostenabrechnung - Hinzuf�gen von Buchungen zum Profil*/
    case "buchung_hinzu" :
        if (file_exists("../classes/class_bk.php")) {
            include_once ("../classes/class_bk.php");
        }
        if (file_exists("../classes/class_wirtschafts_e.php")) {
            include_once ("../classes/class_wirtschafts_e.php");
        }

        $bk = new bk;
        $buchung_id = $_REQUEST["buchung_id"];
        $profil_id = $_REQUEST["profil_id"];
        $bk_konto_id = $_REQUEST["bk_konto_id"];
        $bk_genkey_id = $_SESSION[genkey];
        $bk_hndl = $_SESSION[hndl];

        if ($bk_hndl == '1') {
            $bk -> bk_buchungen_details($buchung_id);
            $hndl_betrag = $bk -> buchung_betrag;
        } else {
            $hndl_betrag = 0.00;
        }

        $kontierung_uebernehmen = $_SESSION[kontierung];

        if ($buchung_id && $profil_id && $bk_konto_id) {

            $bk -> bk_profil_infos($profil_id);
            $gesamt_anteil = $bk -> gesamt_anteil($buchung_id, $profil_id, $bk_konto_id);
            $max_anteil = 100 - $gesamt_anteil;

            if ($kontierung_uebernehmen == '1') {
                $bk -> bk_buchungen_details($buchung_id);
                $bk -> bk_kos_typ = $bk -> b_kos_typ;
                $bk -> bk_kos_id = $bk -> b_kos_id;

            }

            $last_bk_be_id = last_id_ajax('BK_BERECHNUNG_BUCHUNGEN', 'BK_BE_ID') + 1;
            $abfrage = "INSERT INTO BK_BERECHNUNG_BUCHUNGEN VALUES(NULL, '$last_bk_be_id', '$buchung_id', '$bk_konto_id', '$profil_id','$bk_genkey_id', '$max_anteil','$bk->bk_kos_typ', '$bk->bk_kos_id','$hndl_betrag','1')";
            $resultat = mysql_query($abfrage) or die(mysql_error());
        } else {
            echo "Fehler 67765213";
        }
        break;

    /*Betriebskostenabrechnung - Löschen von Buchungen zum Profil*/
    case "buchung_raus" :
        $bk_be_id = $_REQUEST["bk_be_id"];
        $profil_id = $_REQUEST["profil_id"];
        $bk_konto_id = $_REQUEST["bk_konto_id"];

        if ($bk_be_id && $profil_id && $bk_konto_id) {
            $abfrage = "DELETE FROM BK_BERECHNUNG_BUCHUNGEN WHERE BK_BE_ID='$bk_be_id' && BK_K_ID='$bk_konto_id' && BK_PROFIL_ID= '$profil_id'";
            $resultat = mysql_query($abfrage) or die(mysql_error());
        }
        break;

    /*Betriebskostenabrechnung - Löschen von Konten aus dem Profil*/
    case "konto_hinzu" :
        $profil_id = $_REQUEST["profil_id"];
        $bk_konto_id = $_REQUEST["bk_konto_id"];

        if ($profil_id && $bk_konto_id) {
            if (!check_konto_exists($bk_konto_id, $profil_id)) {
                $last_id = last_id_ajax('BK_KONTEN', 'BK_K_ID') + 1;
                $abfrage = "INSERT INTO BK_KONTEN VALUES (NULL, '$last_id', '$bk_konto_id', '$profil_id','0','0','1')";
                $resultat = mysql_query($abfrage) or die(mysql_error());
                $_SESSION[bk_konto] = $bk_konto_id;
            }
        }
        break;

    /*Betriebskostenabrechnung - L�schen von Konten aus dem Profil*/
    case "konto_raus" :
        $profil_id = $_REQUEST["profil_id"];
        $bk_konto_id = $_REQUEST["bk_konto_id"];

        if ($profil_id && $bk_konto_id) {
            $abfrage = "DELETE FROM BK_KONTEN WHERE  BK_K_ID='$bk_konto_id' && BK_PROFIL_ID= '$profil_id'";
            $resultat = mysql_query($abfrage) or die(mysql_error());

            $abfrage = "DELETE FROM BK_BERECHNUNG_BUCHUNGEN WHERE  BK_K_ID='$bk_konto_id' && BK_PROFIL_ID= '$profil_id'";
            $resultat = mysql_query($abfrage) or die(mysql_error());

            unset($_SESSION[bk_konto]);
            unset($_SESSION[bk_konto_id]);

            echo "Konto und Buchungen aus Profil entfernt";

        }
        break;

    case "get_eigentuemer" :
        if (!empty($_REQUEST[einheit_id])) {
            echo get_eigentuemer($_REQUEST[einheit_id]);
        } else {
            echo "Einheit w�hlen - Fehler 4554as";
        }
        break;

    case "get_wp_vorjahr_wert" :
        $wert = get_wp_vorjahr_wert($_REQUEST[objekt_id], $_REQUEST[vorjahr], $_REQUEST[kostenkonto]);
        echo $wert;
        break;

    case "zeitdiff" :
        $von = $_REQUEST['von'];
        $bis = $_REQUEST['bis'];
        if (!empty($von) && !empty($bis)) {
            $von_arr = explode(':', $von);
            $v_std = $von_arr[0];
            $v_min = $von_arr[1];
            $von_min = ($v_std * 60) + $v_min;

            $bis_arr = explode(':', $bis);
            $b_std = $bis_arr[0];
            $b_min = $bis_arr[1];
            $bis_min = ($b_std * 60) + $b_min;
            $dauer_min = $bis_min - $von_min;
            $dauer_std = ($dauer_min / 60);
            echo "$dauer_min|$dauer_std";
        } else {
            echo "FEHLER|FEHLER";
        }

        break;

    case "pool_auswahl" :
        #print_r($_SESSION);
        $dat = $_REQUEST['kontierung_dat'];
        $kos_typ = $_REQUEST['kos_typ'];
        $kos_id = $_REQUEST['kos_id'];
        $js_reg_pool = "onclick=\"reg_pool()\", 'nix')\"";
        dropdown_pools('Zielpool wählen', 'z_pool', 'z_pool', $js_reg_pool, $kos_typ, $kos_id);
        $js = "onclick=\"setTimeout('reg_pool()', 5);";
        $js = $js . "setTimeout('daj3(\'ajax/ajax_info.php?option=kont_pos_deactivate&kontierung_dat=$dat\', \'Rechnung aus Pool zusammenstellen\')', 400);";
        $js = $js . "setTimeout('location.reload()', 1000);\"";
        echo "<input type=button name=\"_snd\" value=\"Eintragen\" class=\"submit\" id=\"_snd\" $js>";

        break;

    case "kont_pos_deactivate" :
        $dat = $_REQUEST['kontierung_dat'];
        kontierung_pos_deaktivieren($dat);
        $r_obj = new rechnung;
        $r_obj -> get_kontierung_obj($dat);
        $pool_id = $_REQUEST['pool_id'];
        insert_in_u_pool($r_obj, $pool_id);
        break;

    case "pool_up" :
        $kos_typ = $_REQUEST['kos_typ'];
        $kos_id = $_REQUEST['kos_id'];
        $pp_dat = $_REQUEST['pp_dat'];
        $pos = $_REQUEST['virt_pos'];
        $pool_id = $_REQUEST['pool_id'];
        up($pp_dat, $pos, $pool_id);
        #$rr = new rechnungen();
        #$rr->u_pool_edit($kos_typ,$kos_id);
        break;

    case "pool_down" :
        $kos_typ = $_REQUEST['kos_typ'];
        $kos_id = $_REQUEST['kos_id'];
        $pp_dat = $_REQUEST['pp_dat'];
        $pos = $_REQUEST['virt_pos'];
        $pool_id = $_REQUEST['pool_id'];
        down($pp_dat, $pos, $pool_id);
        #$rr = new rechnungen();
        #$rr->u_pool_edit($kos_typ,$kos_id);
        break;

    case "change_wert" :
        $spalte = $_REQUEST['spalte'];
        $pp_dat = $_REQUEST['pp_dat'];
        $wert = nummer_komma2punkt($_REQUEST['wert']);
        update_spalte($pp_dat, $spalte, $wert);
        update_g_preis($pp_dat);
        $rr = new rechnungen();
        $rr -> u_pool_edit($kos_typ, $kos_id);
        break;

    case "change_details" :
        $dat = $_REQUEST['dat'];
        $wert = $_REQUEST['wert'];
        $det_name = $_REQUEST['det_name'];
        $kos_typ = $_REQUEST['kos_typ'];
        $kos_id = $_REQUEST['kos_id'];
        //detail_update($dat, $wert);
        /*echo "$dat, $wert, $det_name, $kos_typ, $kos_id";
         die();*/
        detail_update($dat, $wert, $det_name, $kos_typ, $kos_id);
        break;

    case "aufpreis" :
        $spalte = $_REQUEST['spalte'];
        $pp_dat = $_REQUEST['pp_dat'];
        $prozent = nummer_komma2punkt($_REQUEST['prozent']);
        update_v_preis($spalte, $pp_dat, $prozent);
        break;

    case "spalte_prozent" :
        $spalte = $_REQUEST['spalte'];
        $prozent = nummer_komma2punkt($_REQUEST['prozent']);
        update_spalte_2($spalte, $prozent);
        break;

    case "spalte_prozent_pool" :
        $spalte = $_REQUEST['spalte'];
        $prozent = nummer_komma2punkt($_REQUEST['prozent']);
        $pool_id = $_REQUEST['pool_id'];
        spalte_prozent_pool($spalte, $prozent, $pool_id);
        break;

    case "spalte_einheitspreis_pool" :
        $spalte = $_REQUEST['spalte'];
        $preis = nummer_komma2punkt($_REQUEST['preis']);
        $pool_id = $_REQUEST['pool_id'];
        spalte_einheitspreis_pool($spalte, $preis, $pool_id);
        break;

    case "u_pool_rechnung_erstellen" :
        $kos_typ = $_REQUEST['kos_typ'];
        $kos_id = $_REQUEST['kos_id'];
        /*
         $rr = new rechnung(); //aus berlussimo class
         if($kos_typ == 'Objekt'){
         $kos_typ_n = 'Partner';
         $kos_id_n = $rr->eigentuemer_ermitteln('Objekt', $kos_id);
         }
         if($kos_typ == 'Haus'){
         $kos_typ_n = 'Partner';
         $kos_id_n  = $rr->eigentuemer_ermitteln('Haus', $kos_id);
         }
         if($kos_typ == 'Einheit'){
         $kos_typ_n = 'Partner';
         $kos_id_n = $rr->eigentuemer_ermitteln('Einheit', $kos_id);
         }*/

        $aussteller_typ = $_REQUEST['aussteller_typ'];
        $aussteller_id = $_REQUEST['aussteller_id'];
        $r_datum = $_REQUEST['r_datum'];
        $f_datum = $_REQUEST['f_datum'];
        $kurzinfo = $_REQUEST['kurzinfo'];
        $gk_id = $_REQUEST['gk_id'];
        $pool_ids_string = $_REQUEST['pool_ids_string'];

        $r = new rechnungen();
        #if($kos_typ_n && $kos_id_n){
        #echo "$kos_typ, $kos_id, $aussteller_typ, $aussteller_id,$r_datum, $f_datum, $kurzinfo, $gk_id, $pool_ids_string";
        $r -> erstelle_rechnung_u_pool($kos_typ, $kos_id, $aussteller_typ, $aussteller_id, $r_datum, $f_datum, $kurzinfo, $gk_id, $pool_ids_string);
        #}
        break;

    case "u_pools_anzeigen" :
        $kos_typ = utf8_decode($_REQUEST['kos_typ']);
        $kos_bez = utf8_decode($_REQUEST['kos_bez']);
        $r = new rechnungen();
        echo "$kos_typ $kos_bez";
        $r -> u_pools_anzeigen($kos_typ, $kos_bez);
        break;

    case "pool_act_deactivate" :
        $kos_typ = utf8_decode($_REQUEST['kos_typ']);
        $kos_id = utf8_decode($_REQUEST['kos_id']);
        $pool_id = $_REQUEST['pool_id'];
        $_SESSION['pool_id'] = $pool_id;
        $r = new rechnungen();
        $r -> pool_act_deactivate($pool_id, $kos_typ, $kos_id);
        break;

    case "u_pool_erstellen" :
        $kos_typ = utf8_decode($_REQUEST['kos_typ']);
        $kos_bez = utf8_decode($_REQUEST['kos_bez']);
        $pool_bez = utf8_decode($_REQUEST['pool_bez']);
        $r = new rechnungen();
        $r -> u_pool_erstellen($pool_bez, $kos_typ, $kos_bez);
        break;

    case "change_text" :
        $art_nr = $_REQUEST['art_nr'];
        $lieferant_id = $_REQUEST['lieferant_id'];
        $text_neu = $_REQUEST['text_neu'];
        #echo "$art_nr $lieferant_id $text_neu";
        $r = new rechnungen();
        if (!empty($art_nr) && !empty($lieferant_id) && !empty($text_neu)) {
            $r -> artikel_text_update($art_nr, $lieferant_id, $text_neu);
        }
        break;

    case "back2pool" :
        $pp_dat = $_REQUEST['pp_dat'];

        if (!empty($pp_dat)) {
            $r = new rechnungen();
            $r -> back2pool($pp_dat);
        }
        break;

    case "change_kautionsfeld" :
        $feld = $_REQUEST['feld'];
        $wert = $_REQUEST['wert'];
        $mv_id = $_REQUEST['mv_id'];
        $k = new kautionen;
        $k -> feld_wert_speichern($mv_id, $feld, $wert);
        break;

    case "change_hk_wert_et" :
        $eig_id = $_REQUEST['et_id'];
        $betrag = $_REQUEST['wert'];
        $p_id = $_REQUEST['profil_id'];

        $w = new weg;
        $w -> hk_verbrauch_eintragen($p_id, $eig_id, $betrag);
        break;
}//END SWITCH

function update_spalte_2($spalte, $prozent) {
    $db_abfrage = "UPDATE POS_POOL SET `$spalte`=(EINZEL_PREIS+((EINZEL_PREIS/100)*$prozent)) WHERE AKTUELL='1'";
    echo $db_abfrage;
    $result = mysql_query($db_abfrage) or die(mysql_error());
    update_g_preis();
}

function spalte_prozent_pool($spalte, $prozent, $pool_id) {
    $db_abfrage = "UPDATE POS_POOL SET `$spalte`=(EINZEL_PREIS+((EINZEL_PREIS/100)*$prozent)) WHERE POOL_ID='$pool_id' && AKTUELL='1'";
    #echo $db_abfrage;
    $result = mysql_query($db_abfrage) or die(mysql_error());
    update_g_preis();
}

function spalte_einheitspreis_pool($spalte, $preis, $pool_id) {
    $db_abfrage = "UPDATE POS_POOL SET `$spalte`=$preis WHERE POOL_ID='$pool_id' && AKTUELL='1'";
    #echo $db_abfrage;
    $result = mysql_query($db_abfrage) or die(mysql_error());
    update_g_preis();
}

function update_spalte($pp_dat, $spalte, $wert) {
    $db_abfrage = "UPDATE POS_POOL SET `$spalte`='$wert' WHERE PP_DAT='$pp_dat'";
    $result = mysql_query($db_abfrage) or die(mysql_error());
}

function up($pp_dat, $pos, $pool_id) {
    $pos_new = $pos - 1;

    $db_abfrage = "UPDATE POS_POOL SET POS='$pos_new' WHERE PP_DAT='$pp_dat'";
    $result = mysql_query($db_abfrage);

    $db_abfrage = "UPDATE POS_POOL SET POS='$pos' WHERE POS='$pos_new' && POOL_ID='$pool_id' && PP_DAT!='$pp_dat'";
    $result = mysql_query($db_abfrage);

    update_g_preis($pp_dat);

}

function down($pp_dat, $pos, $pool_id) {
    $pos_new = $pos + 1;
    $db_abfrage = "UPDATE POS_POOL SET POS='$pos_new' WHERE PP_DAT='$pp_dat'";
    $result = mysql_query($db_abfrage);

    $db_abfrage = "UPDATE POS_POOL SET POS='$pos' WHERE POS='$pos_new' && POOL_ID='$pool_id' && PP_DAT!='$pp_dat'";
    $result = mysql_query($db_abfrage);

    update_g_preis($pp_dat);
}

function get_last_pos($kos_typ, $kos_id, $aussteller_typ, $aussteller_id, $pool_id) {
    $result = mysql_query("SELECT POS FROM POS_POOL WHERE POOL_ID='$pool_id' && KOS_TYP='$kos_typ' && KOS_ID='$kos_id' && AUSSTELLER_TYP='$aussteller_typ' && AUSSTELLER_ID='$aussteller_id' && AKTUELL='1' ORDER BY POS DESC LIMIT 0,1");
    #echo "SELECT POS FROM POS_POOL WHERE KOS_TYP='$kos_typ' && KOS_ID='$kos_id' && AUSSTELLER_TYP='$aussteller_typ' && AUSSTELLER_ID='$aussteller_id' ORDER BY POS DESC LIMIT 0,1";
    $numrows = mysql_numrows($result);
    if ($numrows) {
        $row = mysql_fetch_assoc($result);
        return $row['POS'];
    } else {
        return 0;
    }
}

function insert_in_u_pool($obj, $pool_id) {
    $pos = get_last_pos($obj -> kos_typ, $obj -> kos_id, $obj -> rechnungs_empfaenger_typ, $obj -> rechnungs_empfaenger_id, $pool_id) + 1;
    $db_abfrage = "INSERT INTO POS_POOL VALUES(NULL, '$obj->beleg_nr', '$obj->pos', '$pool_id', '$pos', '$obj->menge', '$obj->einzel_preis','$obj->einzel_preis', '$obj->g_summe', '$obj->mwst_satz', '$obj->skonto', '$obj->rabatt_satz', '$obj->kostenkonto', '$obj->kos_typ', '$obj->kos_id','$obj->rechnungs_empfaenger_typ','$obj->rechnungs_empfaenger_id', '1')";
    $result = mysql_query($db_abfrage) or die(mysql_error());
}

function update_g_preis() {
    $db_abfrage = "UPDATE POS_POOL SET G_SUMME=(MENGE*V_PREIS)/100*(100-RABATT_SATZ) WHERE AKTUELL='1'";
    $result = mysql_query($db_abfrage) or die(mysql_error());

}

function update_v_preis($spalte, $pp_dat, $prozent) {
    $db_abfrage = "UPDATE POS_POOL SET V_PREIS=(EINZEL_PREIS+((EINZEL_PREIS/100)*$prozent)) WHERE PP_DAT='$pp_dat' && AKTUELL='1'";
    echo $db_abfrage;
    $result = mysql_query($db_abfrage) or die(mysql_error());
    update_g_preis();
}

function kontierung_pos_deaktivieren($dat) {
    $db_abfrage = "UPDATE KONTIERUNG_POSITIONEN SET WEITER_VERWENDEN='0' WHERE KONTIERUNG_DAT='$dat'";
    $resultat = mysql_query($db_abfrage) or die(mysql_error());
    /*Protokollieren*/
    protokollieren('KONTIERUNG_POSITIONEN', $dat, $dat);
    #echo "$db_abfrage<br>OK";
}

function dropdown_pools($label, $name, $id, $js, $kos_typ, $kos_id) {
    $result = mysql_query("SELECT *  FROM  POS_POOLS WHERE KOS_TYP='$kos_typ' && KOS_ID='$kos_id' && AKTUELL='1' ORDER BY POOL_NAME ASC");
    #echo "SELECT *  FROM  POS_POOLS WHERE KOS_TYP='$kos_typ' && KOS_ID='$kos_id' && AKTUELL='1' ORDER BY POOL_NAME ASC";
    $numrows = mysql_numrows($result);
    if ($numrows > 0) {
        while ($row = mysql_fetch_assoc($result))
            $my_array[] = $row;

        echo "<label for=\"$id\">$label</label>\n<select name=\"$name\" id=\"$id\" size=\"1\" $js>\n";
        for ($a = 0; $a < count($my_array); $a++) {
            $pool_id = $my_array[$a]['ID'];
            $pool_name = $my_array[$a]['POOL_NAME'];

            if (isset($_SESSION[pool_id]) && $_SESSION[pool_id] == $pool_id) {
                echo "<option value=\"$pool_id\" selected>$pool_name</option>\n";
            } else {
                echo "<option value=\"$pool_id\" >$pool_name</option>\n";
            }
        }//end for
        echo "</select>\n";
    } else {
        echo "$kos_typ $kos_id";
        echo "<b>Keine Unterpools hinterlegt oder aktiviert</b>";
        $link = "<br><a href=\"?daten=rechnungen&option=u_pool_erstellen\">Hier Pools erstellen</a>";
        echo $link;
        die();
        return FALSE;
    }

}

function connectToBase() {
    mysql_connect(DB_HOST, DB_USER, DB_PASS);
    mysql_select_db(DB_NAME);
}

function get_wp_vorjahr_wert($objekt_id, $vorjahr, $kostenkonto) {
    $result = mysql_query("SELECT PLAN_ID FROM WEG_WPLAN WHERE AKTUELL='1' && JAHR='$vorjahr' && OBJEKT_ID='$objekt_id' LIMIT 0,1");
    $row = mysql_fetch_assoc($result);
    if ($row) {
        $vorplan_id = $row['PLAN_ID'];
        $result = mysql_query("SELECT BETRAG FROM WEG_WPLAN_ZEILEN WHERE WPLAN_ID='$vorplan_id' && AKTUELL='1' && KOSTENKONTO='$kostenkonto' LIMIT 0,1");
        $row = mysql_fetch_assoc($result);
        return nummer_punkt2komma($row['BETRAG']);
    } else {
        return '0,00';
    }

}

/*Artikelinformationen aus dem Katalog holen*/
function artikel_info($partner_id, $artikel_nr) {
    $result = mysql_query("SELECT * FROM POSITIONEN_KATALOG WHERE ART_LIEFERANT='$partner_id' && ARTIKEL_NR = '$artikel_nr' && AKTUELL='1' ORDER BY KATALOG_DAT DESC LIMIT 0,1");
    $numrows = mysql_numrows($result);
    if ($numrows < 1) {
        return false;

    } else {
        while ($row = mysql_fetch_assoc($result))
            $my_array[] = $row;
        return $my_array;
    }

}

/*function nummer_punkt2komma($zahl){
 $zahl= sprintf("%01.2f", $zahl);
 return $zahl;
 }*/

function check_konto_exists($konto, $profil_id) {
    $result = mysql_query("SELECT * FROM BK_KONTEN WHERE KONTO='$konto' && BK_PROFIL_ID='$profil_id' && AKTUELL='1' LIMIT 0,1");
    $numrows = mysql_numrows($result);
    if ($numrows) {
        return true;
    } else {
        return false;
    }
}

/*Ermitteln der letzten katalog_id*/
function get_last_katalog_id() {
    $result = mysql_query("SELECT KATALOG_ID FROM POSITIONEN_KATALOG WHERE AKTUELL='1' ORDER BY KATALOG_ID DESC LIMIT 0,1");
    $row = mysql_fetch_assoc($result);
    return $row[KATALOG_ID];
}

function get_kostentraeger_infos($typ, $id) {
    if ($typ == 'Objekt') {
        $db_abfrage = "SELECT OBJEKT_KURZNAME FROM OBJEKT WHERE OBJEKT_AKTUELL='1' && OBJEKT_ID='$id'";
        $resultat = mysql_query($db_abfrage) or die(mysql_error());
        #echo "<meta content=\"text/html; charset=ISO-8859-1\" http-equiv=\"content-type\">";
        while (list($OBJEKT_KURZNAME) = mysql_fetch_row($resultat)) {
            #echo "$OBJEKT_KURZNAME";
            return $OBJEKT_KURZNAME;
        }
    }

    if ($typ == 'Haus') {
        $db_abfrage = "SELECT HAUS_STRASSE, HAUS_NUMMER FROM HAUS WHERE HAUS_AKTUELL='1' && HAUS_ID='$id'";
        $resultat = mysql_query($db_abfrage) or die(mysql_error());

        while (list($HAUS_STRASSE, $HAUS_NUMMER) = mysql_fetch_row($resultat))
        #echo "$HAUS_STRASSE $HAUS_NUMMER";
            return "$HAUS_STRASSE $HAUS_NUMMER";
    }

    if ($typ == 'Einheit') {
        $db_abfrage = "SELECT EINHEIT_KURZNAME FROM EINHEIT WHERE EINHEIT_AKTUELL='1' && EINHEIT_ID='$id'";
        $resultat = mysql_query($db_abfrage) or die(mysql_error());
        while (list($EINHEIT_KURZNAME) = mysql_fetch_row($resultat))
        #echo "$EINHEIT_KURZNAME";
            return "$EINHEIT_KURZNAME";
    }

    if ($typ == 'Partner') {
        $db_abfrage = "SELECT PARTNER_NAME FROM PARTNER_LIEFERANT WHERE AKTUELL='1' && PARTNER_ID='$id' ";
        $resultat = mysql_query($db_abfrage) or die(mysql_error());
        while (list($PARTNER_NAME) = mysql_fetch_row($resultat))
            $PARTNER_NAME1 = str_replace('<br>', '', $PARTNER_NAME);
        #echo "$PARTNER_NAME1";
        return "$PARTNER_NAME1";
    }
    if ($typ == 'Lager') {
        $db_abfrage = "SELECT LAGER_NAME FROM LAGER WHERE AKTUELL='1' && LAGER_ID='$id'";
        $resultat = mysql_query($db_abfrage) or die(mysql_error());
        while (list($LAGER_NAME) = mysql_fetch_row($resultat))
            $LAGER_NAME1 = str_replace('<br>', '', $LAGER_NAME);
        return "$LAGER_NAME1";
    }

}

function last_id_ajax($tab, $spalte) {
    $result = mysql_query("SELECT $spalte FROM `$tab` ORDER BY $spalte DESC LIMIT 0,1");
    $numrows = mysql_numrows($result);
    if ($numrows) {
        $row = mysql_fetch_assoc($result);
        return $row[$spalte];
    } else {
        return 0;
    }
}

function get_eigentuemer($einheit_id) {
    $weg = new weg;
    $weg -> get_last_eigentuemer_namen($einheit_id);
    $eigentuemer = strip_tags($weg -> eigentuemer_namen);
    if (!empty($eigentuemer)) {
        return $eigentuemer;
    } else {
        return 'Kein Eigentümer';
    }
}

function get_objekt_arr_gk($gk_id) {
    $db_abfrage = "SELECT KOSTENTRAEGER_ID  FROM  GELD_KONTEN_ZUWEISUNG WHERE AKTUELL = '1' && KONTO_ID='$geldkonto_id' && KOSTENTRAEGER_TYP='Objekt'";
    $result = mysql_query($db_abfrage) or die(mysql_error());
    $numrows = mysql_numrows($result);
    if ($numrows) {
        while ($row = mysql_fetch_assoc($result)) {
            $arr[] = $row['KOSTENTRAEGER_ID'];
        }
        return $arr;
    } else {
        return false;
    }
}

function detail_update($detail_dat, $wert_neu, $det_name, $kos_typ, $kos_id) {
    $d = new detail();
    if ($detail_dat != 0) {
        $row = $d -> get_detail_info($detail_dat);
        if (is_array($row)) {
            //print_r($row);
            $alt_dat = $row['DETAIL_DAT'];
            $alt_id = $row['DETAIL_ID'];
            $det_inhalt = $row['DETAIL_INHALT'];
            $det_name = $row['DETAIL_NAME'];
            //$det_bemerkung = $row['DETAIL_BEMERKUNG'];
            $tabelle = $row['DETAIL_ZUORDNUNG_TABELLE'];
            $tabelle_id = $row['DETAIL_ZUORDNUNG_ID'];
            $det_bemerkung = $_SESSION['username'] . '-' . date("d.m.Y H:i");

            $db_abfrage = "UPDATE DETAIL SET DETAIL_AKTUELL='0' WHERE DETAIL_DAT='$detail_dat'";
            $resultat = mysql_query($db_abfrage) or die(mysql_error());
            $d -> detail_speichern_2($tabelle, $tabelle_id, $det_name, $wert_neu, $det_bemerkung);

        }
    } else {
        $det_bemerkung = $_SESSION['username'] . '-' . date("d.m.Y H:i");
        $d -> detail_speichern_2($kos_typ, $kos_id, $det_name, $wert_neu, $det_bemerkung);

    }
}
?>

