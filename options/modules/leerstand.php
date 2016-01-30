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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/options/modules/leerstand.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

/* Klasse "formular" für Formularerstellung laden */
include_once ("classes/class_formular.php");
include_once ("includes/allgemeine_funktionen.php");

/* überprüfen ob Benutzer Zugriff auf das Modul hat */
if (! check_user_mod ( $_SESSION ['benutzer_id'], 'leerstand' )) {
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';
	die ();
}

include_once ('classes/class_leerstand.php');

include_once ("options/links/links.leerstand.php");

if (isset ( $_REQUEST ["daten"] )) {
	$daten = $_REQUEST ["daten"];
}
if (isset ( $_REQUEST ["option"] )) {
	$option = $_REQUEST ["option"];
}
if (isset ( $_REQUEST ["objekt_id"] )) {
	$objekt_id = $_REQUEST ['objekt_id'];
}
if (isset ( $_REQUEST ["haus_id"] )) {
	$haus_id = $_REQUEST ["haus_id"];
}
$link = "?daten=leerstand&option=objekt";
objekt_auswahl_liste ( $link );
if (isset ( $option )) {
	switch ($option) {
		
		case "objekt" :
			if (isset ( $_SESSION ['objekt_id'] )) {
				leerstand_objekt ( $_SESSION ['objekt_id'] );
			}
			break;
		
		case "objekt_pdf" :
			if (isset ( $_SESSION [objekt_id] )) {
				$objekt_id = $_SESSION [objekt_id];
				if (! empty ( $_REQUEST [monat] )) {
					$monat = $_REQUEST [monat];
				} else {
					$monat = date ( "m" );
				}
				
				if (! empty ( $_REQUEST [jahr] )) {
					$jahr = $_REQUEST [jahr];
				} else {
					$jahr = date ( "Y" );
				}
				$l = new leerstand ();
				$l->leerstand_objekt_pdf ( $objekt_id, $monat, $jahr );
			}
			break;
		
		case "test" :
			$a = new miete ();
			$a->berechnen ();
			break;
		
		default :
			break;
		
		case "projekt_pdf" :
			if (! empty ( $_REQUEST [einheit_id] )) {
				$l = new leerstand ();
				$l->pdf_projekt ( $_REQUEST [einheit_id] );
			} else {
				echo "Einheit wählen";
			}
			break;
		
		case "form_interessenten" :
			$l = new leerstand ();
			$l->form_interessent ();
			break;
		
		case "interessent_send" :
			// print_req();
			echo "<form>";
			if (! empty ( $_POST ['name'] ) && ! empty ( $_POST ['anschrift'] ) && ! empty ( $_POST ['w_datum'] )) {
				if (empty ( $_POST ['tel'] ) && ! empty ( $_POST ['email'] )) {
					die ( 'Telefonnr oder Email notwendig' );
				}
				$name = $_POST ['name'];
				$anschrift = $_POST ['anschrift'];
				$tel = $_POST ['tel'];
				$email = $_POST ['email'];
				$w_datum = $_POST ['w_datum'];
				$zimmer = $_POST ['zimmer'];
				$hinweis = $_POST ['hinweis'];
				$l = new leerstand ();
				if ($l->interessenten_speichern ( $name, $anschrift, $tel, $email, $w_datum, $zimmer, $hinweis )) {
					hinweis_ausgeben ( "$name gespeichert" );
				}
			} else {
				fehlermeldung_ausgeben ( 'Name, Anschrift und Wunschdatum sind notwendig!!!' );
			}
			echo "</form>";
			break;
		
		case "pdf_interessenten" :
			$l = new leerstand ();
			$l->pdf_interessentenliste ();
			break;
		
		case "interessentenliste" :
			$l = new leerstand ();
			$l->interessentenliste ();
			break;
		
		case "expose_pdf" :
			$einheit_id = $_REQUEST ['einheit_id'];
			if ($einheit_id) {
				$l = new leerstand ();
				$l->pdf_expose ( $einheit_id );
			} else {
				fehlermeldung_ausgeben ( 'Einheit wählen' );
			}
			break;
		
		case "termine" :
			$l = new leerstand ();
			if (! isset ( $_REQUEST ['vergangen'] )) {
				$l->liste_wohnungen_mit_termin ();
			} else {
				$l->liste_wohnungen_mit_termin ( '<' );
			}
			break;
		
		case "einladungen" :
			$einheit_id = $_REQUEST ['einheit_id'];
			$l = new leerstand ();
			$l->einladungen ( $einheit_id );
			break;
		
		case "form_expose" :
			$einheit_id = $_REQUEST ['einheit_id'];
			if ($einheit_id) {
				$l = new leerstand ();
				$l->form_exposedaten ( $einheit_id );
			} else {
				fehlermeldung_ausgeben ( 'Einheit wählen' );
			}
			break;
		
		case "expose_speichern" :
			$l = new leerstand ();
			// print_req();
			// print_r($_POST);
			if (! empty ( $_POST ['einheit_id'] ) && ! empty ( $_POST ['zimmer'] ) && ! empty ( $_POST ['balkon'] ) && ! empty ( $_POST ['expose_bk'] ) && ! empty ( $_POST ['expose_km'] ) && ! empty ( $_POST ['expose_hk'] ) && ! empty ( $_POST ['heizungsart'] ) && ! empty ( $_POST ['expose_frei'] ) && ! empty ( $_POST ['besichtigungsdatum'] ) && ! empty ( $_POST ['uhrzeit'] )) {
				$l->expose_aktualisieren ( $_POST ['einheit_id'], $_POST ['zimmer'], $_POST ['balkon'], $_POST ['expose_bk'], $_POST ['expose_km'], $_POST ['expose_hk'], $_POST ['heizungsart'], $_POST ['expose_frei'], $_POST ['besichtigungsdatum'], $_POST ['uhrzeit'] );
			} else {
				fehlermeldung_ausgeben ( "Dateneingabe unvollständig" );
			}
			break;
		
		/* Emails mit PDF-Expose versenden */
		case "sendpdfs" :
			echo "<form>";
			// print_req($_POST);
			$l = new leerstand ();
			$einheit_id = $_POST ['einheit_id'];
			if ($einheit_id) {
				
				$pdf_object = $l->pdf_expose ( $einheit_id, 1 ); // Rückgabe PDF-Object
				$b = new buchen ();
				$e = new einheit ();
				$e->get_einheit_info ( $einheit_id );
				$content = $pdf_object->output ();
				$monat = date ( "m" );
				$jahr = date ( "Y" );
				if (! file_exists ( "FOTOS/EINHEIT/$e->einheit_kurzname" )) {
					mkdir ( "FOTOS/EINHEIT/$e->einheit_kurzname", 0777 );
				}
				$b->save_file ( "$e->einheit_kurzname" . "-Expose", "FOTOS/EINHEIT", "$e->einheit_kurzname", $content, $monat, $jahr );
				@chmod ( "FOTOS/EINHEIT/$e->einheit_kurzname", 0777 );
				@chown ( "FOTOS/EINHEIT/$e->einheit_kurzname", 'nobody' );
				$pfad = "FOTOS/EINHEIT/$e->einheit_kurzname/" . "$e->einheit_kurzname" . "-Expose_" . $monat . "_" . $jahr . ".pdf";
				
				$mails = $_POST ['emails'];
				$anz = count ( $mails );
				// echo '<pre>';
				// print_r($anhang);
				// die();
				$files [] = $pfad;
				for($a = 0; $a < $anz; $a ++) {
					$email = $mails [$a];
					// $l->mail_att("$email","Einladung zur Wohnungsbesichtigung","Im Anhang ist eine Exposedatei",$anhang);
					$l->multi_attach_mail ( $email, $files, 'sivac@berlus.de', 'hausverwaltung.de - Einladung zur Wohnungsbesichtigung', "Wir laden Sie zur Wohnungsbesichtigung ein.\nIn der Anlage finden Sie das Exposé mit dem Besichtigunstermin.\n\nIhre Berlus Hausverwaltung\nFontanestr. 1\n14193 Berlin\nwww.hausverwaltung.de\n\nTel.: 030 89 78 44 77\nFax: 030 89 78 44 79\nEmail: info@berlus.de", 'Berlus HV' );
					echo "Email gesendet an $email<br>";
				}
			} else {
				fehlermeldung_ausgeben ( 'Einheit wählen' );
			}
			echo "</form>";
			break;
		
		case "interessenten_edit" :
			// print_req();
			if (! empty ( $_REQUEST ['id'] )) {
				$l = new leerstand ();
				$id = $_REQUEST ['id'];
				$l->form_edit_interessent ( $id );
			} else {
				hinweis_ausgeben ( "Bitte Namen wählen" );
			}
			break;
		
		case "interessenten_update" :
			echo "<form>";
			// print_req();
			if (isset ( $_POST ['delete'] )) {
				$id = $_POST ['delete'];
				$l = new leerstand ();
				
				if ($l->interessenten_deaktivieren ( $id )) {
					hinweis_ausgeben ( "Interessen gelöscht" );
				} else {
					fehlermeldung_ausgeben ( "Interessent konnte nicht gelöscht werden!" );
				}
			} else {
				if (! empty ( $_POST ['id'] ) && ! empty ( $_POST ['name'] ) && ! empty ( $_POST ['anschrift'] ) && ! empty ( $_POST ['tel'] ) && ! empty ( $_POST ['email'] ) && ! empty ( $_POST ['einzug'] ) && ! empty ( $_POST ['zimmer'] )) {
					$id = $_POST ['id'];
					$name = $_POST ['name'];
					$anschrift = $_POST ['anschrift'];
					$tel = $_POST ['tel'];
					$email = $_POST ['email'];
					$einzug = date_german2mysql ( $_POST ['einzug'] );
					$zimmer = $_POST ['zimmer'];
					$hinweis = $_POST ['hinweis'];
					$l = new leerstand ();
					if ($l->interessenten_updaten ( $id, $name, $anschrift, $tel, $email, $einzug, $zimmer, $hinweis )) {
						echo "$name wurde aktualisiert!";
						weiterleiten_in_sec ( "?daten=leerstand&option=interessentenliste", 2 );
					} else {
						fehlermeldung_ausgeben ( "$name konnte nicht aktualisiert werden." );
					}
				} else {
					echo "Bitte alle Datein eingeben!";
					weiterleiten_in_sec ( "?daten=leerstand&option=interessentenliste", 3 );
				}
			}
			echo "</form>";
			break;
		
		case "expose_foto_upload" :
			// print_req();
			$einheit_id = $_REQUEST ['einheit_id'];
			if ($einheit_id) {
				$l = new leerstand ();
				$l->form_foto_upload ( $einheit_id );
			}
			break;
		
		case "expose_foto_upload_check" :
			$e = new einheit ();
			$e->get_einheit_info ( $_POST ['einheit_id'] );
			// print_req($_FILES);
			define ( "MAX_SIZE", "10000" );
			$errors = 0;
			
			if (isset ( $_POST ['btn_sbm'] )) {
				// reads the name of the file the user submitted for uploading
				$images = $_FILES ['expose'] ['name'];
				// if it is not empty
				if (is_array ( $images )) {
					$anz = count ( $images );
					for($a = 0; $a < $anz; $a ++) {
						$dateiname = stripslashes ( $_FILES ['expose'] ['name'] [$a] );
						if (! $dateiname) {
							$datzahl = $a + 1;
							die ( "$datzahl Datei nicht gewählt!" );
						}
						$extension = strtolower ( getExtension ( $dateiname ) );
						if (($extension != "jpg") && ($extension != "jpeg")) {
							fehlermeldung_ausgeben ( "Dateiformat von $dateiname muss JPG oder JPEG sein!" );
							$errors = 1;
						} else {
							
							$size = filesize ( $_FILES ['expose'] ['tmp_name'] [$a] );
							
							if ($size > MAX_SIZE * 1024) {
								fehlermeldung_ausgeben ( "Maximal 10000kb!" );
								$errors = 1;
							}
							
							$datzahl = $a + 1;
							$newname = "FOTOS/EINHEIT/$e->einheit_kurzname/expose$datzahl.$extension";
							if (! file_exists ( "FOTOS/EINHEIT/$e->einheit_kurzname" )) {
								mkdir ( "FOTOS/EINHEIT/$e->einheit_kurzname" );
							}
							$kopiert = copy ( $_FILES ['expose'] ['tmp_name'] [$a], $newname );
							if (! $kopiert) {
								fehlermeldung_ausgeben ( "Datei $newname konnte nicht kopiert werden" );
								$errors = 1;
							}
							if (isset ( $_POST ['btn_sbm'] ) && ! $errors) {
								echo "<h1>Dateien wurden erfolgreich hochgeladen!</h1>";
							}
						}
					} // end for
				} else {
					fehlermeldung_ausgeben ( "Keine Dateien übermitelt" );
				}
			}
			
			break;
		
		case "sanierung" :
			if (! isset ( $_SESSION ['objekt_id'] )) {
				fehlermeldung_ausgeben ( "Objekt wählen" );
			} else {
				$le = new leerstand ();
				$le->sanierungsliste ( $_SESSION ['objekt_id'], 11, 250, 200 );
			}
			break;
		
		case "sanierung_wedding" :
			$le = new leerstand ();
			$le->sanierungsliste ( 1, 11, 250, 200 ); // BLOCK II
			$le->sanierungsliste ( 2, 11, 250, 200 ); // BLOCK III
			$le->sanierungsliste ( 3, 11, 250, 200 ); // BLOCK V
			
			break;
		
		case "fotos_upload" :
			if (! isset ( $_REQUEST ['einheit_id'] )) {
				fehlermeldung_ausgeben ( "Einheit wählen" );
			} else {
				$l = new leerstand ();
				$l->form_fotos_upload ( $_REQUEST ['einheit_id'] );
				$l->fotos_anzeigen_wohnung ( $_REQUEST ['einheit_id'], 'ANZEIGE', '10' );
			}
			
			break;
		
		case "foto_send_ajax" :
			
			ob_clean ();
			// print_r($_FILES);
			// die();
			
			if (isset ( $_FILES ['upload_file'] )) {
				$einheit_id = $_POST ['einheit_id_foto'];
				$e = new einheit ();
				$e->get_einheit_info ( $einheit_id );
				$orig_filename = $_FILES ['upload_file'] ['name'];
				$orig_file = $_FILES ['upload_file'] ['tmp_name'];
				$newname = "FOTOS/EINHEIT/$e->einheit_kurzname/ANZEIGE/$orig_filename";
				
				if (! file_exists ( "FOTOS/EINHEIT/$e->einheit_kurzname" )) {
					echo "NEMA ORDNERA";
					if (mkdir ( "FOTOS/EINHEIT/$e->einheit_kurzname", 0777 )) {
						mkdir ( "FOTOS/EINHEIT/$e->einheit_kurzname/ANZEIGE", 0777 );
					} else {
						// echo "NOK FOTOS";
					}
				} else {
					// echo "OKOK";
				}
				// die();
				
				/*
				 * if(!file_exists("FOTOS/EINHEIT/$e->einheit_kurzname/ANZEIGE")){
				 * echo "ORDER FOTOS/EINHEIT/$e->einheit_kurzname/ANZEIGE exisitiert nicht!";
				 * die();
				 * }
				 */
				if (! file_exists ( "FOTOS/EINHEIT/$e->einheit_kurzname/ANZEIGE" )) {
					// echo "NEMA ANZ ORDNERA";
					mkdir ( "FOTOS/EINHEIT/$e->einheit_kurzname/ANZEIGE", 0777 );
				} else {
					// echo "OK ANZ ORDNERA";
				}
				
				/* Bisher alles ok */
				
				if (file_exists ( "FOTOS/EINHEIT/$e->einheit_kurzname/ANZEIGE" )) {
					
					// $kopiert = copy($orig_file, $newname);
					if (file_exists ( $orig_file )) {
						// echo "tempfile da";
						// die();
						$thumbnail = new thumbnail ();
						$thumbnail->create ( $orig_file );
						$thumbnail->setQuality ( 80 );
						$thumbnail->resize ( "1024" );
						// $thumbnail->output();
						$thumbnail->save ( $orig_file, true );
						// die();
						if (! move_uploaded_file ( $orig_file, $newname )) {
							fehlermeldung_ausgeben ( "Datei $orig_file $newname konnte nicht kopiert werden" );
						} else {
							echo "$orig_filename hochgeladen";
							// echo "OK Datei $orig_filename $newname aus temp verschoben!";
						}
					}
				} else {
					// echo "ORDNER EXISTIERT NICHT und konnte nicht angelegt werden $orig_file";
					mkdir ( "FOTOS/EINHEIT/$e->einheit_kurzname/ANZEIGE", 0777 );
				}
			} else {
				echo "No file sent ...";
			}
			
			// print_req();
			/*
			 * print_r($_POST);
			 * print_r($_REQUEST);
			 * print_r($_FILES);
			 */
			die ();
			break;
		
		case "foto_loeschen" :
			ob_clean ();
			// print_req();
			if (isset ( $_POST ['filename'] )) {
				$filename = $_POST ['filename'];
				if (unlink ( $filename )) {
					echo "$filename geöscht";
				} else {
					echo "nicht gelöscht!";
				}
			}
			die ();
			
			break;
		
		case "fotos_f_anzeige" :
			if (! isset ( $_REQUEST ['einheit_id'] )) {
				fehlermeldung_ausgeben ( "Einheit wählen" );
			} else {
				$le = new leerstand ();
				$le->fotos_anzeigen_wohnung ( $_REQUEST ['einheit_id'], 'ANZEIGE', '10' );
			}
			break;
		
		case "vermietung_wedding" :
			
			$le = new leerstand ();
			// $le-> get_durchschnitt_nk($_SESSION['objekt_id'], null);
			// $le->vermietungsliste($_SESSION['objekt_id'], 11);
			$le->vermietungsliste ( 40, 11 );
			echo "<br><br><hr><br><br>";
			$le->vermietungsliste ( 1, 11 );
			echo "<br><br><hr><br><br>";
			$le->vermietungsliste ( 2, 11 );
			echo "<br><br><hr><br><br>";
			$le->vermietungsliste ( 3, 11 );
			
			break;
		
		case "vermietung" :
			
			if (! isset ( $_SESSION ['objekt_id'] )) {
				fehlermeldung_ausgeben ( "Objekt wählen" );
			} else {
				$le = new leerstand ();
				$le->vermietungsliste ( $_SESSION ['objekt_id'], 11 );
			}
			
			break;
		
		case "filter_setzen" :
			// echo "<pre>";
			// print_r($_POST);
			unset ( $_SESSION ['aktive_filter'] );
			
			if (isset ( $_POST ['Zimmer'] )) {
				$anz = count ( $_POST ['Zimmer'] );
				for($a = 0; $a < $anz; $a ++) {
					$wert = $_POST ['Zimmer'] [$a];
					$_SESSION ['aktive_filter'] ['zimmer'] [] = $wert;
				}
			}
			
			if (isset ( $_POST ['Balkon'] )) {
				$anz = count ( $_POST ['Balkon'] );
				for($a = 0; $a < $anz; $a ++) {
					$wert = $_POST ['Balkon'] [$a];
					$_SESSION ['aktive_filter'] ['balkon'] [] = $wert;
				}
			}
			
			if (isset ( $_POST ['Heizung'] )) {
				$anz = count ( $_POST ['Heizung'] );
				for($a = 0; $a < $anz; $a ++) {
					$wert = $_POST ['Heizung'] [$a];
					$_SESSION ['aktive_filter'] ['heizung'] [] = $wert;
				}
			}
			weiterleiten ( 'index.php?' . $_SERVER ['QUERY_STRING'] );
			// print_r($_SERVER);
			
			break;
		
		case "kontrolle_preise" :
			$l = new leerstand ();
			$l->kontrolle_preise ();
			break;
	}
}
function leerstand_finden($objekt_id) {
	$result = mysql_query ( "SELECT OBJEKT_KURZNAME, EINHEIT_ID, EINHEIT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, EINHEIT_QM, EINHEIT_LAGE
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
ORDER BY EINHEIT_KURZNAME ASC" );
	echo "<hr>SELECT OBJEKT_KURZNAME, EINHEIT_ID, EINHEIT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, EINHEIT_QM, EINHEIT_LAGE
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
ORDER BY EINHEIT_KURZNAME ASC";
	while ( $row = mysql_fetch_assoc ( $result ) )
		$my_arr [] = $row;
	return $my_arr;
}
function leerstand_finden_monat($objekt_id, $datum) {
	$result = mysql_query ( "SELECT OBJEKT_KURZNAME, EINHEIT_ID, EINHEIT_KURZNAME, HAUS_STRASSE, HAUS_NUMMER, EINHEIT_QM, EINHEIT_LAGE, TYP
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
GROUP BY EINHEIT_ID ORDER BY EINHEIT_KURZNAME ASC" );
	// echo "<pre>";
	
	while ( $row = mysql_fetch_assoc ( $result ) )
		$my_arr [] = $row;
		// print_r($my_arr);
	return $my_arr;
}
function leerstand_objekt($objekt_id) {
	$form = new formular ();
	$form->erstelle_formular ( "Leerstände", NULL );
	$b = new berlussimo_global ();
	$link = "?daten=leerstand&option=objekt&objekt_id=$objekt_id";
	
	if (isset ( $_REQUEST ['monat'] )) {
		$monat = $_REQUEST ['monat'];
	} else {
		$monat = date ( "m" );
	}
	if (isset ( $_REQUEST ['jahr'] )) {
		$jahr = $_REQUEST ['jahr'];
	} else {
		$jahr = date ( "Y" );
	}
	if ($monat && $jahr) {
		$l_tag = letzter_tag_im_monat ( $monat, $jahr );
		$datum = "$jahr-$monat-$l_tag";
	}
	
	$b->monate_jahres_links ( $jahr, $link );
	
	if (empty ( $datum )) {
		$leerstand = leerstand_finden ( $objekt_id );
	} else {
		$leerstand = leerstand_finden_monat ( $objekt_id, $datum );
	}
	$monat_name = monat2name ( $monat );
	echo "<table class=\"sortable\">";
	$link_pdf = "<a href=\"?daten=leerstand&option=objekt_pdf&objekt_id=$objekt_id&monat=$monat&jahr=$jahr\">PDF-Ansicht</a>";
	echo "<tr><td colspan=\"6\">$link_pdf</td></tr>";
	echo "<tr><td colspan=\"6\">Leerstand $monat_name $jahr</td></tr>";
	echo "</table>";
	echo "<table class=\"sortable\">";
	echo "<tr><th>Objekt</th><th>Einheit</th><th>TYP</th><th>Lage</th><th>Fläche</th><th>Link</th><th>Anschrift</th><th>PDF</th></tr>";
	
	$anzahl_leer = count ( $leerstand );
	$summe_qm = 0;
	for($a = 0; $a < $anzahl_leer; $a ++) {
		$einheit_id = $leerstand [$a] ['EINHEIT_ID'];
		$lage = $leerstand [$a] ['EINHEIT_LAGE'];
		$qm = $leerstand [$a] ['EINHEIT_QM'];
		$typ = $leerstand [$a] ['TYP'];
		$link_einheit = "<a href=\"?daten=uebersicht&anzeigen=einheit&einheit_id=$einheit_id\">" . $leerstand [$a] ['EINHEIT_KURZNAME'] . "</a>";
		$link_projekt_pdf = "<a href=\"?daten=leerstand&option=projekt_pdf&einheit_id=$einheit_id\"><img src=\"css/pdf.png\"></a>";
		$link_expose_pdf = "<a href=\"?daten=leerstand&option=expose_pdf&einheit_id=$einheit_id\"><img src=\"css/pdf2.png\"></a>";
		$link_expose_eingabe = "<a href=\"?daten=leerstand&option=form_expose&einheit_id=$einheit_id\">Bearbeiten</a>";
		$link_fotos = "<a href=\"?daten=leerstand&option=expose_foto_upload&einheit_id=$einheit_id\">Fotos hochladen</a>";
		echo "<tr><td>" . $leerstand [$a] ['OBJEKT_KURZNAME'] . "</td><td>$link_einheit</td><td>$typ</td><td>$lage</td><td>$qm m²</td><td><a href=\"?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_neu\">Vermieten</td></td><td>" . $leerstand [$a] ['HAUS_STRASSE'] . " " . $leerstand [$a] ['HAUS_NUMMER'] . "</td><td>$link_projekt_pdf Projekt<br>$link_expose_pdf Expose</td></tr>";
		$summe_qm += $qm;
	}
	echo "<tr><td></td><td></td><td></td><td></td><td>$summe_qm m²</td><td></td><td></td><td></td></tr>";
	echo "</table>";
	$form->ende_formular ();
}
function objekt_auswahl_liste($link) {
	if (isset ( $_REQUEST ['objekt_id'] ) && ! empty ( $_REQUEST ['objekt_id'] )) {
		$_SESSION ['objekt_id'] = $_REQUEST ['objekt_id'];
	}
	
	$mieten = new mietkonto ();
	$mieten->erstelle_formular ( "Objekt auswählen...", NULL );
	if (isset ( $_SESSION ['objekt_id'] )) {
		$objekt_kurzname = new objekt ();
		$objekt_kurzname->get_objekt_name ( $_SESSION ['objekt_id'] );
		echo "<p>&nbsp;<b>Ausgewähltes Objekt</b> -> $objekt_kurzname->objekt_name ->";
	} else {
		echo "<p>&nbsp;<b>Objekt auswählen</b>";
	}
	echo "<p class=\"objekt_auswahl\">";
	$objekte = new objekt ();
	$objekte_arr = $objekte->liste_aller_objekte ();
	$anzahl_objekte = count ( $objekte_arr );
	$c = 0;
	for($i = 0; $i < $anzahl_objekte; $i ++) {
		echo "<a class=\"objekt_auswahl_buchung\" href=\"$link&objekt_id=" . $objekte_arr [$i] ['OBJEKT_ID'] . "\">" . $objekte_arr [$i] ['OBJEKT_KURZNAME'] . "</a>&nbsp;";
		$c ++;
		if ($c == 15) {
			echo "<br>";
			$c = 0;
		}
	}
	echo "</p>";
	$mieten->ende_formular ();
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

?>
