<?php

$option = request()->input('option');
$detail_tabelle = request()->input('detail_tabelle');
$detail_id = request()->input('detail_id');

/* Optionsschalter */
switch ($option) {
	
	case "details_anzeigen" :
		$f = new formular ();
		$f->fieldset ( "Details anzeigen", 'details' );
		$d = new detail (); // class details neue, nicht berlussimo
		$d->detailsanzeigen ( $detail_tabelle, $detail_id );
		$f->fieldset_ende ();
		break;
	
	case "details_hinzu" :
		$f = new formular ();
		$f->fieldset ( "Details hinzufügen", 'details' );
		$d = new detail (); // class details neue, nicht berlussimo
		$vorauswahl = request()->input('vorauswahl');
		$d->form_detail_hinzu ( $detail_tabelle, $detail_id, $vorauswahl );
		$f->fieldset_ende ();
		break;
	
	case "detail_gesendet" :
		$f = new formular ();
		$f->fieldset ( "Details hinzufügen", 'details' );
		$d = new detail (); // class details neue, nicht berlussimo
		$d = new detail ();
		
		if (request()->input('detail_kat') != 'nooption') {
			if (request()->input('detail_ukat') != 'nooption') {
				$d->get_katname ( request()->input('detail_kat') );
				$u_kat_value = request()->input('detail_ukat');
				echo "$d->detail_name: $u_kat_value";
				$tabelle = request()->input('tabelle');
				$id = request()->input('id');
				$bemerkung = request()->input('bemerkung');
				$d->detail_speichern ( $tabelle, $id, $d->detail_name, $u_kat_value, $bemerkung );
			} else {
				$d->get_katname ( request()->input('detail_kat') );
				$u_kat_value = request()->input('inhalt');
				echo "$d->detail_name: $u_kat_value";
				$tabelle = request()->input('tabelle');
				$id = request()->input('id');
				$bemerkung = request()->input('bemerkung');
				$d->detail_speichern ( $tabelle, $id, $d->detail_name, $u_kat_value, $bemerkung );
			}
		}
		
		$f->fieldset_ende ();
		break;
	
	case "detail_loeschen" :
		$f = new formular ();
		$f->fieldset ( "Detail löschen", 'details' );
		$d = new detail (); // class details neue, nicht berlussimo
		if (request()->has('detail_dat')) {
			$detail_dat = request()->input('detail_dat');
			echo $detail_dat;
			$d->detail_loeschen ( $detail_dat );
		}
		
		$f->fieldset_ende ();
		break;
	
	case "bk" :
		$f = new formular ();
		$f->fieldset ( "BK", 'details' );
		$bk = new bk (); // betriebskoten
		$bk->zeige ();
		
		$f->fieldset_ende ();
		break;
	
	default :
		echo "<h1>Es wird bearbeitet ;-)</h1>";
		break;
	
	case "detail_suche" :
		$f = new formular ();
		$f->erstelle_formular ( 'Details durchsuchen', '' );
		$d = new detail ();
		$d->dropdown_details ( 'Filter Detail', 'det_name', '_det_name' );
		$f->text_feld ( 'Suchtext', 'suchtext', '', 50, 'suchtext', null );
		$f->hidden_feld ( 'option', 'detail_finden' );
		$f->send_button ( 'BtNSuch', 'Suchen' );
		$f->ende_formular ();
		break;
	
	case "detail_finden" :
		$suchtext = request()->input('suchtext');
		$det_name = request()->input('det_name');
		$d = new detail ();
		$d->finde_detail ( $suchtext, $det_name );
		break;
}
