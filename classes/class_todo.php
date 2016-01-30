<?php
/*
 * Created on / Erstellt am : 13.01.2011
 * Author: Sivac
 */

/**
 * BERLUSSIMO
 *
 * Hausverwaltungssoftware
 *
 *
 * @copyright Copyright (c) 2010, Berlus GmbH, Fontanestr. 1, 14193 Berlin
 * @link http://www.berlus.de
 * @author Sanel Sivac & Wolfgang Wehrheim
 *         @contact software(@)berlus.de
 * @license http://www.gnu.org/licenses/agpl.html AGPL Version 3
 *         
 * @filesource $HeadURL$
 * @version $Revision$
 *          @modifiedby $LastChangedBy$
 *          @lastmodified $Date$
 *         
 */

/* Allgemeine Funktionsdatei laden */
include_once ("includes/allgemeine_funktionen.php");

/* Klasse "formular" für Formularerstellung laden */
include_once ("classes/class_formular.php");
include_once ("classes/berlussimo_class.php");
include_once ("classes/class_wirtschafts_e.php");
include_once ("classes/class_partners.php");
include_once ("classes/mietzeit_class.php");
include_once ("classes/class_mietentwicklung.php");
include_once ("classes/class_benutzer.php");
class todo {
	function form_neue_baustelle($t_id = NULL) {
		$f = new formular ();
		$bb = new buchen ();
		$f->erstelle_formular ( 'Neue Baustelle erstellen', '' );
		$f->text_feld ( 'Bezeichnung', 'bau_bez', '', 50, 'bau_bez', '' );
		$f->hidden_feld ( 'option', 'neue_baustelle' );
		$p = new partners ();
		$p->partner_dropdown ( 'Rechnungsempfänger wählen', 'p_id', 'p_id' );
		$f->send_button ( 'btn_sndb', 'Erstellen' );
		$f->ende_formular ();
	}
	function neue_baustelle_speichern($bau_bez, $p_id) {
		$last_id = last_id2 ( 'BAUSTELLEN_EXT', 'ID' ) + 1;
		$db_abfrage = "INSERT INTO BAUSTELLEN_EXT VALUES (NULL, '$last_id', '$bau_bez', '$p_id', '1','1')";
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		return true;
	}
	function baustellen_liste_arr($aktiv = 1) {
		$db_abfrage = "SELECT * FROM BAUSTELLEN_EXT WHERE AKTUELL='1' && AKTIV='$aktiv' ORDER BY BEZ, PARTNER_ID";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_arr [] = $row;
			return $my_arr;
		}
	}
	function baustellen_liste($aktiv = 1) {
		$arr = $this->baustellen_liste_arr ( $aktiv );
		if (is_array ( $arr )) {
			$anz = count ( $arr );
			echo "<table class=\"sortable\">";
			echo "<thead><tr><th>BAUSTELLE</th><th>RECHNUNGSEMPFÄNGER</th><th>OPTIONEN</th></tr></thead>";
			for($a = 0; $a < $anz; $a ++) {
				$bau_id = $arr [$a] ['ID'];
				$bez = $arr [$a] ['BEZ'];
				$p_id = $arr [$a] ['PARTNER_ID'];
				$p = new partners ();
				$p->get_partner_name ( $p_id );
				$partner_name = $p->partner_name;
				if ($aktiv == '1') {
					$link = "<a href=\"?daten=todo&option=baustelle_deaktivieren&bau_id=$bau_id\">Deaktivieren</a>";
				} else {
					$link = "<a href=\"?daten=todo&option=baustelle_aktivieren&bau_id=$bau_id\">Aktivieren</a>";
				}
				echo "<tr><td>$bez</td><td>$partner_name</td><td>$link</td></tr>";
			}
			echo "</table>";
		}
	}
	function baustelle_aktivieren($b_id, $aktiv = '1') {
		$db_abfrage = "UPDATE BAUSTELLEN_EXT SET AKTIV='$aktiv' WHERE ID='$b_id'";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
	}
	function form_neue_aufgabe($t_id = NULL, $typ = 'Benutzer') {
		$bb = new buchen ();
		$f = new formular ();
		$f->erstelle_formular ( 'Neues Projekt oder Aufgabe', '' );
		$f->hidden_feld ( 'typ', $typ );
		if ($t_id != NULL) {
			$projekt_name = $this->get_text ( $t_id );
			$f->fieldset ( "$projekt_name -> Neue Aufgabe erstellen", 'na' );
		} else {
			$f->fieldset ( 'Neues Projekt erstellen', 'na' );
		}
		$f->text_bereich ( 'Beschreibung', 'text', '', 5, 20, 'aufgabe' );
		
		// if($t_id == NULL){
		if (isset ( $_REQUEST ['kos_typ'] ) && ! empty ( $_REQUEST ['kos_typ'] ) && isset ( $_REQUEST ['kos_id'] ) && ! empty ( $_REQUEST ['kos_id'] )) {
			$f->hidden_feld ( 'kostentraeger_typ', $_REQUEST ['kos_typ'] );
			$f->hidden_feld ( 'kostentraeger_id', $_REQUEST ['kos_id'] );
		} else {
			$js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
			$bb->dropdown_kostentreager_typen ( 'Kostenträgertyp', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ );
			$js_id = "";
			$bb->dropdown_kostentreager_ids ( 'Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id );
		}
		// }else{
		// $this->get_kos_typ_id($t_id);
		// $f->hidden_feld('kostentraeger_typ', $this->kos_typ);
		// $f->hidden_feld('kostentraeger_id', $this->kos_id);
		// echo "BABBABA $this->kos_bez $this->kos_typ $this->kos_id<br>";
		// }
		
		if ($typ == 'Benutzer') {
			$b = new benutzer ();
			$b->dropdown_benutzer ();
		}
		if ($typ == 'Partner') {
			$pp = new partners ();
			$pp->partner_dropdown ( 'Partner wählen', 'benutzer_id', 'benutzer_id' );
		}
		
		$f->datum_feld ( 'Anzeigen ab', 'anzeigen_ab', date ( "d.m.Y" ), 'dat_a' );
		$this->dropdown_akut ();
		if ($t_id != NULL) {
			$f->text_feld ( 'Wert in EUR', 'wert_eur', '0,00', '10', 'wert_eur', '' );
		} else {
			
			$f->hidden_feld ( 'wert_eur', '0,00' );
		}
		
		$f->send_button ( 'submit_n', 'Speichern' );
		$f->fieldset_ende ();
		if (isset ( $_POST ['submit_n'] )) {
			// print_r($_POST);
			if (! empty ( $_REQUEST [benutzer_id] ) && ! empty ( $_REQUEST [submit_n] ) && ! empty ( $_REQUEST [anzeigen_ab] ) && ! empty ( $_REQUEST [text] )) {
				$last_id = last_id2 ( 'TODO_LISTE', 'T_ID' ) + 1;
				$anz_ab = date_german2mysql ( $_REQUEST ['anzeigen_ab'] );
				$typ = $_REQUEST ['typ'];
				$wert_eur = nummer_komma2punkt ( $_REQUEST ['wert_eur'] );
				$kostentraeger_typ = $_REQUEST ['kostentraeger_typ'];
				$kostentraeger_id = $_REQUEST ['kostentraeger_id'];
				// echo "<br>------$kostentraeger_typ $kostentraeger_id--------<br>";
				if (! is_numeric ( $kostentraeger_id )) {
					$kostentraeger_bez = $_REQUEST ['kostentraeger_id'];
					$kostentraeger_id = $bb->kostentraeger_id_ermitteln ( $kostentraeger_typ, $kostentraeger_bez );
					$mail_subj = "Neues Projekt $_REQUEST[text]";
				} else {
					$r = new rechnung ();
					$kostentraeger_bez = $r->kostentraeger_ermitteln ( $kostentraeger_typ, $kostentraeger_id );
					$mail_subj = "Neue Aufgabe ";
				}
				// echo "$kostentraeger_bez $kostentraeger_typ $kostentraeger_id<br>";
				// die();
				
				$db_abfrage = "INSERT INTO TODO_LISTE VALUES (NULL, '$last_id', '$t_id', '$_REQUEST[text]', NULL, '$anz_ab','$typ', '$_REQUEST[benutzer_id]','$_SESSION[benutzer_id]', '0','$_REQUEST[akut]','$_REQUEST[kostentraeger_typ]','$kostentraeger_id', '$wert_eur','1')";
				echo $db_abfrage;
				$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
				$mail_text = "Beschreibung: " . $_REQUEST ['text'] . "\n";
				$mail_text .= "Kostentraeger: $kostentraeger_bez\n";
				// $mail_text .= $_SESSION['benutzer_id'];
				if ($kostentraeger_typ == 'Einheit') {
					$mail_text .= "\n" . str_replace ( '<br>', "\n", $this->kontaktdaten_anzeigen_mieter ( $kostentraeger_id ) );
				}
				if ($_REQUEST ['benutzer_id'] == '29') {
					if (mail ( 'goehler@berlus.de', "$mail_subj", $mail_text )) {
						// if(mail('sivac@berlus.de', 'Mein Betreff', 'NACHRICHT')){
						echo "EMAIL GESENDET!";
					} else {
						echo "EMAIL NICHT GESENDET!";
					}
				}
				// echo "GESPEICHERT!";
				$text = $_REQUEST ['text'];
				// hinweis_ausgeben("Ihre Eingabe <b>$text</b> wurde unter MEINE PROJEKTE gespeichert!!!");
				ob_clean ();
				weiterleiten ( "?daten=todo&option=pdf_auftrag&proj_id=$last_id" );
			}
		}
		$f->ende_formular ();
	}
	function dropdown_akut($akut = 'NEIN') {
		echo "<label for=\"akut\">Akut / Wichtig</label><select id=\"akut\" name=\"akut\" size=\"1\">";
		if ($akut == 'NEIN') {
			echo "<option value=\"Nein\" selected>Nein</option>";
			echo "<option value=\"Ja\">Ja</option>";
		} else {
			echo "<option value=\"Nein\">Nein</option>";
			echo "<option value=\"Ja\" selected>Ja</option>";
		}
		echo "</select>";
	}
	function dropdown_erledigt($erl = 0) {
		echo "<label for=\"status\">Status</label><select id=\"status\" name=\"status\" size=\"1\">";
		if ($erl == 0) {
			echo "<option value=\"1\">Erledigt</option>";
			echo "<option value=\"0\"  selected>Offen</option>";
		} else {
			echo "<option value=\"1\" selected>Erledigt</option>";
			echo "<option value=\"0\" >Offen</option>";
		}
		echo "</select>";
	}
	function rss_feed_ok_test($benutzer_id) {
		ob_clean ();
		echo '<?xml version="1.0" encoding="ISO-8859-1" ?>
<rss version="2.0">';
		
		echo "<channel>\n";
		echo '<title>Meine Projekte PHP</title>
  <link>http://www.berlus.de</link>
  <description>Meine Termine und Projekte</description>';
		
		echo '<item>
    <title>Projekt 1</title>
    <link>http://www.w3schools.com/rss</link>
    <description>
	<![CDATA[
	<!-- ab hier html-->
	<b>SANEL</b><br>SANELA
		
	]]>
	</description>
    <enclosure url="http://berlus.de" type="video/mpeg"></enclosure>
  </item>';
		
		echo "</channel>\n";
		echo "</rss>";
		die ();
	}
	function rss_feed($benutzer_id) {
		$url = "http://berlussimo.no-ip.biz:5000/berlussimo-aktiv/";
		ob_clean ();
		header ( "Content-Type: application/xml; charset=ISO-8859-1" );
		echo '<?xml version="1.0" encoding="ISO-8859-1" ?>';
		echo "\n";
		echo '<rss version="2.0">';
		echo "\n";
		echo '<?xml-stylesheet type="text/css" href="css/rss.css" ?>';
		
		echo "<channel>\n";
		
		$my_proj_id_arr = $this->get_my_projekt_arr ( $benutzer_id );
		if (! is_array ( $my_proj_id_arr )) {
			// die("Keine Projekte und Aufgaben für Sie vohanden!");
			echo "<title>Keine Projekte und Aufgaben für Sie!</title>\n";
			echo "<link>http://berlussimo-1/berlussimo_workspace/sivac/berlussimo/rss.php</link>\n";
			echo "<description>Sie haben keine Projekte und Aufgaben</description>\n";
		} else {
			$b = new benutzer ();
			$b->get_benutzer_infos ( $benutzer_id );
			echo "<title>Projekte von Benutzer: $b->benutzername</title>\n";
			echo "<link>http://berlussimo.no-ip.biz:5000/berlussimo_workspace/sivac/berlussimo/</link>\n";
			echo "<description>Ihre Projekte und Aufgaben</description>\n";
			$anz_p = count ( $my_proj_id_arr );
			
			for($p = 0; $p < $anz_p; $p ++) {
				$proj_id = $my_proj_id_arr [$p] ['PROJ_ID'];
				$result = mysql_query ( "SELECT * FROM TODO_LISTE WHERE T_ID='$proj_id' && AKTUELL='1' ORDER BY ANZEIGEN_AB ASC" );
				$anz = mysql_numrows ( $result );
				if ($anz) {
					$pz = $p + 1;
					$z1 = 0;
					while ( $row = mysql_fetch_assoc ( $result ) ) {
						$z1 ++;
						$t_id = $row ['T_ID'];
						$text = $row ['TEXT'];
						$edit_text = "<a href=\"?daten=todo&option=edit&t_id=$t_id\">$text</a>";
						$anzeigen_ab = date_mysql2german ( $row ['ANZEIGEN_AB'] );
						$erledigt = $row ['ERLEDIGT'];
						$verfasser_id = $row ['VERFASSER_ID'];
						$b = new benutzer ();
						$b->get_benutzer_infos ( $verfasser_id );
						$verfasser_name = $b->benutzername;
						$beteiligt_id = $row ['BENUTZER_ID'];
						$b->get_benutzer_infos ( $beteiligt_id );
						$beteiligt_name = $b->benutzername;
						$o = new objekt ();
						$t_vergangen = $o->tage_berechnen_bis_heute ( $anzeigen_ab );
						echo "<item>\n";
						echo "<title>$text</title>\n";
						echo "<description>";
						echo '<![CDATA[';
						echo "$t_vergangen T | <b>Verfasst:</b> $verfasser_name | <b>Beteiligt:</b> $beteiligt_name";
						
						/* Anfang CDATA */
						echo "<hr>";
						
						$u_aufgaben_arr = $this->get_unteruafgaben_arr ( $t_id );
						$anz = count ( $u_aufgaben_arr );
						if ($anz) {
							$z2 = 0;
							for($a = 0; $a < $anz; $a ++) {
								$z2 ++;
								$u_t_id = $u_aufgaben_arr [$a] ['T_ID'];
								$u_text = $u_aufgaben_arr [$a] ['TEXT'];
								$link_aendern = "<a href=\"$url?daten=todo&amp;option=edit&amp;t_id=$u_t_id\">$u_text</a>";
								echo "<b>$z2.</b> $link_aendern<hr>";
							}
						}
						
						/* End CDATA */
						echo ']]>';
						echo "</description>\n";
						
						echo "<enclosure url=\"http://berlus.de\" type=\"video/mpeg\"></enclosure>";
						echo "</item>\n";
					}
				}
			}
		}
		
		echo '<item>
    <title>Projekt 1</title>
    <link>http://www.w3schools.com/rss</link>
    <description>
	<![CDATA[
	<!-- ab hier html-->
	<b>SANEL</b><br>SANELA
	<head>
	<script type="text/javascript">
	function mee(){
	alert("Hallo Welt!");
	}
	</script>
	</head>
	<form>
	<input type="button"  width="60" value=" LÖSCHEN " onclick="mee();"/>	
	
	</form>   
   	
	]]>
	
	</description>
    <enclosure url="http://berlus.de" type="video/mpeg"></enclosure>
  </item>';
		
		echo "</channel>\n";
		echo "</rss>";
		die ();
	}
	function rss_feed_OK($benutzer_id) {
		ob_clean ();
		header ( "Content-Type: application/xml; charset=ISO-8859-1" );
		echo '<?xml version="1.0" encoding="ISO-8859-1" ?>';
		/* echo "<?xml-stylesheet type=\"text/css\" href=\"css/rss.css\" ?>\n"; */
		echo "<rss version=\"2.0\">\n";
		echo "<channel>\n";
		
		$my_proj_id_arr = $this->get_my_projekt_arr ( $benutzer_id );
		if (! is_array ( $my_proj_id_arr )) {
			// die("Keine Projekte und Aufgaben für Sie vohanden!");
			echo "<title>Keine Projekte und Aufgaben für Sie!</title>\n";
			echo "<link>http://berlussimo-1/berlussimo_workspace/sivac/berlussimo/rss.php</link>\n";
		} else {
			$b = new benutzer ();
			$b->get_benutzer_infos ( $benutzer_id );
			echo "<title>Aufgaben von Benutzer: $b->benutzername</title>\n";
			echo "<link>http://berlussimo-1/berlussimo_workspace/sivac/berlussimo/rss.php</link>\n";
			echo "<description>Angezeigt werden Projekte und dazugehürige Aufgaben!</description>\n";
			$anz_p = count ( $my_proj_id_arr );
			
			for($p = 0; $p < $anz_p; $p ++) {
				$proj_id = $my_proj_id_arr [$p] ['PROJ_ID'];
				$result = mysql_query ( "SELECT * FROM TODO_LISTE WHERE T_ID='$proj_id' && AKTUELL='1' ORDER BY ANZEIGEN_AB ASC" );
				
				$anz = mysql_numrows ( $result );
				if ($anz) {
					$pz = $p + 1;
					$f = new formular ();
					echo "<item>\n";
					echo "<title>Projekt $pz</title>\n";
					$link_mp = "http://berlussimo-1/berlussimo_workspace/sivac/berlussimo/?daten=todo";
					echo "<link>$link_mp</link>";
					// echo "</item>\n";
					$z1 = 0;
					while ( $row = mysql_fetch_assoc ( $result ) ) {
						// echo "<thead><tr><th></th><th>TAGE</th><th>DATUM</th><th>PROJEKT</th><th>VERFASSER</th><th>VERANTWORTLICH</th><th>ZUORDNUNG</th><th>STATUS</th></tr><thead>";
						$z1 ++;
						$t_id = $row ['T_ID'];
						$text = $row ['TEXT'];
						$edit_text = "<a href=\"?daten=todo&option=edit&t_id=$t_id\">$text</a>";
						$anzeigen_ab = date_mysql2german ( $row ['ANZEIGEN_AB'] );
						$erledigt = $row ['ERLEDIGT'];
						$verfasser_id = $row ['VERFASSER_ID'];
						$b = new benutzer ();
						$b->get_benutzer_infos ( $verfasser_id );
						$verfasser_name = $b->benutzername;
						$beteiligt_id = $row ['BENUTZER_ID'];
						$b = new benutzer ();
						$b->get_benutzer_infos ( $beteiligt_id );
						$beteiligt_name = $b->benutzername;
						if ($erledigt == '1') {
							$erledigt = 'erledigt';
						} else {
							$erledigt = "offen";
						}
						
						$link_erledigt = "<a href=\"\">";
						$o = new objekt ();
						$t_vergangen = $o->tage_berechnen_bis_heute ( $anzeigen_ab );
						
						$akut = $row ['AKUT'];
						if ($akut == 'JA') {
							$c1 = 3;
						} else {
							$c1 = 4;
						}
						
						$kos_typ = $row [KOS_TYP];
						$kos_id = $row [KOS_ID];
						$r = new rechnung ();
						$kos_bez = $r->kostentraeger_ermitteln ( $kos_typ, $kos_id );
						
						// echo "<tr class=\"zeile$c1\"><td>$z1</td><td>$t_vergangen T</td><td>$anzeigen_ab</td><td>$edit_text</td>";
						// echo "<td>$verfasser_name</td><td>$beteiligt_name</td><td>$kos_bez</td>";
						// echo "<item>\n";
						// echo "<title>$text</title>\n";
						echo "<description><![CDATA[ Vergangen: $t_vergangen T | Ab: $anzeigen_ab | Erfasser:$verfasser_name | Beteiligt: $beteiligt_name | Zuweisung:$kos_bez ";
						if ($erledigt == 'erledigt') {
							echo "| Status: $erledigt |";
						} else {
							echo "| Status:$erledigt |";
						}
						// $link_1 = "http://berlussimo-1/berlussimo_workspace/sivac/berlussimo/?daten=todo&amp;option=edit&amp;t_id=$t_id";
						echo "Akut: $akut $link_1 ]]</description>\n";
						// $link_1 = "http://berlussimo-1/berlussimo_workspace/sivac/berlussimo/?daten=todo&amp;option=edit&amp;t_id=$t_id";
						// echo "<link>$link_1</link>\n";
						echo "</item>\n";
						$link_neue_u_aufgabe = "<a href=\"?daten=todo&option=neues_projekt&t_id=$t_id\">Neue Aufgabe erstellen</a>";
						// echo "<tr class=\"zeile6\"><td colspan=\"4\">$link_neue_u_aufgabe</td><td colspan=\"4\"></td></tr>";
						
						$u_aufgaben_arr = $this->get_unteruafgaben_arr ( $t_id );
						$anz = count ( $u_aufgaben_arr );
						if ($anz) {
							$z2 = 0;
							// echo "<tfoot><tr><th></th><th>TAGE</th><th>DATUM</th><th>AUFGABE</th><th>VERFASSER</th><th>VERANTWORTLICH</th><th>ZUORDNUNG</th><th>STATUS</th></tr></tfoot>";
							for($a = 0; $a < $anz; $a ++) {
								$z2 ++;
								$u_t_id = $u_aufgaben_arr [$a] ['T_ID'];
								$u_text = $u_aufgaben_arr [$a] ['TEXT'];
								$u_edit_text = "<a href=\"?daten=todo&option=edit&t_id=$u_t_id\">$u_text</a>";
								$u_anzeigen_ab = date_mysql2german ( $u_aufgaben_arr [$a] ['ANZEIGEN_AB'] );
								$u_erledigt = $u_aufgaben_arr [$a] ['ERLEDIGT'];
								if ($u_erledigt == '1') {
									$u_erledigt = 'erledigt';
								} else {
									$u_erledigt = "offen";
								}
								
								$u_verfasser_id = $u_aufgaben_arr [$a] ['VERFASSER_ID'];
								$b = new benutzer ();
								$b->get_benutzer_infos ( $u_verfasser_id );
								$u_verfasser_name = $b->benutzername;
								$u_beteiligt_id = $u_aufgaben_arr [$a] ['BENUTZER_ID'];
								$b = new benutzer ();
								$b->get_benutzer_infos ( $u_beteiligt_id );
								$u_beteiligt_name = $b->benutzername;
								$u_akut = $u_aufgaben_arr [$a] ['AKUT'];
								if ($u_akut == 'JA') {
									$c = 3;
								} else {
									$c = 5;
								}
								
								$u_kos_typ = $u_aufgaben_arr [$a] [KOS_TYP];
								$u_kos_id = $u_aufgaben_arr [$a] [KOS_ID];
								$r = new rechnung ();
								$u_kos_bez = $r->kostentraeger_ermitteln ( $u_kos_typ, $u_kos_id );
								
								$u_t_vergangen = $o->tage_berechnen_bis_heute ( $u_anzeigen_ab );
								echo "<item>\n";
								echo "<title>$pz.$z2: $u_text -> Vergangen: $u_t_vergangen T | Ab: $u_anzeigen_ab</title>\n";
								echo "<description>Erfasser:$u_verfasser_name | Beteiligt: $u_beteiligt_name | Zuweisung:$u_kos_bez ";
								
								// echo "<description>[CDATA[ This is the description. ]]</description>\n";
								
								// echo "<tr class=\"zeile$c\"><td>$z1.$z2</td><td>$u_t_vergangen T</td><td>$u_anzeigen_ab</td><td>$u_edit_text</td>";
								// echo "<td>$u_verfasser_name</td><td>$u_beteiligt_name</td><td>$u_kos_bez</td>";
								if ($u_erledigt == 'erledigt') {
									echo "| Status: $erledigt |";
								} else {
									echo "| Status: $erledigt |";
								}
								// echo "</tr>";
								echo "Akut: $u_akut </description>\n";
								$link_2 = "http://berlussimo-1/berlussimo_workspace/sivac/berlussimo/?daten=todo&amp;option=edit&amp;t_id=$u_t_id";
								echo "<link>$link_2</link>\n";
								echo "</item>\n";
							}
						}
					}
				}
			}
		} // end else
		echo "</channel>\n";
		echo "</rss>\n";
		// echo "</rss>";
		die (); // wichtig
	}
	function todo_liste($benutzer_id = '0', $erl = '0') {
		
		// form_neue_aufgabe();
		if ($benutzer_id == '') {
			$benutzer_id = '0';
		}
		// $result = mysql_query ("SELECT * FROM TODO_LISTE WHERE UE_ID='0' && (BENUTZER_ID='$benutzer_id' OR VERFASSER_ID='$benutzer_id') && ANZEIGEN_AB <= DATE_FORMAT(NOW(), '%Y-%m-%d' ) && AKTUELL='1' ORDER BY ANZEIGEN_AB ASC");
		$my_proj_id_arr = $this->get_my_projekt_arr ( $benutzer_id, $erl );
		// print_r($my_proj_id_arr);
		if (! is_array ( $my_proj_id_arr )) {
			die ( 'Keine Projekte und Aufgaben für Sie vohanden!' );
		} else {
			$anz_p = count ( $my_proj_id_arr );
			for($p = 0; $p < $anz_p; $p ++) {
				$proj_id = $my_proj_id_arr [$p] ['PROJ_ID'];
				$result = mysql_query ( "SELECT * FROM TODO_LISTE WHERE T_ID='$proj_id' && AKTUELL='1' ORDER BY ANZEIGEN_AB ASC" );
				
				$anz = mysql_numrows ( $result );
				if ($anz) {
					$pz = $p + 1;
					$f = new formular ();
					
					$f->fieldset ( "Projekt $pz", 'ana' );
					// echo "<table class=\"sortable\">";
					
					$z1 = 0;
					$f->erstelle_formular ( 'FF', null );
					while ( $row = mysql_fetch_assoc ( $result ) ) {
						// echo "<table>";
						echo "<table class=\"sortable\">";
						echo "<thead><tr><th>OPT</th><th>TAGE</th><th>DATUM</th><th>PROJEKT</th><th>VERFASSER</th><th>VERANTWORTLICH</th><th>ZUORDNUNG</th><th>STATUS</th></tr></thead>";
						$z1 ++;
						$t_dat = $row ['T_DAT'];
						$t_id = $row ['T_ID'];
						$link_pdf = "<a href=\"?daten=todo&option=pdf_projekt&proj_id=$t_id\"><img src=\"css/pdf.png\"></a>";
						$link_pdf_1 = "<a href=\"?daten=todo&option=pdf_auftrag&proj_id=$t_id\"><img src=\"css/pdf.png\"></a>";
						$text = $row ['TEXT'];
						$edit_text = "<a href=\"?daten=todo&option=edit&t_id=$t_id\">$text</a>";
						$anzeigen_ab = date_mysql2german ( $row ['ANZEIGEN_AB'] );
						$erledigt = $row ['ERLEDIGT'];
						$verfasser_id = $row ['VERFASSER_ID'];
						$b = new benutzer ();
						$b->get_benutzer_infos ( $verfasser_id );
						$verfasser_name = $b->benutzername;
						$benutzer_typ = $row ['BENUTZER_TYP'];
						if ($benutzer_typ == 'Benutzer' or empty ( $benutzer_typ )) {
							$beteiligt_id = $row ['BENUTZER_ID'];
							$b = new benutzer ();
							$b->get_benutzer_infos ( $beteiligt_id );
							$beteiligt_name = $b->benutzername;
						}
						if ($benutzer_typ == 'Partner') {
							$partner_id = $row ['BENUTZER_ID'];
							$pp = new partners ();
							$pp->get_partner_info ( $partner_id );
							$beteiligt_name = $pp->partner_name;
						}
						
						if ($erledigt == '1') {
							$erledigt = 'erledigt';
						} else {
							$erledigt = "offen";
						}
						
						$link_erledigt = "<a href=\"\">";
						$o = new objekt ();
						$t_vergangen = $o->tage_berechnen_bis_heute ( $anzeigen_ab );
						
						$akut = $row ['AKUT'];
						if ($akut == 'JA') {
							$c1 = 3;
						} else {
							$c1 = 4;
						}
						
						$kos_typ = $row ['KOS_TYP'];
						$kos_id = $row ['KOS_ID'];
						$r = new rechnung ();
						$kos_bez = $r->kostentraeger_ermitteln ( $kos_typ, $kos_id );
						
						echo "<tr class=\"zeile$c1\"><td>";
						$f->check_box_js ( 't_dats[]', $t_dat, 'Erledigt', null, null );
						echo "</td><td>$t_vergangen T</td><td>$anzeigen_ab</td><td>$edit_text $link_pdf</td>";
						echo "<td>$verfasser_name</td><td>$beteiligt_name</td><td>$kos_bez<br>";
						if ($kos_typ == 'Einheit') {
							$kontaktdaten_mieter = $this->kontaktdaten_anzeigen_mieter ( $kos_id );
							echo $kontaktdaten_mieter;
						}
						echo "</td>";
						if ($erledigt == 'erledigt') {
							echo "<td class=\"gruen\"><b>$erledigt</b>";
						} else {
							echo "<td class=\"rot\">$erledigt";
						}
						echo "$link_pdf_1 </td>";
						echo "</tr>";
						$link_neue_u_aufgabe = "<a href=\"?daten=todo&option=neues_projekt&t_id=$t_id\">Neue Aufgabe erstellen</a>";
						// echo "<tr class=\"zeile6\"><td colspan=\"4\">$link_neue_u_aufgabe</td><td colspan=\"4\"></td></tr>";
						
						$u_aufgaben_arr = $this->get_unteruafgaben_arr ( $t_id );
						$anz = count ( $u_aufgaben_arr );
						if ($anz) {
							$z2 = 0;
							echo "<tfoot><tr><th>ERL</th><th></th><th>TAGE</th><th>DATUM</th><th>AUFGABE</th><th>VERFASSER</th><th>VERANTWORTLICH</th><th>ZUORDNUNG</th><th>STATUS</th></tr></tfoot>";
							for($a = 0; $a < $anz; $a ++) {
								$z2 ++;
								$u_t_id = $u_aufgaben_arr [$a] ['T_ID'];
								$u_text = $u_aufgaben_arr [$a] ['TEXT'];
								$u_edit_text = "<a href=\"?daten=todo&option=edit&t_id=$u_t_id\">$u_text</a>";
								$u_anzeigen_ab = date_mysql2german ( $u_aufgaben_arr [$a] ['ANZEIGEN_AB'] );
								$u_link_pdf = "<a href=\"?daten=todo&option=pdf_auftrag&proj_id=$u_t_id\"><img src=\"css/pdf.png\"></a>";
								$u_erledigt = $u_aufgaben_arr [$a] ['ERLEDIGT'];
								if ($u_erledigt == '1') {
									$u_erledigt = 'erledigt';
								} else {
									$u_erledigt = "offen";
								}
								
								$u_verfasser_id = $u_aufgaben_arr [$a] ['VERFASSER_ID'];
								$b = new benutzer ();
								$b->get_benutzer_infos ( $u_verfasser_id );
								$u_verfasser_name = $b->benutzername;
								$u_beteiligt_id = $u_aufgaben_arr [$a] ['BENUTZER_ID'];
								$b = new benutzer ();
								$b->get_benutzer_infos ( $u_beteiligt_id );
								$u_beteiligt_name = $b->benutzername;
								$u_akut = $u_aufgaben_arr [$a] ['AKUT'];
								if ($u_akut == 'JA') {
									$c = 3;
								} else {
									$c = 5;
								}
								
								$u_kos_typ = $u_aufgaben_arr [$a] ['KOS_TYP'];
								$u_kos_id = $u_aufgaben_arr [$a] ['KOS_ID'];
								$r = new rechnung ();
								$u_kos_bez = $r->kostentraeger_ermitteln ( $u_kos_typ, $u_kos_id );
								
								$u_t_vergangen = $o->tage_berechnen_bis_heute ( $u_anzeigen_ab );
								echo "<tr class=\"zeile$c\"><td>";
								$f->check_box_js ( 't_dats[]', $t_dat, 'Erledigt', null, null );
								echo "</td><td>$u_t_vergangen T</td><td>$u_anzeigen_ab</td><td>$u_edit_text</td>";
								echo "<td>$u_verfasser_name</td><td>$u_beteiligt_name</td><td>$u_kos_bez<br>";
								if ($u_kos_typ == 'Einheit') {
									$ukontaktdaten_mieter = $this->kontaktdaten_anzeigen_mieter ( ltrim ( rtrim ( $u_kos_id ) ) );
									echo $ukontaktdaten_mieter;
								}
								echo "</td>";
								if ($u_erledigt == 'erledigt') {
									echo "<td class=\"gruen\"><b>$u_erledigt</b>";
								} else {
									echo "<td class=\"rot\">$u_erledigt";
								}
								echo " $u_link_pdf</td>";
								echo "</tr>";
							}
						}
						echo "</table>";
					}
					$f->fieldset_ende ();
				} // end for p
					  
				// $f->button_js('BTN_alle_erl', 'Markierte als ERLDIGT kennzeichnen!!!', null);
			} // end else
			
			$f->hidden_feld ( 'option', 'erledigt_alle' );
			$f->send_button_js ( 'BTN_alle_erl', 'Markierte als ERLDIGT kennzeichnen!!!', null );
			$f->ende_formular ();
		}
		// $this->todo_liste2($benutzer_id, $erl);
	}
	function get_aufgaben($b_id, $erl = '0') {
		$db_abfrage = "SELECT * FROM TODO_LISTE WHERE (BENUTZER_ID='$b_id' OR VERFASSER_ID='$b_id') && AKTUELL='1' && UE_ID!='0' && ERLEDIGT='$erl' ORDER BY KOS_TYP, KOS_ID, UE_ID, VERFASSER_ID, ANZEIGEN_AB ASC";
		
		// echo $db_abfrage;
		// echo "<br><br>";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$my_arr [] = $row;
			}
			return $my_arr;
		}
	}
	function get_auftraege_einheit($kos_typ, $kos_id, $erledigt = '0') {
		if ($erledigt == '0') {
			$db_abfrage = "SELECT *, DATE_FORMAT(ERSTELLT, '%d.%m.%Y') AS ERSTELLT_D FROM  `TODO_LISTE` WHERE  ERLEDIGT='$erledigt' &&`KOS_TYP` = '$kos_typ' && KOS_ID='$kos_id'  ORDER BY ERSTELLT DESC";
		}
		if ($erledigt == '1') {
			$db_abfrage = "SELECT *, DATE_FORMAT(ERSTELLT, '%d.%m.%Y') AS ERSTELLT_D FROM  `TODO_LISTE` WHERE  ERLEDIGT='$erledigt' &&`KOS_TYP` = '$kos_typ' && KOS_ID='$kos_id'  ORDER BY ERSTELLT DESC";
		}
		
		if ($erledigt == 'alle') {
			$db_abfrage = "SELECT *, DATE_FORMAT(ERSTELLT, '%d.%m.%Y') AS ERSTELLT_D FROM  `TODO_LISTE` WHERE  `KOS_TYP` = '$kos_typ' && KOS_ID='$kos_id'  ORDER BY ERSTELLT DESC";
		}
		
		/*
		 * if($erledigt=='alle'){
		 * echo $erledigt;
		 * $db_abfrage = "SELECT *, DATE_FORMAT(ERSTELLT, '%d.%m.%Y') AS ERSTELLT_D FROM `TODO_LISTE` WHERE `KOS_TYP` = '$kos_typ' && KOS_ID='$kos_id' ORDER BY ERSTELLT DESC";
		 * }
		 */
		
		// echo "$erledigt ".$db_abfrage;
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$my_arr [] = $row;
			}
			// print_r($my_arr);
			return $my_arr;
		}
	}
	function dropdown_projekte($label, $name, $id, $b_id) {
		$arr = $this->get_auftraege_alle ( $b_id );
		// print_r($arr);
		$anz = count ( $arr );
		if ($anz) {
			echo "<label for=\"$id\">$label</label>";
			echo "<select id=\"$id\" name=\"$name\">";
			for($a = 0; $a < $anz; $a ++) {
				$t_id = $arr [$a] ['T_ID'];
				$text = $arr [$a] ['TEXT'];
				echo "<option value=\"$t_id\">$text</option>";
			}
			echo "</select>";
		}
	}
	function form_verschieben($t_id) {
		$f = new formular ();
		$f->erstelle_formular ( 'Auftraege verschieben', '' );
		$this->get_aufgabe_alles ( $t_id );
		$f->text_feld_inaktiv ( 'Auftragstext', 'at', $this->text, strlen ( $this->text ), 'at' );
		$this->dropdown_projekte ( 'Verschieben in mein Projekt', 'p_id', 'p_id', $_SESSION ['benutzer_id'] );
		$f->hidden_feld ( 'option', 'verschieben_snd' );
		$f->send_button ( 'btn_snd_v', 'Verschieben' );
		$f->ende_formular ();
	}
	function verschieben($t_id, $p_id) {
		$db_abfrage = "UPDATE TODO_LISTE SET UE_ID='$p_id' WHERE T_ID='$t_id'";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		return true;
	}
	function als_erledigt_markieren($t_dat) {
		$db_abfrage = "UPDATE TODO_LISTE SET ERLEDIGT='1' WHERE T_DAT='$t_dat'";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		
		// Protokollieren
		protokollieren ( 'TODO_LISTE', $t_dat, $t_dat );
		
		return true;
	}
	function get_auftraege($b_id, $erl = '0') {
		$db_abfrage = "SELECT * FROM TODO_LISTE WHERE (BENUTZER_ID='$b_id' OR VERFASSER_ID='$b_id') && AKTUELL='1' && UE_ID='0' && ERLEDIGT='$erl' && T_ID NOT IN (SELECT UE_ID FROM TODO_LISTE WHERE (BENUTZER_ID='$b_id' OR VERFASSER_ID='$b_id') && AKTUELL='1' && UE_ID!='0' && ERLEDIGT='$erl') ORDER BY KOS_TYP, KOS_ID, UE_ID, VERFASSER_ID, ANZEIGEN_AB ASC";
		
		// echo $db_abfrage;
		// echo "<br>";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$my_arr [] = $row;
			}
			return $my_arr;
		}
	}
	function get_auftraege_alle($b_id, $erl = '0') {
		$db_abfrage = "SELECT * FROM TODO_LISTE WHERE (BENUTZER_ID='$b_id' OR VERFASSER_ID='$b_id') && AKTUELL='1' && UE_ID='0' && ERLEDIGT='$erl' ORDER BY TEXT ASC";
		// $db_abfrage = "SELECT * FROM TODO_LISTE WHERE AKTUELL='1' && UE_ID='0' && ERLEDIGT='$erl' ORDER BY TEXT ASC";
		
		// echo $db_abfrage;
		// echo "<br>";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$my_arr [] = $row;
			}
			return $my_arr;
		}
	}
	function get_alle_auftraege($erl = '0') {
		// $db_abfrage = "SELECT * FROM TODO_LISTE WHERE AKTUELL='1' && ERLEDIGT='$erl' ORDER BY VERFASSER_ID, BENUTZER_ID, TEXT ASC";
		$db_abfrage = "SELECT * FROM TODO_LISTE WHERE AKTUELL='1' ORDER BY VERFASSER_ID, BENUTZER_ID, TEXT ASC";
		// $db_abfrage = "SELECT * FROM TODO_LISTE WHERE AKTUELL='1' && UE_ID='0' && ERLEDIGT='$erl' ORDER BY TEXT ASC";
		
		// echo $db_abfrage;
		// echo "<br>";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$my_arr [] = $row;
			}
			return $my_arr;
		}
	}
	function todo_liste2($benutzer_id = '0', $erl = '0') {
		// $arr_n = $this->get_aufgaben($benutzer_id, $erl); // aufgaben
		// $arr_a = $this->get_auftraege($benutzer_id,$erl); // aufgaben
		$arr_n = $this->get_alle_auftraege ( $erl );
		// $arr_a = $this->get_alle_auftraege(0);
		
		// echo "<pre>";
		// echo "<hr><hr>";
		// $arr = array_sortByIndex(array_merge($arr_n, $arr_a),'ANZEIGEN_AB', DESC);
		// $arr = array_merge($arr_n, $arr_a);
		// print_r($arr_a + $arr_n);
		// print_r($arr);
		$anz_n = count ( $arr_n );
		if ($anz_n) {
			for($a = 0; $a < $anz_n; $a ++) {
				$arr [] = $arr_n [$a];
			}
		}
		
		/*
		 * $anz_a = count($arr_a);
		 * if($anz_a){
		 * for($a=0;$a<$anz_a;$a++){
		 * $arr[] = $arr_a[$a];
		 * }
		 * }
		 */
		
		// print_r($arr);
		// print_r($arr_a);
		// unset($arr_a);
		unset ( $arr_n );
		
		unset ( $arr );
		
		$db_abfrage = "SELECT * FROM TODO_LISTE WHERE AKTUELL='1'  ORDER BY T_ID DESC";
		// echo "<hr>";
		// echo $db_abfrage;
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$arr [] = $row;
			}
		}
		
		$anz = count ( $arr );
		// echo "$anz ANZ $numrows";
		// echo '<pre>';
		// print_r($arr);
		// die('SANEL');
		$p = 0;
		if ($anz) {
			
			$pz = $p + 1;
			$f = new formular ();
			// ob_clean();
			$f->fieldset ( "Meine Aufträge", 'ana' );
			
			// echo "<table class=\"sortable\">";
			// echo "<table class=\"sortable\">";
			echo "<table class=\"sortable\">";
			echo "<thead><tr><th></th><th>TAGE</th><th>DATUM</th><th>AUFTRAG</th><th>VERFASSER</th><th>VERANTWORTLICH</th><th>ZUORDNUNG</th><th>STATUS</th></tr></thead>";
			$z1 = 0;
			for($a = 0; $a < $anz; $a ++) {
				$row = $arr [$a];
				// print_r($row);
				// echo "<table>";
				
				$z1 ++;
				$t_id = $row ['T_ID'];
				// $link_pdf = "<a href=\"?daten=todo&option=pdf_projekt&proj_id=$t_id\"><img src=\"css/pdf.png\"></a>";
				$link_pdf_1 = "<a href=\"?daten=todo&option=pdf_auftrag&proj_id=$t_id\"><img src=\"css/pdf.png\"></a>";
				$text = $row ['TEXT'];
				$edit_text = "<a href=\"?daten=todo&option=edit&t_id=$t_id\">$text</a>";
				$anzeigen_ab = date_mysql2german ( $row ['ANZEIGEN_AB'] );
				$erledigt = $row ['ERLEDIGT'];
				$verfasser_id = $row ['VERFASSER_ID'];
				$b = new benutzer ();
				$b->get_benutzer_infos ( $verfasser_id );
				$verfasser_name = $b->benutzername;
				$beteiligt_id = $row ['BENUTZER_ID'];
				$b = new benutzer ();
				$b->get_benutzer_infos ( $beteiligt_id );
				$beteiligt_name = $b->benutzername;
				if ($erledigt == '1') {
					$erledigt = 'erledigt';
				} else {
					$erledigt = "offen";
				}
				
				$link_erledigt = "<a href=\"\">";
				$o = new objekt ();
				$t_vergangen = $o->tage_berechnen_bis_heute ( $anzeigen_ab );
				
				$akut = $row ['AKUT'];
				if ($akut == 'JA') {
					$c1 = 3;
				} else {
					$c1 = 4;
				}
				
				$kos_typ = $row ['KOS_TYP'];
				$kos_id = $row ['KOS_ID'];
				$r = new rechnung ();
				if (! empty ( $kos_typ ) && ! empty ( $kos_id )) {
					$kos_bez = $r->kostentraeger_ermitteln ( $kos_typ, $kos_id );
				} else {
					$kos_bez = "$kos_typ $kos_id Unbekannt";
				}
				echo "<tr class=\"zeile$c1\"><td>$z1</td><td>$t_vergangen T</td><td>$anzeigen_ab</td><td>$edit_text</td>";
				echo "<td>$verfasser_name</td><td>$beteiligt_name</td><td>$kos_bez<br>";
				if ($kos_typ == 'Einheit') {
					$kontaktdaten_mieter = $this->kontaktdaten_anzeigen_mieter ( $kos_id );
					echo "$kos_bez $kontaktdaten_mieter";
				}
				echo "</td>";
				if ($erledigt == 'erledigt') {
					echo "<td class=\"gruen\"><b>$erledigt</b>";
				} else {
					echo "<td class=\"rot\">$erledigt";
				}
				echo "$link_pdf_1 </td>";
				echo "</tr>";
				$link_neue_u_aufgabe = "<a href=\"?daten=todo&option=neues_projekt&t_id=$t_id\">Neue Aufgabe erstellen</a>";
				// echo "<tr class=\"zeile6\"><td colspan=\"4\">$link_neue_u_aufgabe</td><td colspan=\"4\"></td></tr>";
			}
		} else {
			hinweis_ausgeben ( "Keine gefunden!" );
		}
		echo "</table>";
		$f->fieldset_ende ();
	}
	function todo_liste3($benutzer_id = '0', $erl = '0') {
		// $arr_n = $this->get_aufgaben($benutzer_id, $erl); // aufgaben
		// $arr_a = $this->get_auftraege($benutzer_id,$erl); // aufgaben
		$arr_n = $this->get_alle_auftraege ( $erl );
		// $arr_a = $this->get_alle_auftraege(0);
		
		// echo "<pre>";
		// echo "<hr><hr>";
		// $arr = array_sortByIndex(array_merge($arr_n, $arr_a),'ANZEIGEN_AB', DESC);
		// $arr = array_merge($arr_n, $arr_a);
		// print_r($arr_a + $arr_n);
		// print_r($arr);
		$anz_n = count ( $arr_n );
		if ($anz_n) {
			for($a = 0; $a < $anz_n; $a ++) {
				$arr [] = $arr_n [$a];
			}
		}
		
		/*
		 * $anz_a = count($arr_a);
		 * if($anz_a){
		 * for($a=0;$a<$anz_a;$a++){
		 * $arr[] = $arr_a[$a];
		 * }
		 * }
		 */
		
		// print_r($arr);
		// print_r($arr_a);
		// unset($arr_a);
		unset ( $arr_n );
		
		unset ( $arr );
		
		$db_abfrage = "SELECT * FROM TODO_LISTE WHERE AKTUELL='1' AND ERLEDIGT='$erl' ORDER BY T_ID DESC";
		// echo "<hr>";
		// echo $db_abfrage;
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$arr [] = $row;
			}
		}
		
		$anz = count ( $arr );
		// echo "$anz ANZ $numrows";
		// echo '<pre>';
		// print_r($arr);
		// die('SANEL');
		$p = 0;
		if ($anz) {
			
			$pz = $p + 1;
			$f = new formular ();
			// ob_clean();
			$f->fieldset ( "Meine Aufträge", 'ana' );
			
			// echo "<table class=\"sortable\">";
			// echo "<table class=\"sortable\">";
			echo "<table class=\"sortable\">";
			echo "<thead><tr><th></th><th>TAGE</th><th>DATUM</th><th>AUFTRAG</th><th>VERFASSER</th><th>VERANTWORTLICH</th><th>ZUORDNUNG</th><th>STATUS</th></tr></thead>";
			$z1 = 0;
			for($a = 0; $a < $anz; $a ++) {
				$row = $arr [$a];
				// print_r($row);
				// echo "<table>";
				
				$z1 ++;
				$t_id = $row ['T_ID'];
				// $link_pdf = "<a href=\"?daten=todo&option=pdf_projekt&proj_id=$t_id\"><img src=\"css/pdf.png\"></a>";
				$link_pdf_1 = "<a href=\"?daten=todo&option=pdf_auftrag&proj_id=$t_id\"><img src=\"css/pdf.png\"></a>";
				$text = $row ['TEXT'];
				$edit_text = "<a href=\"?daten=todo&option=edit&t_id=$t_id\">$text</a>";
				$anzeigen_ab = date_mysql2german ( $row ['ANZEIGEN_AB'] );
				$erledigt = $row ['ERLEDIGT'];
				$verfasser_id = $row ['VERFASSER_ID'];
				$b = new benutzer ();
				$b->get_benutzer_infos ( $verfasser_id );
				$verfasser_name = $b->benutzername;
				$beteiligt_id = $row ['BENUTZER_ID'];
				$b = new benutzer ();
				$b->get_benutzer_infos ( $beteiligt_id );
				$beteiligt_name = $b->benutzername;
				if ($erledigt == '1') {
					$erledigt = 'erledigt';
				} else {
					$erledigt = "offen";
				}
				
				$link_erledigt = "<a href=\"\">";
				$o = new objekt ();
				$t_vergangen = $o->tage_berechnen_bis_heute ( $anzeigen_ab );
				
				$akut = $row ['AKUT'];
				if ($akut == 'JA') {
					$c1 = 3;
				} else {
					$c1 = 4;
				}
				
				$kos_typ = $row ['KOS_TYP'];
				$kos_id = $row ['KOS_ID'];
				$r = new rechnung ();
				if (! empty ( $kos_typ ) && ! empty ( $kos_id )) {
					$kos_bez = $r->kostentraeger_ermitteln ( $kos_typ, $kos_id );
				} else {
					$kos_bez = "$kos_typ $kos_id Unbekannt";
				}
				echo "<tr class=\"zeile$c1\"><td>$z1</td><td>$t_vergangen T</td><td>$anzeigen_ab</td><td>$edit_text</td>";
				echo "<td>$verfasser_name</td><td>$beteiligt_name</td><td>$kos_bez<br>";
				if ($kos_typ == 'Einheit') {
					$kontaktdaten_mieter = $this->kontaktdaten_anzeigen_mieter ( $kos_id );
					echo "$kos_bez $kontaktdaten_mieter";
				}
				echo "</td>";
				if ($erledigt == 'erledigt') {
					echo "<td class=\"gruen\"><b>$erledigt</b>";
				} else {
					echo "<td class=\"rot\">$erledigt";
				}
				echo "$link_pdf_1 </td>";
				echo "</tr>";
				$link_neue_u_aufgabe = "<a href=\"?daten=todo&option=neues_projekt&t_id=$t_id\">Neue Aufgabe erstellen</a>";
				// echo "<tr class=\"zeile6\"><td colspan=\"4\">$link_neue_u_aufgabe</td><td colspan=\"4\"></td></tr>";
			}
		} else {
			hinweis_ausgeben ( "Keine gefunden!" );
		}
		echo "</table>";
		$f->fieldset_ende ();
	}
	function kontaktdaten_anzeigen_mieter($einheit_id) {
		// $einheit_id = get_einheit_id($einheit_bez);
		$ee = new einheit ();
		$status = $ee->get_einheit_status ( $einheit_id );
		if ($status == true) {
			$mv_id = $ee->get_last_mietvertrag_id ( $einheit_id );
		} else {
			$mv_id = null;
		}
		if (empty ( $mv_id )) {
			/* Nie vermietet */
			$ee->get_einheit_info ( $einheit_id );
			return "<b>Leerstand</b>\n$ee->haus_strasse $ee->haus_nummer\n<b>Lage: $ee->einheit_lage</b>\n$ee->haus_plz $ee->haus_stadt";
		} else {
			$m = new mietvertraege ();
			$m->get_mietvertrag_infos_aktuell ( $mv_id );
			$result = mysql_query ( "SELECT PERSON_MIETVERTRAG_PERSON_ID FROM PERSON_MIETVERTRAG WHERE PERSON_MIETVERTRAG_MIETVERTRAG_ID='$mv_id' && PERSON_MIETVERTRAG_AKTUELL='1' ORDER BY PERSON_MIETVERTRAG_ID ASC" );
			$numrows = mysql_numrows ( $result );
			if ($numrows) {
				$kontaktdaten = "Lage: $m->einheit_lage<br>$m->personen_name_string_u<br>$m->haus_strasse $m->haus_nr, $m->haus_plz $m->haus_stadt<br>";
				while ( $row = mysql_fetch_assoc ( $result ) ) {
					$person_id = $row ['PERSON_MIETVERTRAG_PERSON_ID'];
					$arr = $this->finde_detail_kontakt_arr ( 'PERSON', $person_id );
					if (is_array ( $arr )) {
						$anz = count ( $arr );
						for($a = 0; $a < $anz; $a ++) {
							$dname = $arr [$a] ['DETAIL_NAME'];
							$dinhalt = $arr [$a] ['DETAIL_INHALT'];
							$kontaktdaten .= "<br><b>$dname</b>:$dinhalt";
						}
					}
				}
				return $kontaktdaten;
			}
			
			// return "<b><br>TEL VON MIETER E: $einheit_bez</b>";
		}
	}
	function finde_detail_kontakt_arr($tab, $id) {
		$db_abfrage = "SELECT DETAIL_NAME, DETAIL_INHALT FROM DETAIL WHERE DETAIL_ZUORDNUNG_TABELLE = '$tab' && (DETAIL_NAME LIKE '%tel%'or DETAIL_NAME LIKE '%fax%' or DETAIL_NAME LIKE '%mobil%' or DETAIL_NAME LIKE '%handy%' OR DETAIL_NAME LIKE '%mail%' OR DETAIL_NAME LIKE '%anschrift%') && DETAIL_ZUORDNUNG_ID = '$id' && DETAIL_AKTUELL = '1' ORDER BY DETAIL_NAME ASC";
		// echo $db_abfrage;
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $resultat );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $resultat ) )
				$my_array [] = $row;
			return $my_array;
		}
	}
	function get_unteruafgaben_arr($t_id) {
		$db_abfrage = "SELECT * FROM TODO_LISTE WHERE UE_ID ='$t_id' && ANZEIGEN_AB <= DATE_FORMAT(NOW(), '%Y-%m-%d' ) && AKTUELL='1' ORDER BY ERLEDIGT ASC, AKUT ASC, ANZEIGEN_AB ASC";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_arr [] = $row;
			return $my_arr;
		}
	}
	function get_text($t_id) {
		$result = mysql_query ( "SELECT TEXT FROM TODO_LISTE WHERE T_ID='$t_id' && AKTUELL='1' ORDER BY T_ID DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		return $row ['TEXT'];
	}
	function get_status($t_id) {
		$result = mysql_query ( "SELECT ERLEDIGT FROM TODO_LISTE WHERE T_ID='$t_id' && AKTUELL='1' ORDER BY T_ID DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		
		if ($row ['ERLEDIGT'] == 1) {
			return 'erledigt';
		} else {
			return 'offen';
		}
	}
	function get_kos_bez($t_id) {
		$result = mysql_query ( "SELECT KOS_TYP, KOS_ID FROM TODO_LISTE WHERE T_ID='$t_id' && AKTUELL='1' ORDER BY T_ID DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		
		$kos_typ = $row ['KOS_TYP'];
		$kos_id = $row ['KOS_ID'];
		$r = new rechnung ();
		$kos_bez = $r->kostentraeger_ermitteln ( $kos_typ, $kos_id );
		// echo $kos_bez;
		// die('SSS');
		// echo $kos_typ.$kos_id;
		// die();
		return $kos_bez;
	}
	function get_my_auftraege_arr($benutzer_id, $erl = 0) {
		$db_abfrage = "SELECT * 	FROM `TODO_LISTE` 	WHERE (	`BENUTZER_ID` ='$benutzer_id'	OR `VERFASSER_ID` ='$benutzer_id' )
	AND `AKTUELL` = '1' AND ERLEDIGT='$erl' ORDER BY ERSTELLT DESC";
		
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_arr [] = $row;
			return $my_arr;
		}
	}
	function my_todo_liste($benutzer_id, $erl = 0) {
		$u_aufgaben_arr = $this->get_my_auftraege_arr ( $benutzer_id, $erl );
		$anz = count ( $u_aufgaben_arr );
		if ($anz) {
			$f = new formular ();
			$f->erstelle_formular ( '', null );
			echo "<table class=\"sortable\">";
			echo "<thead><tr><th>NR</th><th>OPT</th><th>TAGE</th><th>DATUM</th><th>PROJEKT</th><th>VERFASSER</th><th>VERANTWORTLICH</th><th>ZUORDNUNG</th><th>STATUS</th></tr></thead>";
			$z = 0;
			for($a = 0; $a < $anz; $a ++) {
				$z ++;
				
				$t_dat = $u_aufgaben_arr [$a] ['T_DAT'];
				$u_t_id = $u_aufgaben_arr [$a] ['T_ID'];
				$u_text = $u_aufgaben_arr [$a] ['TEXT'];
				$u_edit_text = "<a href=\"?daten=todo&option=edit&t_id=$u_t_id\">$u_text</a>";
				$u_anzeigen_ab = date_mysql2german ( $u_aufgaben_arr [$a] ['ANZEIGEN_AB'] );
				$u_link_pdf = "<a href=\"?daten=todo&option=pdf_auftrag&proj_id=$u_t_id\"><img src=\"css/pdf.png\"></a>";
				$link_auftraege_an = "<a href=\"?daten=todo&option=option=auftraege_an&typ=$benutzer_typ&id=$beteiligt_id\"><img src=\"css/pdf.png\"></a>";
				$u_erledigt = $u_aufgaben_arr [$a] ['ERLEDIGT'];
				if ($u_erledigt == '1') {
					$u_erledigt = 'erledigt';
				} else {
					$u_erledigt = "offen";
				}
				
				$u_verfasser_id = $u_aufgaben_arr [$a] ['VERFASSER_ID'];
				/*
				 * $b = new benutzer;
				 * $b->get_benutzer_infos($u_verfasser_id);
				 * $u_verfasser_name = $b->benutzername;
				 * $u_beteiligt_id = $u_aufgaben_arr[$a]['BENUTZER_ID'];
				 * $b = new benutzer;
				 * $b->get_benutzer_infos($u_beteiligt_id);
				 * $u_beteiligt_name = $b->benutzername;
				 */
				
				$b = new benutzer ();
				$b->get_benutzer_infos ( $u_verfasser_id );
				$u_verfasser_name = $b->benutzername;
				$benutzer_typ = $u_aufgaben_arr [$a] ['BENUTZER_TYP'];
				$beteiligt_id = $u_aufgaben_arr [$a] ['BENUTZER_ID'];
				if ($benutzer_typ == 'Benutzer' or empty ( $benutzer_typ )) {
					
					$b = new benutzer ();
					$b->get_benutzer_infos ( $beteiligt_id );
					$u_beteiligt_name = $b->benutzername;
				}
				if ($benutzer_typ == 'Partner') {
					$partner_id = $u_aufgaben_arr [$a] ['BENUTZER_ID'];
					$pp = new partners ();
					$pp->get_partner_info ( $partner_id );
					$u_beteiligt_name = $pp->partner_name;
				}
				
				$u_akut = $u_aufgaben_arr [$a] ['AKUT'];
				if ($u_akut == 'JA') {
					$c = 3;
				} else {
					$c = 5;
				}
				
				$u_kos_typ = $u_aufgaben_arr [$a] ['KOS_TYP'];
				$u_kos_id = $u_aufgaben_arr [$a] ['KOS_ID'];
				$r = new rechnung ();
				$u_kos_bez = $r->kostentraeger_ermitteln ( $u_kos_typ, $u_kos_id );
				
				$o = new objekt ();
				$u_t_vergangen = $o->tage_berechnen_bis_heute ( $u_anzeigen_ab );
				
				$link_auftraege_an = "<a href=\"?daten=todo&option=auftraege_an&typ=$benutzer_typ&id=$beteiligt_id\">$u_beteiligt_name</a>";
				
				echo "<tr class=\"zeile$c\"><td>$z.</td><td>";
				$f->check_box_js ( 't_dats[]', $t_dat, 'Erledigt', null, null );
				echo "</td><td>$u_t_vergangen T</td><td>$u_anzeigen_ab</td><td><b>Auftragsnr.:$u_t_id</b>: $u_edit_text</td>";
				echo "<td>$u_verfasser_name</td><td>$link_auftraege_an</td><td>$u_kos_bez<br>";
				if ($u_kos_typ == 'Einheit') {
					$ukontaktdaten_mieter = $this->kontaktdaten_anzeigen_mieter ( ltrim ( rtrim ( $u_kos_id ) ) );
					echo $ukontaktdaten_mieter;
				}
				echo "</td>";
				if ($u_erledigt == 'erledigt') {
					echo "<td class=\"gruen\"><b>$u_erledigt</b>";
				} else {
					echo "<td class=\"rot\">$u_erledigt";
				}
				echo " $u_link_pdf</td>";
				echo "</tr>";
			}
		}
		echo "</table>";
		
		$f->hidden_feld ( 'option', 'erledigt_alle' );
		$f->send_button_js ( 'BTN_alle_erl', 'Markierte als ERLDIGT kennzeichnen!!!', null );
		$f->ende_formular ();
	}
	function get_my_projekt_arr($benutzer_id, $erl = '0') {
		if ($erl == '0') {
			/*
			 * $db_abfrage = "SELECT IF( UE_ID = '0', T_ID, UE_ID ) AS PROJ_ID FROM TODO_LISTE WHERE (
			 * BENUTZER_ID = '$benutzer_id'
			 * OR VERFASSER_ID = '$benutzer_id'
			 * ) && ANZEIGEN_AB <= DATE_FORMAT( NOW( ) , '%Y-%m-%d' ) && AKTUELL = '1' && ERLEDIGT='0'
			 * GROUP BY PROJ_ID
			 * ORDER BY ANZEIGEN_AB ASC";
			 */
			$db_abfrage = "SELECT T_ID AS PROJ_ID
FROM `TODO_LISTE`
WHERE (
`BENUTZER_ID` ='$benutzer_id'
OR `VERFASSER_ID` ='$benutzer_id'
)
AND `AKTUELL` = '1' && ERLEDIGT='0' && UE_ID='0'";
		} else {
			/*
			 * $db_abfrage = "SELECT IF( UE_ID = '0', T_ID, UE_ID ) AS PROJ_ID FROM TODO_LISTE WHERE (
			 * BENUTZER_ID = '$benutzer_id'
			 * OR VERFASSER_ID = '$benutzer_id'
			 * ) && ANZEIGEN_AB <= DATE_FORMAT( NOW( ) , '%Y-%m-%d' ) && AKTUELL = '1' && ERLEDIGT='1'
			 * GROUP BY PROJ_ID
			 * ORDER BY ANZEIGEN_AB ASC";
			 */
			$db_abfrage = "SELECT T_ID AS PROJ_ID
FROM `TODO_LISTE`
WHERE (
`BENUTZER_ID` ='$benutzer_id'
OR `VERFASSER_ID` ='$benutzer_id'
)
AND `AKTUELL` = '1' && ERLEDIGT='1' && UE_ID='0'";
		}
		
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_arr [] = $row;
			return $my_arr;
		}
	}
	function get_kos_typ_id($t_id) {
		$result = mysql_query ( "SELECT KOS_TYP, KOS_ID FROM TODO_LISTE WHERE T_ID='$t_id' && AKTUELL='1' ORDER BY T_ID DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		if (is_array ( $row )) {
			$this->kos_typ = $row ['KOS_TYP'];
			$this->kos_id = $row ['KOS_ID'];
			$r = new rechnung ();
			$this->kos_bez = $r->kostentraeger_ermitteln ( $this->kos_typ, $this->kos_id );
		} else {
			die ( 'Kostenträger unbekannt' );
		}
	}
	function form_suche($typ_int_ext = 'intern') {
		$f = new formular ();
		$f->erstelle_formular ( "Auftragsuche $typ_int_ext", null );
		if ($typ_int_ext == 'intern') {
			$be = new benutzer ();
			$f->check_box_js ( 'check_int_ext', 1, 'Externe Aufträge suchen', 'onchange="redirect_to(\'?daten=todo&option=auftrag_suche&typ_int_ext=extern\')"', null );
			$be->dropdown_benutzer2 ( 'Mitarbeiter wählen', 'benutzer_id', 'benutzer_id', null );
			$f->hidden_feld ( 'benutzer_typ', 'benutzer' );
		} else {
			$p = new partner ();
			$f->check_box_js ( 'check_int_ext', 1, 'Interne Aufträge suchen', 'onchange="redirect_to(\'?daten=todo&option=auftrag_suche&typ_int_ext=intern\')"', null );
			echo "<br>";
			$p->partner_dropdown ( 'Externe Firma/Partner wählen', 'benutzer_id', 'benutzer_id', null );
			$f->hidden_feld ( 'benutzer_typ', 'Partner' );
		}
		$f->hidden_feld ( 'option', 'auftrag_suche_send' );
		$f->send_button ( 'BTN_SuchA', 'Aufträge finden' );
		$f->ende_formular ();
	}
	function form_edit_aufgabe($t_id) {
		if (empty ( $t_id )) {
			die ( 'Aufgabe oder Projekt wählen' );
		}
		$this->get_aufgabe_alles ( $t_id );
		// echo '<pre>';
		// print_r($this);
		$f = new formular ();
		$f->erstelle_formular ( 'Bearbeiten', '' );
		$bb = new buchen ();
		if ($this->ue_id == '0') {
			$f->fieldset ( "Projekt bearbeiten:$this->text", 'na' );
			// echo "<a href=\"?daten=todo&option=projekt_loeschen&t_id=$t_id\">Projekt löschen ???</a><br>";
		} else {
			$f->fieldset ( "Aufgabe bearbeiten:$this->text", 'na' );
			// echo "<a href=\"?daten=todo&option=projekt_loeschen&t_id=$t_id\">Aufgabe löschen ???</a><br>";
		}
		$f->text_bereich ( 'Beschreibung', 'text', $this->text, 5, 20, 'aufgabe' );
		
		$js_typ = "onchange=\"list_kostentraeger('list_kostentraeger', this.value)\"";
		// $bb->dropdown_kostentreager_typen('Kostenträgertyp', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ);
		$bb->dropdown_kostentreager_typen_vw ( 'Kostenträgertyp', 'kostentraeger_typ', 'kostentraeger_typ', $js_typ, $this->kos_typ );
		$js_id = "";
		// $bb->dropdown_kostentreager_ids('Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id);
		$bb->dropdown_kostentraeger_bez_vw ( 'Kostenträger', 'kostentraeger_id', 'dd_kostentraeger_id', $js_id, $this->kos_typ, $this->kos_id );
		
		if ($this->benutzer_typ == 'Benutzer' or empty ( $this->benutzer_typ )) {
			$b = new benutzer ();
			$b->dropdown_benutzer ( $this->mitarbeiter_name );
		}
		if ($this->benutzer_typ == 'Partner') {
			$pp = new partners ();
			$pp->partner_dropdown ( 'Partner wählen', 'benutzer_id', 'benutzer_id', $this->benutzer_id );
		}
		
		$f->datum_feld ( 'Anzeigen ab', 'anzeigen_ab', $this->anzeigen_ab, 'dat_a' );
		$this->dropdown_akut ( $this->akut );
		$this->dropdown_erledigt ( $this->erledigt );
		$f->send_button ( 'submit_n1', 'Änderungen speichern' );
		$f->fieldset_ende ();
		$f->ende_formular ();
		if (isset ( $_POST ['submit_n1'] )) {
			if (! empty ( $_REQUEST ['benutzer_id'] ) && ! empty ( $_REQUEST ['submit_n1'] ) && ! empty ( $_REQUEST ['anzeigen_ab'] ) && ! empty ( $_REQUEST ['text'] )) {
				$anz_ab = date_german2mysql ( $_REQUEST [anzeigen_ab] );
				
				$kostentraeger_typ = $_REQUEST ['kostentraeger_typ'];
				$kostentraeger_bez = $_REQUEST ['kostentraeger_id'];
				$kostentraeger_id = $bb->kostentraeger_id_ermitteln ( $kostentraeger_typ, $kostentraeger_bez );
				$erledigt = $_REQUEST [status];
				$db_abfrage = "UPDATE TODO_LISTE SET TEXT='$_REQUEST[text]', ANZEIGEN_AB='$anz_ab', BENUTZER_ID='$_REQUEST[benutzer_id]', ERLEDIGT='$erledigt', AKUT='$_REQUEST[akut]', KOS_TYP='$_REQUEST[kostentraeger_typ]', KOS_ID='$kostentraeger_id' WHERE T_DAT='$this->t_dat'";
				$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
				weiterleiten ( "?daten=todo" );
			}
		}
	}
	function get_aufgabe_alles($t_id) {
		$result = mysql_query ( "SELECT * FROM TODO_LISTE WHERE T_ID='$t_id' && AKTUELL='1' ORDER BY T_ID DESC LIMIT 0,1" );
		$row = mysql_fetch_assoc ( $result );
		$this->t_dat = $row ['T_DAT'];
		$this->t_id = $row ['T_ID'];
		$this->ue_id = $row ['UE_ID'];
		$this->benutzer_typ = $row ['BENUTZER_TYP'];
		$this->benutzer_id = $row ['BENUTZER_ID'];
		$this->verfasser_id = $row ['VERFASSER_ID'];
		$bb = new benutzer ();
		if (empty ( $this->benutzer_typ ) or ($this->benutzer_typ == 'Benutzer')) {
			$this->benutzer_typ = 'Benutzer';
			$bb->get_benutzer_infos ( $this->benutzer_id );
			$this->mitarbeiter_name = $bb->benutzername;
		}
		if ($this->benutzer_typ == 'Partner') {
			$pp = new partners ();
			$pp->get_partner_info ( $this->benutzer_id );
			$this->partner_ans = "$pp->partner_strasse $pp->partner_hausnr, $pp->partner_plz $pp->partner_ort";
			$dd = new detail ();
			$this->partner_fax = $dd->finde_detail_inhalt ( 'PARTNER_LIEFERANT', $this->benutzer_id, 'Fax' );
			$this->partner_email = $dd->finde_detail_inhalt ( 'PARTNER_LIEFERANT', $this->benutzer_id, 'Email' );
			
			$this->mitarbeiter_name = "$pp->partner_name";
		}
		
		$bb->get_benutzer_infos ( $this->verfasser_id );
		$this->verfasst_von = $bb->benutzername;
		
		$this->erledigt = $row ['ERLEDIGT'];
		if ($this->erledigt == '1') {
			$this->erledigt_text = "erledigt";
		} else {
			$this->erledigt_text = "offen";
		}
		$this->anzeigen_ab = date_mysql2german ( $row ['ANZEIGEN_AB'] );
		$this->akut = $row ['AKUT'];
		$this->text = $row ['TEXT'];
		$this->kos_typ = $row ['KOS_TYP'];
		$this->kos_id = $row ['KOS_ID'];
		$r = new rechnung ();
		$this->kos_bez = $r->kostentraeger_ermitteln ( $this->kos_typ, $this->kos_id );
	}
	function projekt_aufgabe_loeschen($t_id) {
		$this->get_aufgabe_alles ( $t_id );
		$f = new formular ();
		$f->erstelle_formular ( 'Löschen von Projekten und Aufgaben', '' );
		$f->fieldset ( 'Löschen', 'loeschen' );
		if ($this->ue_id == '0') {
			echo "Ganzes Projekt <b>$this->text</b> löschen?<br><br>";
			$f->hidden_feld ( 'art', 'Projekt' );
		} else {
			echo "Aufgabe: <b>$this->text</b> löschen?<br><br>";
			$f->hidden_feld ( 'art', 'Aufgabe' );
		}
		$f->hidden_feld ( 't_id', $t_id );
		$f->hidden_feld ( 'option', 'loeschen' );
		$f->send_button ( 'del', 'JA' );
		$f->ende_formular ();
		$f->fieldset_ende ();
	}
	function projekt_aufgabe_loeschen_sql($t_id, $art) {
		if ($art == 'Aufgabe') {
			$db_abfrage = "DELETE FROM TODO_LISTE WHERE T_ID ='$t_id'";
		}
		if ($art = 'Projekt') {
			$db_abfrage = "DELETE FROM TODO_LISTE WHERE T_ID ='$t_id' OR UE_ID='$t_id'";
		}
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		weiterleiten ( "?daten=todo" );
	}
	function pdf_projekt($id) {
		// echo "PDF HIER";
		$arr = $this->get_unteruafgaben_arr ( $id );
		if (is_array ( $arr )) {
			$anz = count ( $arr );
			$projekt_name = $this->get_text ( $id );
			for($a = 0; $a < $anz; $a ++) {
				
				$mitarbeiter_id = $arr [$a] ['BENUTZER_ID'];
				$bb = new benutzer ();
				$bb->get_benutzer_infos ( $mitarbeiter_id );
				
				$arr [$a] ['MITARBEITER'] = $bb->benutzername;
				
				if ($arr [$a] ['ERLEDIGT'] == 1) {
					$arr [$a] ['ERLEDIGT_STAT'] = 'erledigt';
				} else {
					$arr [$a] ['ERLEDIGT_STAT'] = 'offen';
				}
				
				$arr [$a] ['POS'] = $a + 1;
				
				$r = new rechnung ();
				$kos_typ = $arr [$a] ['KOS_TYP'];
				$kos_id = $arr [$a] ['KOS_ID'];
				$kos_bez = $r->kostentraeger_ermitteln ( $kos_typ, $kos_id );
				if ($kos_typ == 'Einheit') {
					$kontaktdaten_mieter = $this->kontaktdaten_anzeigen_mieter ( $kos_id );
					// echo $kontaktdaten_mieter;
					$arr [$a] ['KOS_BEZ'] = $kos_bez . "\n" . str_replace ( '<br>', "\n", $kontaktdaten_mieter );
				} else {
					$arr [$a] ['KOS_BEZ'] = $kos_bez;
				}
			}
		} else {
			$projekt_name = $this->get_text ( $id );
			$arr [0] ['TEXT'] = $projekt_name;
			$arr [0] ['ERLEDIGT_STAT'] = $this->get_status ( $id );
			$kos_bez = $this->get_kos_bez ( $id );
			$this->get_aufgabe_alles ( $id );
			// $arr[0]['KOS_BEZ'] = $kos_bez;
			
			if ($this->kos_typ == 'Einheit') {
				$kontaktdaten_mieter = $this->kontaktdaten_anzeigen_mieter ( $this->kos_id );
				// echo $kontaktdaten_mieter;
				$arr [0] ['KOS_BEZ'] = $kos_bez . "\n" . str_replace ( '<br>', "\n", $kontaktdaten_mieter );
			} else {
				$arr [0] ['KOS_BEZ'] = $kos_bez;
			}
			$projekt_name = '';
		}
		// echo '<pre>';
		// print_r($arr);
		// die();
		ob_clean (); // ausgabepuffer leeren
		include_once ('pdfclass/class.ezpdf.php');
		include_once ('classes/class_bpdf.php');
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION [partner_id], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
		
		$cols = array (
				'POS' => "POS",
				'T_ID' => "ANR",
				'TEXT' => "Aufgaben",
				'KOS_BEZ' => "Ort",
				'ERLEDIGT_STAT' => "Status",
				'MITARBEITER' => "Mitarbeiter" 
		);
		
		$pdf->ezText ( "$projekt_name", 11 );
		$pdf->ezTable ( $arr, $cols, "<b>Projekt: $id</b>", array (
				'showHeadings' => 1,
				'shaded' => 1,
				'titleFontSize' => 7,
				'fontSize' => 7,
				'xPos' => 55,
				'xOrientation' => 'right',
				'width' => 500,
				'cols' => array (
						'SEITE' => array (
								'justification' => 'left',
								'width' => 27 
						),
						'EINHEIT' => array (
								'justification' => 'left',
								'width' => 50 
						),
						'ZEITRAUM' => array (
								'justification' => 'left',
								'width' => 90 
						),
						'EMPF' => array (
								'justification' => 'left' 
						) 
				) 
		) );
		
		ob_clean (); // ausgabepuffer leeren
		header ( "Content-type: application/pdf" ); // wird von MSIE ignoriert
		$pdf->ezStream ();
	}
	function pdf_auftrag($id) {
		$this->get_aufgabe_alles ( $id );
		
		$pp = new benutzer ();
		$b_arr = $pp->get_user_info ( $_SESSION ['benutzer_id'] );
		// print_r($b_arr);
		// die();
		$_SESSION ['partner_id'] = $b_arr [0] ['BP_PARTNER_ID'];
		
		if ($this->kos_typ == 'Einheit') {
			$kontaktdaten_mieter = $this->kontaktdaten_anzeigen_mieter ( $this->kos_id );
			// echo $kontaktdaten_mieter;
			$kontaktdaten_mieter = "<b>Einheit</b>: $this->kos_bez" . "\n" . str_replace ( '<br>', "\n", $kontaktdaten_mieter );
		}
		
		if ($this->kos_typ == 'Partner') {
			$p = new partners ();
			$p->get_partner_info ( $this->kos_id );
			$kontaktdaten_mieter = "$p->partner_name\n$p->partner_strasse $p->partner_hausnr\n$p->partner_plz $p->partner_ort\n";
			$det_arr = $this->finde_detail_kontakt_arr ( 'PARTNER_LIEFERANT', $this->kos_id );
			// echo strtoupper($this->kos_typ);
			if (is_array ( $det_arr )) {
				$anzd = count ( $det_arr );
				for($a = 0; $a < $anzd; $a ++) {
					$dname = $this->html2txt ( $det_arr [$a] ['DETAIL_NAME'] );
					$dinhalt = $this->html2txt ( $det_arr [$a] ['DETAIL_INHALT'] );
					$kontaktdaten_mieter .= "\n$dname:$dinhalt";
				}
			}
		}
		
		if ($this->kos_typ == 'Eigentuemer') {
			$weg = new weg ();
			$weg->get_eigentumer_id_infos2 ( $this->kos_id );
			// echo '<pre>';
			// print_r($weg);
			// die();
			$miteigentuemer_namen = strip_tags ( $weg->eigentuemer_name_str_u );
			$kontaktdaten_mieter = "$weg->haus_strasse $weg->haus_nummer\n<b>$weg->haus_plz $weg->haus_stadt</b>\n\n";
			for($pe = 0; $pe < count ( $weg->eigentuemer_person_ids ); $pe ++) {
				$et_p_id = $weg->eigentuemer_person_ids [$pe];
				$det_arr = $this->finde_detail_kontakt_arr ( 'Person', $et_p_id );
				// echo strtoupper($this->kos_typ);
				$kontaktdaten_mieter .= rtrim ( ltrim ( $weg->eigentuemer_name [$pe] ['HRFRAU'] ) ) . " ";
				$kontaktdaten_mieter .= rtrim ( ltrim ( $weg->eigentuemer_name [$pe] ['Nachname'] ) ) . " ";
				$kontaktdaten_mieter .= rtrim ( ltrim ( $weg->eigentuemer_name [$pe] ['Vorname'] ) ) . "\n";
				if (is_array ( $det_arr )) {
					$anzd = count ( $det_arr );
					for($ad = 0; $ad < $anzd; $ad ++) {
						$dname = $this->html2txt ( $det_arr [$ad] ['DETAIL_NAME'] );
						$dinhalt = $this->html2txt ( $det_arr [$ad] ['DETAIL_INHALT'] );
						$kontaktdaten_mieter .= "$dname:$dinhalt\n";
					}
				}
				$kontaktdaten_mieter .= "\n";
			}
		}
		
		if ($this->kos_typ != 'Partner' && $this->kos_typ != 'Einheit' && $this->kos_typ != 'Eigentuemer') {
			if ($this->kos_typ == 'Haus') {
				$h = new haus ();
				$h->get_haus_info ( $this->kos_id );
				$kontaktdaten_mieter = "Haus:\n$h->haus_strasse $h->haus_nummer\n<b>$h->haus_plz $h->haus_stadt</b>";
			} else {
				$d = new detail ();
				$kontaktdaten_mieter = $this->kos_bez;
			}
		}
		$kontaktdaten_mieter = str_replace ( '<br />', "\n", $kontaktdaten_mieter );
		$kontaktdaten_mieter = $this->html2txt ( $kontaktdaten_mieter );
		
		// echo '<pre>';
		// print_r($arr);
		// die();
		ob_clean (); // ausgabepuffer leeren
		include_once ('pdfclass/class.ezpdf.php');
		include_once ('classes/class_bpdf.php');
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$bpdf = new b_pdf ();
		$bpdf->b_header ( $pdf, 'Partner', $_SESSION ['partner_id'], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6 );
		
		$pdf->Rectangle ( 250, 630, 305, 80 );
		$pdf->addText ( 252, 700, 10, "Arbeitsauftrag Nr: <b>$id</b> an" );
		$pdf->addText ( 252, 685, 9, "<b>$this->benutzer_typ</b>: $this->mitarbeiter_name $this->partner_ans" );
		if ($this->benutzer_typ == 'Partner') {
			// print_r($this);
			// die();
			$pdf->addText ( 252, 675, 9, "<b>Fax: $this->partner_fax</b>" );
			$pdf->addText ( 375, 675, 9, "<b>Email: $this->partner_email</b>" );
		}
		// $pdf->addText(352,685,8,"<b>Nummer</b>: $id");
		$pdf->addText ( 252, 665, 8, "<b>Datum</b>: $this->anzeigen_ab" );
		/*
		 * if($this->akut=='1'){
		 * $akut = 'JA';
		 * }else{
		 * $akut = 'NEIN';
		 * }
		 */
		
		if ($this->erledigt == '1') {
			$erledigt = 'JA';
		} else {
			$erledigt = 'NEIN';
		}
		
		$pdf->addText ( 252, 655, 8, "<b>AKUT</b>: $this->akut" );
		$pdf->addText ( 252, 645, 8, "<b>Erfasst</b>: $this->verfasst_von" );
		// $pdf->addText(352,645,8,"<b>$this->benutzer_typ</b>: $this->mitarbeiter_name");
		
		if ($this->kos_typ == 'Einheit') {
			$weg = new weg ();
			// $et_arr = $weg->get_eigentuemer_arr($einheit_id);
			$weg->get_last_eigentuemer ( $this->kos_id );
			if (isset ( $weg->eigentuemer_id )) {
				$e_id = $weg->eigentuemer_id;
				// $weg->get_eigentumer_id_infos3($e_id);
				$weg->get_eigentuemer_namen ( $e_id );
				$miteigentuemer_namen = strip_tags ( $weg->eigentuemer_name_str );
				
				/* ################Betreuer################## */
				$anz_p = count ( $weg->eigentuemer_person_ids );
				$betreuer_str = '';
				$betreuer_arr;
				for($be = 0; $be < $anz_p; $be ++) {
					$et_p_id = $weg->eigentuemer_person_ids [$be];
					$d_k = new detail ();
					$dt_arr = $d_k->finde_alle_details_grup ( 'PERSON', $et_p_id, 'INS-Kundenbetreuer' );
					
					if (is_array ( $dt_arr )) {
						$anz_bet = count ( $dt_arr );
						for($bet = 0; $bet < $anz_bet; $bet ++) {
							$bet_str = $dt_arr [$bet] ['DETAIL_INHALT'];
							$betreuer_str .= "$bet_str<br>";
							$betreuer_arr [] = $bet_str;
						}
					} else {
						// $betreuer_str .= "<b>KEINE BET</b>";
					}
				}
				
				if (is_array ( $betreuer_arr )) {
					$betreuer_str = '';
					$betreuer_arr1 = array_unique ( $betreuer_arr );
					for($bbb = 0; $bbb < count ( $betreuer_arr1 ); $bbb ++) {
						$betreuer_str .= $betreuer_arr1 [$bbb];
					}
					$pdf->addText ( 252, 635, 8, "<b>Erledigt</b>:$erledigt" );
				}
			} else {
				$miteigentuemer_namen = "UNBEKANNT";
			}
		} else {
			$pdf->addText ( 252, 635, 8, "<b>Erledigt</b>: $erledigt" );
		}
		$pdf->ezText ( $kontaktdaten_mieter );
		// $pdf->ezSetDy(-20); //abstand
		if ($pdf->y > 645) {
			$pdf->ezSetY ( 645 );
		}
		$pdf->ezSetDy ( - 5 ); // abstand
		                   // $pdf->ezText("<i>INS-OBJEKTBETREUER: $betreuer_str</i>", 7);
		$pdf->ezText ( "<b>Auftragsbeschreibung:</b>", 12 );
		
		$pdf->ezText ( $this->text );
		$pdf->ezSetDy ( - 10 ); // abstand
		                    // print_r($this);
		                    // die();
		if ($this->benutzer_typ == 'Benutzer') {
			$pdf->ezText ( "<b>Durchgeführte Arbeiten:</b>" );
			$pdf->ezText ( "_________________________________________________________________________" );
			$pdf->ezText ( "_________________________________________________________________________" );
			$pdf->ezText ( "_________________________________________________________________________" );
			$pdf->ezText ( "_________________________________________________________________________" );
			$pdf->ezText ( "_________________________________________________________________________" );
			$pdf->ezText ( "_________________________________________________________________________" );
			$pdf->ezText ( "_________________________________________________________________________" );
			$pdf->ezSetDy ( - 15 ); // abstand
			$pdf->ezText ( "<b>Material:</b>" );
			$pdf->ezText ( "_________________________________________________________________________" );
			$pdf->ezText ( "_________________________________________________________________________" );
			$pdf->ezText ( "_________________________________________________________________________" );
			$pdf->ezText ( "_________________________________________________________________________" );
			
			$pdf->ezSetDy ( - 10 ); // abstand
			$pdf->Rectangle ( 50, $pdf->y - 20, 10, 10 );
			$pdf->addText ( 65, $pdf->y - 18, 8, "<b>Arbeit abgeschlossen</b>" );
			$pdf->ezSetDy ( - 15 ); // abstand
			$pdf->Rectangle ( 50, $pdf->y - 20, 10, 10 );
			$pdf->addText ( 65, $pdf->y - 18, 8, "<b>Arbeit nicht abgeschlossen</b>" );
			$pdf->addText ( 200, $pdf->y - 18, 8, "<b>Neuer Termin: _______________/____________ Uhr</b>" );
			$pdf->ezSetDy ( - 50 ); // abstand
			
			$pdf->Rectangle ( 50, $pdf->y - 20, 10, 10 );
			$pdf->addText ( 65, $pdf->y - 18, 8, "<b>Fahrzeit:______________ Std:Min</b>" );
			$pdf->addText ( 200, $pdf->y - 18, 8, "<b>Ankunftszeit: _______________ Uhr</b>" );
			$pdf->addText ( 375, $pdf->y - 18, 8, "<b>Fertigstellunsgszeit: _______________ Uhr</b>" );
			$pdf->ezSetDy ( - 100 ); // abstand
			$pdf->addText ( 50, $pdf->y - 18, 8, "_______________________" );
			$pdf->addText ( 200, $pdf->y - 18, 8, "_______________________________" );
			$pdf->addText ( 375, $pdf->y - 18, 8, "___________________________________" );
			$pdf->ezSetDy ( - 10 ); // abstand
			$pdf->addText ( 90, $pdf->y - 18, 6, "Datum" );
			$pdf->addText ( 240, $pdf->y - 18, 6, "Unterschrift Kunde" );
			$pdf->addText ( 425, $pdf->y - 18, 6, "Unterschrift Monteur" );
		}
		if ($this->benutzer_typ == 'Partner') {
			
			$rr = new rechnung ();
			if ($this->kos_typ == 'Eigentuemer') {
				$rr->get_empfaenger_infos ( 'Objekt', $weg->objekt_id );
			} else {
				$rr->get_empfaenger_infos ( $this->kos_typ, $this->kos_id );
			}
			// $rechnungs_empfaenger_typ = $this->rechnungs_empfaenger_typ;
			// $rechnungs_empfaenger_id = $this->rechnungs_empfaenger_id;
			// $pdf->ezText("TTT: $rr->rechnungs_empfaenger_typ $rr->rechnungs_empfaenger_id",10);
			$dd = new detail ();
			$rep_eur = $dd->finde_detail_inhalt ( 'PARTNER_LIEFERANT', $rr->rechnungs_empfaenger_id, 'Rep-Freigabe' );
			$rr->get_empfaenger_info ( $rr->rechnungs_empfaenger_id );
			$pdf->ezSetDy ( - 10 ); // abstand
			if (empty ( $rep_eur )) {
				$pdf->ezText ( "<b>Freigabe bis: ______ EUR Netto</b>" );
			} else {
				$pdf->ezText ( "<b>Freigabe bis: $rep_eur EUR Netto</b>" );
			}
			$dd = new detail ();
			$b_tel = $dd->finde_detail_inhalt ( 'BENUTZER', $_SESSION ['benutzer_id'], 'Telefon' );
			if (empty ( $b_tel )) {
				$b_tel = $dd->finde_detail_inhalt ( 'PARTNER_LIEFERANT', $_SESSION [partner_id], 'Telefon' );
			}
			$pdf->ezSetDy ( - 10 ); // abstand
			$pdf->ezText ( "<b>Bei Kosten über Freigabesumme bitten wir um Rückmeldung unter $b_tel.</b>" );
			$pdf->ezSetDy ( - 10 ); // abstand
			$pdf->ezText ( "Rechnung bitte unter Angabe der <u><b>Auftragsnummer $id</b></u> und <u><b>$this->kos_typ $this->kos_bez</b></u>   an:", 10 );
			$pdf->ezSetDy ( - 10 ); // abstand
			$pdf->ezText ( "<b>$rr->rechnungs_empfaenger_name\n$rr->rechnungs_empfaenger_strasse  $rr->rechnungs_empfaenger_hausnr\n$rr->rechnungs_empfaenger_plz  $rr->rechnungs_empfaenger_ort</b>", 12 );
			$pdf->ezSetDy ( - 25 ); // abstand
			$pdf->ezText ( "Mit freundlichen Grüßen", 10 );
			$pdf->ezSetDy ( - 25 ); // abstand
			$pdf->ezText ( "i.A. $this->verfasst_von", 10 );
		}
		
		ob_clean ();
		// header('Content-Type: application/pdf');
		$gk_bez = utf8_encode ( date ( "Y_m_d" ) . '_' . substr ( str_replace ( '.', '_', str_replace ( ',', '', str_replace ( ' ', '_', ltrim ( rtrim ( $this->kos_bez ) ) ) ) ), 0, 30 ) . '_Auftrag-Nr._' . $id . '.pdf' );
		// echo "Content-Disposition:attachment;filename='$gk_bez'";
		// die();
		// header("Content-Disposition:attachment;filename='$gk_bez'");
		$pdf_opt ['Content-Disposition'] = $gk_bez;
		
		// echo '<pre>';
		// print_r($_SERVER);
		
		// $url = 'http://berlussimo-1/berlussimo_workspace/sivac/berlussimo/index.php?/'.$_SERVER['QUERY_STRING'];
		// print_r(get_headers($url));
		
		// die();
		$pdf->ezStream ( $pdf_opt );
		
		/* Normal */
		// $pdf->ezStream();
	}
	function html2txt($document) {
		$search = array (
				'@<script[^>]*?>.*?</script>@si', // Strip out javascript
				'@<[\/\!]*?[^<>]*?>@si', // Strip out HTML tags
				'@<style[^>]*?>.*?</style>@siU', // Strip style tags properly
				'@<![\s\S]*?--[ \t\n\r]*>@' 
		) // Strip multi-line comments including CDATA
;
		$text = preg_replace ( $search, '', $document );
		return $text;
	}
	function get_haus_ids($haus_str, $haus_nr, $haus_plz) {
		$db_abfrage = "SELECT HAUS_ID FROM HAUS WHERE HAUS_AKTUELL='1' && HAUS_STRASSE='$haus_str' && HAUS_NUMMER='$haus_nr' && HAUS_PLZ='$haus_plz'";
		// echo $db_abfrage;
		$resultat = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $resultat );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $resultat ) )
				$my_array [] = $row;
			return $my_array;
		}
	}
	function auftraege_an_haus($haus_id) {
		if (isset ( $_REQUEST ['einheit_id'] ) && ! empty ( $_REQUEST ['einheit_id'] )) {
			$arr = $this->get_auftraege_einheit ( 'Einheit', $_REQUEST ['einheit_id'] );
			$e = new einheit ();
			$e->get_einheit_info ( $_REQUEST ['einheit_id'] );
			if (is_array ( $arr )) {
				// echo '<pre>';
				// print_r($arr);
				// die();
				echo "<table>";
				echo "<tr><th colspan=\"4\">EINHEIT $e->einheit_kurzname</th></tr>";
				echo "<tr><th>TEXT</th><th>VON/AN</th><th>ERLEDIGT</th><th>DATUM</th></tr>";
				$anz = count ( $arr );
				for($a = 0; $a < $anz; $a ++) {
					$t_id = $arr [$a] ['T_ID'];
					$text = $arr [$a] ['TEXT'];
					$verfasser_id = $arr [$a] ['VERFASSER_ID'];
					$bb = new benutzer ();
					$bb->get_benutzer_infos ( $verfasser_id );
					$verfasser_name = $bb->benutzername;
					$benutzer_typ = $arr [$a] ['BENUTZER_TYP'];
					$benutzer_id = $arr [$a] ['BENUTZER_ID'];
					
					if ($benutzer_typ == 'Benutzer') {
						$bb->get_benutzer_infos ( $benutzer_id );
						$benutzer_name = $bb->benutzername;
					}
					if ($benutzer_typ == 'Partner') {
						$p = new partners ();
						$p->get_partner_info ( $benutzer_id );
						$benutzer_name = "$p->partner_name";
					}
					$erledigt = $arr [$a] ['ERLEDIGT'];
					if ($erledigt == '1') {
						$erl = 'JA';
					} else {
						$erl = 'NEIN';
					}
					$erstellt = $arr [$a] ['ERSTELLT'];
					// echo "$text $verfasser_name $benutzer_name $erstellt<br>";
					$link_pdf = "<a href=\"?daten=todo&option=pdf_auftrag&proj_id=$t_id\"><img src=\"css/pdf2.png\"></a>";
					$link_txt = "<a href=\"?daten=todo&option=edit&t_id=$t_id\">$text</a>";
					
					echo "<tr><td>$link_txt $link_pdf</td><td>$verfasser_name<br>$benutzer_name</td><td>$erl</td><td>$erstellt</td></tr>";
				}
				echo "</table>";
			}
		}
		if (isset ( $arr )) {
			unset ( $arr );
		}
		
		$h = new haus ();
		$h->get_haus_info ( $haus_id );
		$haus_ids = $this->get_haus_ids ( $h->haus_strasse, $h->haus_nummer, $h->haus_plz );
		$anz_h = count ( $haus_ids );
		$arr = Array ();
		$obj_arr = array ();
		for($b = 0; $b < $anz_h; $b ++) {
			$haus_id = $haus_ids [$b] ['HAUS_ID'];
			$ha = new haus ();
			$ha->get_haus_info ( $haus_id );
			$obj_arr [] = $ha->objekt_id;
			$tmp_arr = $this->get_auftraege_einheit ( 'Haus', $haus_id );
			if (is_array ( $tmp_arr )) {
				$arr = array_merge ( $arr, $tmp_arr );
			}
		}
		if (! is_array ( $arr )) {
			
			fehlermeldung_ausgeben ( "Keine Aufträge an Haus $h->haus_strasse $h->haus_nummer" );
		} else {
			array_unique ( $obj_arr );
			$anz = count ( $arr );
			// echo "<pre>";
			// print_r($arr);
			echo "<table>";
			echo "<tr><th colspan=\"4\">HAUS</th></tr>";
			echo "<tr><th>TEXT</th><th>VON/AN</th><th>ERLEDIGT</th><th>DATUM</th></tr>";
			for($a = 0; $a < $anz; $a ++) {
				$t_id = $arr [$a] ['T_ID'];
				$text = $arr [$a] ['TEXT'];
				$verfasser_id = $arr [$a] ['VERFASSER_ID'];
				$bb = new benutzer ();
				$bb->get_benutzer_infos ( $verfasser_id );
				$verfasser_name = $bb->benutzername;
				$benutzer_typ = $arr [$a] ['BENUTZER_TYP'];
				$benutzer_id = $arr [$a] ['BENUTZER_ID'];
				
				if ($benutzer_typ == 'Benutzer') {
					$bb->get_benutzer_infos ( $benutzer_id );
					$benutzer_name = $bb->benutzername;
				}
				if ($benutzer_typ == 'Partner') {
					$p = new partners ();
					$p->get_partner_info ( $benutzer_id );
					$benutzer_name = "$p->partner_name";
				}
				$erledigt = $arr [$a] ['ERLEDIGT'];
				if ($erledigt == '1') {
					$erl = 'JA';
				} else {
					$erl = 'NEIN';
				}
				$erstellt = $arr [$a] ['ERSTELLT'];
				// echo "$text $verfasser_name $benutzer_name $erstellt<br>";
				$link_pdf = "<a href=\"?daten=todo&option=pdf_auftrag&proj_id=$t_id\"><img src=\"css/pdf2.png\"></a>";
				$link_txt = "<a href=\"?daten=todo&option=edit&t_id=$t_id\">$text</a>";
				echo "<tr><td>$link_txt $link_pdf</td><td>$verfasser_name<br>$benutzer_name</td><td>$erl</td><td>$erstellt</td></tr>";
			}
			echo "</table>";
			// echo '<pre>';
			// print_r($obj_arr);
			$anz_o = count ( $obj_arr );
			$obj_auf = array ();
			for($o = 0; $o < $anz_o; $o ++) {
				$objekt_id = $obj_arr [$o];
				$tmp_arr = $this->get_auftraege_einheit ( 'Objekt', $objekt_id );
				// print_r($tmp_arr);
				if (is_array ( $tmp_arr )) {
					$obj_auf = array_merge ( $obj_auf, $tmp_arr );
				}
			}
			// print_r($obj_auf);
			$arr = $obj_auf;
			$anz = count ( $arr );
			if ($anz > 0) {
				echo "<table>";
				echo "<tr><th colspan=\"4\">OBJEKT</th></tr>";
				echo "<tr><th>TEXT</th><th>VON/AN</th><th>ERLEDIGT</th><th>DATUM</th></tr>";
				
				for($a = 0; $a < $anz; $a ++) {
					$t_id = $arr [$a] ['T_ID'];
					$text = $arr [$a] ['TEXT'];
					$verfasser_id = $arr [$a] ['VERFASSER_ID'];
					$bb = new benutzer ();
					$bb->get_benutzer_infos ( $verfasser_id );
					$verfasser_name = $bb->benutzername;
					$benutzer_typ = $arr [$a] ['BENUTZER_TYP'];
					$benutzer_id = $arr [$a] ['BENUTZER_ID'];
					
					if ($benutzer_typ == 'Benutzer') {
						$bb->get_benutzer_infos ( $benutzer_id );
						$benutzer_name = $bb->benutzername;
					}
					if ($benutzer_typ == 'Partner') {
						$p = new partners ();
						$p->get_partner_info ( $benutzer_id );
						$benutzer_name = "$p->partner_name";
					}
					$erledigt = $arr [$a] ['ERLEDIGT'];
					if ($erledigt == '1') {
						$erl = 'JA';
					} else {
						$erl = 'NEIN';
					}
					$erstellt = $arr [$a] ['ERSTELLT'];
					$link_pdf = "<a href=\"?daten=todo&option=pdf_auftrag&proj_id=$t_id\"><img src=\"css/pdf2.png\"></a>";
					$link_txt = "<a href=\"?daten=todo&option=edit&t_id=$t_id\">$text</a>";
					echo "<tr><td>$link_txt $link_pdf</td><td>$verfasser_name<br>$benutzer_name</td><td>$erl</td><td>$erstellt</td></tr>";
				}
				echo "</table>";
			}
		}
	}
	function liste_auftrage_an_arr($typ, $id, $erl = 0) {
		$db_abfrage = "SELECT * 	FROM `TODO_LISTE` 	WHERE BENUTZER_TYP='$typ' && `BENUTZER_ID` ='$id' AND `AKTUELL` = '1' AND ERLEDIGT='$erl' ORDER BY ERSTELLT DESC";
		// echo $db_abfrage."<br>";
		$result = mysql_query ( $db_abfrage ) or die ( mysql_error () );
		$numrows = mysql_numrows ( $result );
		if ($numrows) {
			while ( $row = mysql_fetch_assoc ( $result ) )
				$my_arr [] = $row;
			return $my_arr;
		}
	}
	function liste_auftrage_an($typ, $id, $erl = 0) {
		$arr = $this->liste_auftrage_an_arr ( $typ, $id, $erl );
		if (! is_array ( $arr )) {
			fehlermeldung_ausgeben ( "Keine Auftrage an $typ $id" );
		} else {
			$anz = count ( $arr );
			$f = new formular ();
			if ($erl == 0) {
				$f->erstelle_formular ( "Aufträge OFFEN", null );
			} else {
				$f->erstelle_formular ( "Aufträge ERLEDIGT", null );
			}
			
			echo "<table class=\"sortable\">";
			echo "<thead><tr><th>NR</th><th>ERL</th><th>DATUM</th><th>PROJEKT</th><th>VERFASSER</th><th>VERANTWORTLICH</th><th>ZUORDNUNG</th><th>STATUS</th></tr></thead>";
			$z = 0;
			for($a = 0; $a < $anz; $a ++) {
				$z ++;
				$t_dat = $arr [$a] ['T_DAT'];
				$t_id = $arr [$a] ['T_ID'];
				$this->get_aufgabe_alles ( $t_id );
				$u_link_pdf = "<a href=\"?daten=todo&option=pdf_auftrag&proj_id=$t_id\"><img src=\"css/pdf.png\"></a>";
				
				echo "<tr><td>$z.<br>$u_link_pdf</td><td>";
				if ($this->erledigt == '0') {
					$f->check_box_js ( 't_dats[]', $t_dat, 'Erledigt', null, null );
				}
				
				echo "</td><td>$this->anzeigen_ab</td><td><b>Auftragsnr.:$this->text</b></td>";
				echo "<td>$this->verfasst_von</td><td>$this->mitarbeiter_name</td><td>$this->kos_bez</td><td>$this->erledigt_text</td></tr>";
			}
			echo "</table>";
			$f->hidden_feld ( 'option', 'erledigt_alle' );
			$f->send_button_js ( 'BTN_alle_erl', 'Markierte als ERLDIGT kennzeichnen!!!', null );
			$f->ende_formular ();
		}
	}
} // end class todo

?>