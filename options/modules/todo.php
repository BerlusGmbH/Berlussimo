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

/* �berpr�fen ob Benutzer Zugriff auf das Modul hat */
if (! check_user_mod ( $_SESSION ['benutzer_id'], 'todo' )) {
	echo '<script type="text/javascript">';
	echo "alert('Keine Berechtigung')";
	echo '</script>';
	die ();
}

/* Modulabh�ngige Dateien d.h. Links und eigene Klasse */
include_once ("options/links/links.todo.php");
include_once ("classes/class_todo.php");
if (isset ( $_REQUEST ["option"] )) {
	$option = $_REQUEST ["option"];
} else {
	$option = '';
}
/* Optionsschalter */
switch ($option) {
	
	default :
		$t = new todo ();
		// $t->todo_liste($_SESSION['benutzer_id'], '0');
		// $t->todo_liste($_SESSION['benutzer_id'], '0');
		$t->my_todo_liste ( $_SESSION ['benutzer_id'], '0' );
		break;
	
	/* OFFEN */
	case "neue_auftraege" :
		$t = new todo ();
		$t->todo_liste2 ( $_SESSION ['benutzer_id'], '0' );
		break;
	
	/* OFFEN */
	case "offene_auftraege" :
		$t = new todo ();
		$t->todo_liste3 ( $_SESSION ['benutzer_id'], '0' );
		break;
	
	/* OFFEN */
	case "erledigte_auftraege" :
		$t = new todo ();
		$t->todo_liste2 ( $_SESSION ['benutzer_id'], '1' );
		break;
	
	/* Erledigte Projekte */
	case "erledigte_projekte" :
		$t = new todo ();
		$t->todo_liste ( $_SESSION ['benutzer_id'], '1' );
		break;
	
	/* Neues Projekt */
	case "neues_projekt" :
		$t = new todo ();
		$t->form_neue_aufgabe ( $_REQUEST ['t_id'], $_REQUEST ['typ'] );
		break;
	
	/* Editieren von Aufgaben und Projekten */
	case "edit" :
		if (! empty ( $_REQUEST ['t_id'] )) {
			$t = new todo ();
			$t->form_edit_aufgabe ( $_REQUEST ['t_id'] );
		} else {
			echo "Aufgabe oder Projekt w�hlen";
		}
		break;
	
	/* Projekt oder Aufgabe l�schen */
	case "projekt_loeschen" :
		if (! empty ( $_REQUEST ['t_id'] )) {
			$t = new todo ();
			$t->projekt_aufgabe_loeschen ( $_REQUEST ['t_id'] );
		} else {
			echo "Aufgabe oder Projekt w�hlen";
		}
		break;
	
	/* Definitiv l�schen */
	case "loeschen" :
		if (! empty ( $_REQUEST ['t_id'] )) {
			$t = new todo ();
			$t->projekt_aufgabe_loeschen_sql ( $_REQUEST ['t_id'], $_REQUEST ['art'] );
		} else {
			echo "Aufgabe oder Projekt w�hlen";
		}
		break;
	
	case "rss" :
		$t = new todo ();
		$t->rss_feed ( $_SESSION ['benutzer_id'] );
		break;
	
	case "pdf_projekt" :
		if (! empty ( $_REQUEST ['proj_id'] )) {
			$t = new todo ();
			$t->pdf_projekt ( intval ( $_REQUEST ['proj_id'] ) );
		} else {
			echo "Projekt w�hlen";
		}
		break;
	
	case "pdf_auftrag" :
		if (! empty ( $_REQUEST ['proj_id'] )) {
			$t = new todo ();
			$t->pdf_auftrag ( intval ( $_REQUEST ['proj_id'] ) );
		} else {
			echo "Projekt w�hlen";
		}
		break;
	
	case "form_neue_baustelle" :
		$t = new todo ();
		$t->form_neue_baustelle ();
		break;
	
	case "neue_baustelle" :
		if (! empty ( $_REQUEST ['bau_bez'] ) && ! empty ( $_REQUEST ['p_id'] )) {
			$t = new todo ();
			if ($t->neue_baustelle_speichern ( $_REQUEST ['bau_bez'], $_REQUEST ['p_id'] )) {
				$bau_bez = $_REQUEST ['bau_bez'];
				hinweis_ausgeben ( "Baustelle $bau_bez wurde erstellt" );
			}
			// print_r($_REQUEST);
		} else {
			fehlermeldung_ausgeben ( 'Ihre Eingabe zur Baustelle war unvollst�ndig!' );
		}
		break;
	
	case "baustellen_liste" :
		$t = new todo ();
		$t->baustellen_liste ();
		break;
	
	case "baustellen_liste_inaktiv" :
		$t = new todo ();
		$t->baustellen_liste ( '0' );
		break;
	
	case "baustelle_aktivieren" :
		$bau_id = $_REQUEST ['bau_id'];
		$t = new todo ();
		$t->baustelle_aktivieren ( $bau_id, '1' );
		weiterleiten ( "?daten=todo&option=baustellen_liste" );
		break;
	
	case "baustelle_deaktivieren" :
		$bau_id = $_REQUEST ['bau_id'];
		$t = new todo ();
		$t->baustelle_aktivieren ( $bau_id, '0' );
		weiterleiten ( "?daten=todo&option=baustellen_liste_inaktiv" );
		break;
	
	case "verschieben" :
		if (! empty ( $_REQUEST ['t_id'] )) {
			$t_id = $_REQUEST ['t_id'];
			$t = new todo ();
			$t->form_verschieben ( $t_id );
		} else {
			fehlermeldung_ausgeben ( "Aufgaben/Projekt id eingeben" );
		}
		break;
	
	case "verschieben_snd" :
		print_req ();
		if (! empty ( $_REQUEST ['t_id'] ) && ! empty ( $_REQUEST ['p_id'] )) {
			$t_id = $_REQUEST ['t_id']; // aufgaben_id T_ID
			$p_id = $_REQUEST ['p_id']; // projekt_id UE_ID
			$t = new todo ();
			if ($t->verschieben ( $t_id, $p_id )) {
				weiterleiten ( "?daten=todo" );
			} else {
				fehlermeldung_ausgeben ( "Verschieben gescheitert" );
			}
		} else {
			fehlermeldung_ausgeben ( "Aufgaben/Projekt id eingeben" );
		}
		break;
	
	case "auftrag_haus" :
		if (isset ( $_REQUEST ['haus_id'] ) && ! empty ( $_REQUEST ['haus_id'] )) {
			// echo "OK";
			$t = new todo ();
			$t->auftraege_an_haus ( $_REQUEST ['haus_id'] );
		} else {
			fehlermeldung_ausgeben ( "Haus w�hlen" );
		}
		break;
	
	case "api_ticket_test":
	/*Export TODO'S'*/
	$config = array (
				'url' => 'http://192.168.2.16/ticket/api/http.php/tickets.json',
				'key' => '1BD9ABDCC4784E1BA3872A5440FD06A2' 
		);
		
		// Fill in the data for the new ticket, this will likely come from $_POST.
		
		$todo = new todo ();
		$auftraege_arr = $todo->get_alle_auftraege ( 0 );
		
		// print_r($auftraege_arr);
		$anz_a = count ( $auftraege_arr );
		// die("ANZAHL :$anz_a");
		
		for($a = 0; $a < $anz_a; $a ++) {
			$t_id = $auftraege_arr [$a] ['T_ID'];
			$datum = $auftraege_arr [$a] ['ERSTELLT'];
			$text = $auftraege_arr [$a] ['TEXT'];
			$text_k = substr ( $text, 0, 20 );
			// echo "TEXT:$text<br>";
			echo "TEXTK:$text_k<br>";
			$kos_typ = $auftraege_arr [$a] ['KOS_TYP'];
			$kos_id = $auftraege_arr [$a] ['KOS_ID'];
			$r = new rechnung ();
			$kos_bez = $r->kostentraeger_ermitteln ( $kos_typ, $kos_id );
			
			$data ['name'] = 'BerlussimoAPI';
			$data ['email'] = 'sivac@berlus.de';
			$data ['subject'] = utf8_encode ( $text );
			$data ['message'] = utf8_encode ( $text );
			$data ['ip'] = $_SERVER ['REMOTE_ADDR'];
			$data ['body'] = 'BODY MANUAL';
			$data ['zuordnung'] = $kos_bez;
			$data ['kos_typ'] = $kos_typ;
			$data ['kos_id'] = $kos_id;
			$data ['created'] = $datum;
			$data ['attachments'] = array ();
			
			// print_r($data);
			// die();
			
			/*
			 * Add in attachments here if necessary
			 *
			 * $data['attachments'][] =
			 * array('filename.pdf' =>
			 * 'data:image/png;base64,' .
			 * base64_encode(file_get_contents('/path/to/filename.pdf')));
			 */
			
			// pre-checks
			function_exists ( 'curl_version' ) or die ( 'CURL support required' );
			function_exists ( 'json_encode' ) or die ( 'JSON support required' );
			
			// set timeout
			set_time_limit ( 30 );
			
			// curl post
			$ch = curl_init ();
			curl_setopt ( $ch, CURLOPT_URL, $config ['url'] );
			curl_setopt ( $ch, CURLOPT_POST, 1 );
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, json_encode ( $data ) );
			curl_setopt ( $ch, CURLOPT_USERAGENT, 'PHP Berlussimo' );
			curl_setopt ( $ch, CURLOPT_HEADER, FALSE );
			curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
					'Expect:',
					'X-API-Key: ' . $config ['key'] 
			) );
			curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, FALSE );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, TRUE );
			$result = curl_exec ( $ch );
			$code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
			curl_close ( $ch );
			
			if ($code != 201)
				die ( 'Unable to create ticket: ' . $result );
			
			$ticket_id = ( int ) $result;
			echo "Ticket $ticket_id erstellt<br>";
			
			// Continue onward here if necessary. $ticket_id has the ID number of the
			// newly-created ticket
		}
		break;
	
	case "erledigt_alle" :
		// print_req();
		if (isset ( $_REQUEST ['t_dats'] ) && ! empty ( $_REQUEST ['t_dats'] )) {
			if (is_array ( $_REQUEST ['t_dats'] )) {
				$anz_markiert = count ( $_REQUEST ['t_dats'] );
				for($a = 0; $a < $anz_markiert; $a ++) {
					$t_dat = $_REQUEST ['t_dats'] [$a];
					$to = new todo ();
					$to->als_erledigt_markieren ( $t_dat );
				}
				weiterleiten_in_sec ( "index.php?daten=todo", 2 );
			} else {
				fehlermeldung_ausgeben ( "Projekte und Aufgaben markieren!!!" );
			}
		} else {
			fehlermeldung_ausgeben ( "Projekte und Aufgaben markieren!!!" );
		}
		break;
	
	case "auftraege_an" :
		
		if (isset ( $_REQUEST ['typ'] ) && isset ( $_REQUEST ['id'] ) && ! empty ( $_REQUEST ['typ'] ) && ! empty ( $_REQUEST ['id'] )) {
			$typ = $_REQUEST ['typ'];
			$id = $_REQUEST ['id'];
			$to = new todo ();
			/* Offene */
			$to->liste_auftrage_an ( $typ, $id, 0 );
			/* Erledigte */
			$to->liste_auftrage_an ( $typ, $id, 1 );
		}
		break;
	
	/* Auftragsuche Formular */
	case "auftrag_suche" :
		$t = new todo ();
		if (isset ( $_REQUEST ['typ_int_ext'] ) && ! empty ( $_REQUEST ['typ_int_ext'] )) {
			
			$t->form_suche ( $_REQUEST ['typ_int_ext'] );
		} else {
			$t->form_suche ();
		}
		break;
	
	/* Auftragsuche Formular sendet Suchdaten */
	case "auftrag_suche_send" :
		$t = new todo ();
		print_req ();
		if (isset ( $_REQUEST ['benutzer_typ'] ) && ! empty ( $_REQUEST ['benutzer_typ'] ) && isset ( $_REQUEST ['benutzer_id'] ) && ! empty ( $_REQUEST ['benutzer_id'] )) {
			$typ = $_REQUEST ['benutzer_typ'];
			$id = $_REQUEST ['benutzer_id'];
			weiterleiten ( "?daten=todo&option=auftraege_an&typ=$typ&id=$id" );
		}
		break;
} // end switch

?>	