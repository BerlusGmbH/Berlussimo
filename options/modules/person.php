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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/person.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */

// error_reporting(E_ALL);
include_once ("includes/allgemeine_funktionen.php");

/* überprüfen ob Benutzer Zugriff auf das Modul hat */
if (! isset ( $_SESSION ['benutzer_id'] ) or ! check_user_mod ( $_SESSION ['benutzer_id'], 'person' )) {
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';
	die ();
}

include_once ("includes/formular_funktionen.php");
//include_once ("options/links/links.person.php");
include_once ("classes/class_person.php");

if (isset ( $_REQUEST ["daten"] )) {
	$daten = $_REQUEST ["daten"];
	if (isset ( $_REQUEST ["anzeigen"] )) {
		$anzeigen = $_REQUEST ["anzeigen"];
	}
	if (isset ( $_REQUEST ["submit_person"] )) {
		$submit_person = $_REQUEST ['submit_person'];
	}
	if (isset ( $_REQUEST ["submit_person_direkt"] )) {
		$submit_person_direkt = $_REQUEST ["submit_person_direkt"];
	}
	if (isset ( $_REQUEST ["person_loeschen"] )) {
		$person_loeschen = $_REQUEST ["person_loeschen"];
	}
	if (isset ( $_REQUEST ["person_aendern"] )) {
		$person_aendern = $_REQUEST ["person_aendern"];
	}
	if (isset ( $_REQUEST ["submit_person_aendern"] )) {
		$submit_person_aendern = $_REQUEST ["submit_person_aendern"];
	}
	switch ($anzeigen) {
		
		case "alle_personen" :
			$form = new mietkonto ();
			$form->erstelle_formular ( "Liste aller vorhandenen Personen", NULL );
			include ("options/formulare/person_suche.php");
			// personen_liste_alle();
			$p = new personen ();
			$p->personen_liste_alle ();
			$form->ende_formular ();
			break;
		
		case "person_erfassen_alt" :
			personen_liste (); // seitlicher block
			$form = new mietkonto ();
			$form->erstelle_formular ( "Neue Person erfassen", NULL );
			
			iframe_start ();
			// echo "person erfassen";
			if (! isset ( $submit_person )) {
				person_erfassen_form ();
			}
			if (isset ( $submit_person )) {
				echo "<pre>";
				// print_r($_REQUEST);
				echo "</pre>";
				check_fields ();
			}
			if (isset ( $submit_person_direkt )) {
				person_in_db_eintragen_direkt ();
			}
			if (isset ( $_REQUEST [person_speichern] )) {
				person_in_db_eintragen ();
			}
			iframe_end ();
			$form->ende_formular ();
			break;
		
		/* Neues Personeneingabeformular mit Geschlechteingabe in die Details */
		case "person_erfassen" :
			$p = new personen ();
			$p->form_person_erfassen ();
			break;
		
		/* Prüfen der Eingabe im Formular */
		case "person_erfassen_check" :
			$f = new formular ();
			$f->erstelle_formular ( 'Überprüfen', '' );
			$f->fieldset ( "Daten überprüfen", 'p_pruefen' );
			$geb_dat = $_POST ['geburtsdatum'];
			$nachname = $_POST ['nachname'];
			$vorname = $_POST ['vorname'];
			$geschlecht = $_POST ['geschlecht'];
			$telefon = $_POST ['telefon'];
			$handy = $_POST ['handy'];
			$email = $_POST ['email'];
			if (empty ( $nachname ) or empty ( $vorname ) or empty ( $geb_dat )) {
				fehlermeldung_ausgeben ( "<br>Name, Vorname oder Geburtsdatum unvollständig" );
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
				if ($p->person_exists ( $vorname, $nachname, $geb_dat )) {
					echo "$nachname $vorname geb. am $geb_dat existiert bereits, trotzdem speichern???";
				}
				$f->hidden_feld ( "nachname", "$nachname" );
				$f->hidden_feld ( "vorname", "$vorname" );
				$f->hidden_feld ( "geburtsdatum", "$geb_dat" );
				$f->hidden_feld ( "geschlecht", "$geschlecht" );
				$f->hidden_feld ( "telefon", "$telefon" );
				$f->hidden_feld ( "handy", "$handy" );
				$f->hidden_feld ( "email", "$email" );
				$f->hidden_feld ( "anzeigen", "person_erfassen_save" );
				$f->send_button ( "submit_kostenkonto", "Speichern" );
			}
			$f->fieldset_ende ();
			$f->ende_formular ();
			break;
		
		/* Neue Person nach Prüfung speichern */
		case "person_erfassen_save" :
			$f = new formular ();
			$f->fieldset ( "Person/Mieter speichern", 'p_save' );
			$geb_dat = $_POST ['geburtsdatum'];
			$nachname = $_POST ['nachname'];
			$vorname = $_POST ['vorname'];
			$geschlecht = $_POST ['geschlecht'];
			$telefon = $_POST ['telefon'];
			$handy = $_POST ['handy'];
			$email = $_POST ['email'];
			if (empty ( $nachname ) or empty ( $vorname ) or empty ( $geb_dat )) {
				fehlermeldung_ausgeben ( "<br>Name, Vorname oder Geburtsdatum unvollständig" );
			} else {
				echo "Eingegebene Daten überprüfen<hr>";
				echo "Nachname:$nachname<br>";
				echo "Vorname: $vorname<br>";
				echo "Geburtsdatum: $geb_dat<br>";
				echo "Telefon: $telefon<br>";
				echo "Handy: $handy<br><br>";
				echo "Email: $email<br><br>";
				$p = new personen ();
				$p->save_person ( $nachname, $vorname, $geb_dat, $geschlecht, $telefon, $handy, $email );
			}
			
			$f->fieldset_ende ();
			break;
		
		case "alle_mieter" :
			$form = new mietkonto ();
			$form->erstelle_formular ( "Liste aller Mieter", NULL );
			iframe_start ();
			// mieternamen_liste_alle();
			alle_mieter_arr (); // funct hier
			iframe_end ();
			$form->ende_formular ();
			break;
		
		case "person_loeschen" :
			$form = new mietkonto ();
			$form->erstelle_formular ( "Person löschen", NULL );
			iframe_start ();
			if (isset ( $_REQUEST ["person_dat"] )) {
				// person_loeschen($_REQUEST["person_dat"]);
				hinweis_ausgeben ( "Löschfunktion deaktiviert!!!" );
				weiterleiten ( "javascript:history.back()" );
			}
			iframe_end ();
			$form->ende_formular ();
			break;
		
		case "person_aendern" :
			$form = new mietkonto ();
			$form->erstelle_formular ( "Person ändern", NULL );
			iframe_start ();
			echo "Person aendern";
			if (isset ( $_REQUEST ["person_id"] ) && ! isset ( $submit_person_aendern ) && ! isset ( $_REQUEST [person_definitiv_speichern] )) {
				person_aendern_from ( $_REQUEST ["person_id"] );
			}
			if (isset ( $submit_person_aendern )) {
				check_fields_nach_aenderung (); // prüfen und eintragen
			}
			if (isset ( $_REQUEST [person_definitiv_speichern] )) {
				check_fields_nach_aenderung (); // Änderung anzeigen prüfen und eintragen
			}
			iframe_end ();
			$form->ende_formular ();
			break;
		
		case "person_hinweis" :
			$p = new personen ();
			$p->get_person_hinweis ();
			break;
		
		case "person_anschrift" :
			$p = new personen ();
			/* Verzugs und Zustelladressen */
			$p->get_person_anschrift ();
			break;
	}
}
function check_fields() {
	print_r ( $_POST );
	
	foreach ( $_REQUEST as $key => $value ) {
		
		if (($key == "person_nachname") && empty ( $value )) {
			fehlermeldung_ausgeben ( "Bitte tragen Sie einen Familiennamen ein!" );
			backlink ();
			$myerror = true;
			break;
		} 

		elseif (($key == "person_vorname") && empty ( $value )) {
			fehlermeldung_ausgeben ( "Bitte tragen Sie einen Vornamen ein!" );
			backlink ();
			$myerror = true;
			break;
		} 

		elseif (($key == "person_geburtstag") && empty ( $value )) {
			fehlermeldung_ausgeben ( "Bitte tragen Sie einen Geburtstag ein!" );
			backlink ();
			$myerror = true;
			break;
		} 

		elseif (($key == "person_geburtstag") && isset ( $value )) {
			$datum = $_REQUEST [person_geburtstag];
			// if(strlen ($datum) != "10"){
			// fehlermeldung_ausgeben("Datumslänge nicht korrekt!");
			// backlink();
			// $myerror = true;
			// break;
			// }
			
			$tmp = explode ( ".", $datum );
			if (checkdate ( $tmp [1], $tmp [0], $tmp [2] )) {
			} else {
				fehlermeldung_ausgeben ( "Falsches Datumsformat, bitte überprüfen!" );
				backlink ();
				$myerror = true;
				break;
			}
		}
	} // end for
	if (! isset ( $myerror )) {
		foreach ( $_REQUEST as $key => $value ) {
			// echo "$key => $value<br>";
		}
		erstelle_formular ( NULL, NULL ); // name, action
		echo "<tr><td><h1>Folgende Daten wurden übermittelt:\n</h1></td></tr>\n";
		echo "<tr><td><h2>Personendaten: $objekt_kurzname</h2></td></tr>\n";
		echo "<tr><td>";
		// print_r($_POST);
		warnung_ausgeben ( "Sind Sie sicher, daß Sie die Person $_POST[person_nachname] $_POST[person_vorname] geb. am $_POST[person_geburtstag] speichern wollen?" );
		echo "</td></tr>";
		erstelle_hiddenfeld ( "person_nachname", "$_POST[person_nachname]" );
		erstelle_hiddenfeld ( "person_vorname", "$_POST[person_vorname]" );
		erstelle_hiddenfeld ( "person_geburtstag", "$_POST[person_geburtstag]" );
		erstelle_submit_button ( "person_speichern", "Speichern" ); // name, wert
		ende_formular ();
	}
}
function alle_mieter_arr() {
	$abfrage = "SELECT  DISTINCT MIETVERTRAG.EINHEIT_ID, MIETVERTRAG.MIETVERTRAG_ID, PERSON_MIETVERTRAG.PERSON_MIETVERTRAG_MIETVERTRAG_ID, PERSON_MIETVERTRAG.PERSON_MIETVERTRAG_PERSON_ID";
	$abfrage .= " FROM `MIETVERTRAG`, PERSON_MIETVERTRAG WHERE ((MIETVERTRAG_BIS = '0000-00-00')OR (MIETVERTRAG_BIS >'2008-06-10'))";
	$abfrage .= " && ( MIETVERTRAG.MIETVERTRAG_ID = PERSON_MIETVERTRAG.PERSON_MIETVERTRAG_MIETVERTRAG_ID) ";
	
	// $abfrage = 'SELECT DISTINCT PERSON.PERSON_ID, PERSON.PERSON_NACHNAME, PERSON.PERSON_VORNAME, PERSON_MIETVERTRAG.PERSON_MIETVERTRAG_PERSON_ID, PERSON_MIETVERTRAG.PERSON_MIETVERTRAG_MIETVERTRAG_ID, MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.MIETVERTRAG_BIS, MIETVERTRAG.EINHEIT_ID';
	// $abfrage .=' FROM PERSON, PERSON_MIETVERTRAG, MIETVERTRAG WHERE PERSON.PERSON_ID = PERSON_MIETVERTRAG.PERSON_MIETVERTRAG_PERSON_ID && PERSON_MIETVERTRAG.PERSON_MIETVERTRAG_MIETVERTRAG_ID = MIETVERTRAG.MIETVERTRAG_ID';
	// $abfrage .=' && ((MIETVERTRAG.MIETVERTRAG_BIS=\'0000-00-00\') OR (MIETVERTRAG.MIETVERTRAG_BIS>\'2008-06-10\')) ORDER BY PERSON.PERSON_NACHNAME ASC';
	// echo $abfrage;
	// $abfrage = "SELECT DISTINCT PERSON.PERSON_ID, PERSON.PERSON_NACHNAME, PERSON.PERSON_VORNAME, PERSON_MIETVERTRAG.PERSON_MIETVERTRAG_PERSON_ID, PERSON_MIETVERTRAG.PERSON_MIETVERTRAG_MIETVERTRAG_ID, MIETVERTRAG.MIETVERTRAG_ID, MIETVERTRAG.MIETVERTRAG_BIS, MIETVERTRAG.EINHEIT_ID FROM PERSON, PERSON_MIETVERTRAG, MIETVERTRAG WHERE PERSON.PERSON_ID = PERSON_MIETVERTRAG.PERSON_MIETVERTRAG_PERSON_ID && PERSON_MIETVERTRAG.PERSON_MIETVERTRAG_MIETVERTRAG_ID = MIETVERTRAG.MIETVERTRAG_ID ORDER BY PERSON.PERSON_NACHNAME ASC";
	$result = mysql_query ( $abfrage );
	$mieterdaten = array ();
	while ( $row = mysql_fetch_assoc ( $result ) ) {
		// echo $row[PERSON.PERSON_ID];
		$mieterdaten [] = $row;
	}
	$anzahl_mieter = count ( $mieterdaten );
	// return $mieterdaten;
	echo "<pre>";
	print_r ( $mieterdaten );
	echo "</pre>";
}
function check_fields_nach_aenderung() {
	foreach ( $_REQUEST as $key => $value ) {
		
		if (($key == "person_nachname") && empty ( $value )) {
			fehlermeldung_ausgeben ( "Bitte tragen Sie einen Familiennamen ein!" );
			backlink ();
			$myerror = true;
			break;
		} 

		elseif (($key == "person_vorname") && empty ( $value )) {
			fehlermeldung_ausgeben ( "Bitte tragen Sie einen Vornamen ein!" );
			backlink ();
			$myerror = true;
			break;
		} 

		elseif (($key == "person_geburtstag") && empty ( $value )) {
			fehlermeldung_ausgeben ( "Bitte tragen Sie einen Geburtstag ein!" );
			backlink ();
			$myerror = true;
			break;
		} 

		elseif (($key == "person_geburtstag") && isset ( $value )) {
			$datum = $_REQUEST [person_geburtstag];
			// if(strlen ($datum) != "10"){
			// fehlermeldung_ausgeben("Datumslänge nicht korrekt!");
			// backlink();
			// $myerror = true;
			// break;
			// }
			$tmp = explode ( ".", $datum );
			if (checkdate ( $tmp [1], $tmp [0], $tmp [2] )) {
			} else {
				fehlermeldung_ausgeben ( "Falsches Datumsformat, bitte überprüfen!" );
				backlink ();
				$myerror = true;
				break;
			}
		}
	} // end for
	if (! isset ( $myerror )) {
		foreach ( $_REQUEST as $key => $value ) {
			// echo "$key => $value<br>";
		}
		if (! isset ( $_REQUEST [person_definitiv_speichern] )) {
			erstelle_formular ( NULL, NULL ); // name, action
			echo "<tr><td><h1>Folgende Daten wurden übermittelt:\n</h1></td></tr>\n";
			echo "<tr><td><h2>Personendaten: $objekt_kurzname</h2></td></tr>\n";
			echo "<tr><td>";
			// print_r($_POST);
			warnung_ausgeben ( "Sind Sie sicher, daß Sie die Person $_POST[person_nachname] $_POST[person_vorname] geb. am $_POST[person_geburtstag] ändern wollen?" );
			echo "</td></tr>";
			erstelle_hiddenfeld ( "person_nachname", "$_POST[person_nachname]" );
			erstelle_hiddenfeld ( "person_vorname", "$_POST[person_vorname]" );
			erstelle_hiddenfeld ( "person_geburtstag", "$_POST[person_geburtstag]" );
			erstelle_submit_button ( "person_definitiv_speichern", "Speichern" ); // name, wert
			ende_formular ();
		}
	}
	if (isset ( $_REQUEST [person_definitiv_speichern] )) {
		person_aendern_in_db ( $_REQUEST ["person_id"] );
		hinweis_ausgeben ( "Person: $_REQUEST[person_nachname] $_REQUEST[person_vorname] wurde geändert !" );
		hinweis_ausgeben ( "Sie werden weitergeleitet." );
		// echo "<head>";
		// echo "<meta http-equiv=\"refresh\" content=\"2; URL=?daten=person&anzeigen=alle_personen\">";
		// echo "</head>";
		weiterleiten ( "?daten=person&anzeigen=alle_personen" );
	}
}
function personen_liste() {
	$db_abfrage = "SELECT PERSON_NACHNAME, PERSON_VORNAME, PERSON_GEBURTSTAG FROM PERSON WHERE PERSON_AKTUELL='1' ORDER BY PERSON_NACHNAME, PERSON_VORNAME ASC";
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	echo "<div class=\"tabelle_personen\"><table>\n";
	echo "<tr class=\"feldernamen\"><td colspan=3>Personenliste</td></tr>\n";
	echo "<tr class=\"feldernamen\"><td>Nachname</td><td>Vorname</td><td>Geb. am</td></tr>\n";
	$counter = 0;
	while ( list ( $PERSON_NACHNAME, $PERSON_VORNAME, $PERSON_GEBURTSTAG ) = mysql_fetch_row ( $resultat ) ) {
		$counter ++;
		if ($counter == 1) {
			echo "<tr class=\"zeile1\"><td>$PERSON_NACHNAME</td><td>$PERSON_VORNAME</td><td>$PERSON_GEBURTSTAG</td></tr>\n";
		}
		if ($counter == 2) {
			echo "<tr class=\"zeile2\"><td>$PERSON_NACHNAME</td><td>$PERSON_VORNAME</td><td>$PERSON_GEBURTSTAG</td></tr>\n";
			$counter = 0;
		}
	}
	echo "</table></div>";
}

?>
