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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/formulare/form_haus.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

include_once ("options/links/links.form_haus.php");

if (request()->has('daten_rein')) {
	switch (request()->input('daten_rein')) {
		
		case "anlegen" :
			$form = new mietkonto ();
			$form->erstelle_formular ( "Haus anlegen", NULL );
			
			if (!request()->has("haus_objekt")) {
				iframe_start ();
				objekt_liste_links ();
			} else {
				haeuser_liste_tabelle ( request()->input('haus_objekt') ); // rechts die liste der häuser
				iframe_start ();
				haus_eingabe_formular ( request()->input('haus_objekt') );
			}
			
			if (request()->has("submit_haus")) {
				foreach ( request()->request->all() as $key => $value ) {
					if (empty ( $value )) {
						fehlermeldung_ausgeben ( "Alle felder müssen ausgefüllt werden" );
						weiterleiten ( "javascript:history.back()" );
						// echo "<a href=\"javascript:history.back()\">Zurück</a>\n";
						$error = 1;
						iframe_end ();
						break;
					}
				}
				if ($error != 1) {
					$letzte_haus_id = letzte_haus_id ();
					haus_in_db_eintragen ( request()->input('haus_strasse'), request()->input('haus_nummer'), request()->input('haus_stadt'), request()->input('haus_plz'), request()->input('haus_qm'), request()->input('haus_objekt'));
				}
			}
			iframe_end ();
			$form->ende_formular ();
			break;
		
		case "haus_neu" :
			$h = new haus ();
			if (request()->has('objekt_id')) {
				$h->form_haus_neu ( request()->input('objekt_id') );
			} else {
				$h->form_haus_neu ( '' );
			}
			break;
		
		case "haus_speichern" :
			if (request()->isMethod('post')) {
				if (request()->has('strasse') && request()->has('haus_nr') && request()->has('ort') && request()->has('plz') && request()->has('qm') && request()->has('objekt_id')) {
					echo "alles ok";
					$h = new haus ();
					$h->haus_speichern ( request()->input('strasse'), request()->input('haus_nr'), request()->input('ort'), request()->input('plz'), request()->input('qm'), request()->input('objekt_id') );
					weiterleiten ( route('legacy::haeuser::index', ['haus_raus' => 'haus_kurz', 'objekt_id' => request()->input('objekt_id')], false));
				} else {
					echo "Daten unvollständig";
				}
			} else {
				echo "Daten unvollständig";
			}
			break;
		
		case "aendern_liste" :
			$form = new mietkonto ();
			$form->erstelle_formular ( "Haus ändern", NULL );
			iframe_start ();
			echo "<h1>Haus ändern</h1>";
			if (!request()->has('objekt_id')) {
				objekt_liste_links_aenderung ();
			}
			if (!request()->has('haus_id') && request()->has('objekt_id')) {
				$objekt_kurzname = objekt_kurzname ( request()->input('objekt_id') );
				hinweis_ausgeben ( "Objekt: $objekt_kurzname" );
				haus_liste_links_aenderung ( request()->input('objekt_id') );
			}
			if (request()->has('haus_id') && request()->has('objekt_id')) {
				$objekt_kurzname = objekt_kurzname ( request()->input('objekt_id') );
				$haus_kurzname = haus_strasse_nr ( request()->input('haus_id') );
				hinweis_ausgeben ( "Objekt: $objekt_kurzname" );
				hinweis_ausgeben ( "Haus: $haus_kurzname" );
				haus_aendern_formular ( request()->input('haus_id'));
				haeuser_liste_tabelle ( request()->input('objekt_id') ); // rechts die liste der häuser
			}
			
			iframe_end ();
			$form->ende_formular ();
			break;
		
		case "aendern" :
			$form = new mietkonto ();
			$form->erstelle_formular ( "Haus ändern", NULL );
			iframe_start ();
			echo "<h1>Haus ändern - Prozedur</h1>";
			foreach ( request()->request->all() as $key => $value ) {
				if (! isset ( $value )) {
					fehlermeldung_ausgeben ( "FEHLER: Alle Felder müssen ausgefüllt werden!" );
					echo "<a href=\"javascript:history.back()\">Zurück</a>\n";
					$error = 1;
					// echo "ERROR $key $value<br>";
					break;
				}
				// echo "$key $value<br>";
			}
			if (! isset ( $error )) {
				if (! request()->has('einheit_update')) {
					erstelle_formular ( 'haus_in_db', NULL ); // name, action
					$objekt_kurzname = objekt_kurzname ( request()->input("objekt_id") );
					echo "<tr><td><h1>Folgende Daten wurden übermittelt:\n</h1></td></tr>\n";
					echo "<tr><td><h2>Objektkurzname: $objekt_kurzname</h2></td></tr>\n";
					echo "<tr><td><h2>Haus: " . request()->input('haus_strasse') . " " . request()->input('haus_nummer') . " in " . request()->input('haus_plz') . " " . request()->input('haus_stadt') . "</h2></td></tr>\n";
					echo "<tr><td>";
					warnung_ausgeben ( "Sind Sie sicher, daß Sie das Haus " . request()->input('haus_strasse') . " " . request()->input('haus_nummer') . " im Objekt $objekt_kurzname  ändern wollen?" );
					echo "</td></tr>";
					erstelle_hiddenfeld ( "haus_dat", request()->input('haus_dat'));
					erstelle_hiddenfeld ( "haus_id", request()->input('haus_id') );
					erstelle_hiddenfeld ( "objekt_id", request()->input('objekt_id') );
					erstelle_hiddenfeld ( "haus_strasse", request()->input('haus_strasse') );
					erstelle_hiddenfeld ( "haus_nummer", request()->input('haus_nummer') );
					erstelle_hiddenfeld ( "haus_plz", request()->input('haus_plz') );
					erstelle_hiddenfeld ( "haus_stadt", request()->input('haus_stadt') );
					erstelle_hiddenfeld ( "haus_qm", request()->input('haus_qm') );
					erstelle_submit_button ( "einheit_update", "Speichern" ); // name, wert
					ende_formular ();
				}
				if (request()->has('einheit_update')) {
					$haus_dat = request()->input('haus_dat');
					deaktiviere_haus_dat ( $haus_dat );
					haus_geaendert_eintragen ( request()->input('haus_dat'), request()->input('haus_id'), request()->input('haus_strasse'), request()->input('haus_nummer'), request()->input('haus_stadt'), request()->input('haus_plz'), request()->input('haus_qm'), request()->input('objekt_id') );
				}
			}
			echo $error;
			iframe_end ();
			$form->ende_formular ();
			break;
		
		case "loeschen" :
			echo "<h1>Haus löschen</h1>";
			break;
	}
}
function haus_liste_links_aenderung($objekt_id) {
	$daten_rein = request()->input('daten_rein');
	$db_abfrage = "SELECT HAUS_DAT, HAUS_ID, HAUS_STRASSE, HAUS_NUMMER FROM HAUS WHERE OBJEKT_ID='$objekt_id' && HAUS_AKTUELL='1' ORDER BY HAUS_STRASSE, HAUS_NUMMER ASC";
	$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	$numrows = mysql_numrows ( $resultat );
	if ($numrows < 1) {
		echo "<h2 class=\"fehler\">Keine Häuser im ausgewählten Objekt</h2><br>\n";
		echo "Erst Haus im Objekt anlegen - <a href='" . route('legacy::haeuserform::index', ['daten_rein' => 'anlegen']) . "'>Hauseningabe hier&nbsp;</a>\n<br>\n";
	} else {
		while ( list ( $HAUS_DAT, $HAUS_ID, $HAUS_STRASSE, $HAUS_NUMMER ) = mysql_fetch_row ( $resultat ) ) {
			echo "<a class=\"objekt_links\" href='" . route('legacy::haeuserform::index', ['daten_rein' => $daten_rein, 'objekt_id' => $objekt_id, 'haus_id' => $HAUS_ID]) . "'>$HAUS_STRASSE $HAUS_NUMMER</a><br>\n";
		}
	}
}

?>
