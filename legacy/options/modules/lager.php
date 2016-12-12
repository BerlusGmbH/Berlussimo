<?php

$option = request()->input('option');
$lager_info = new lager ();

switch ($option) {
	
	default :
		session()->forget('objekt_id');
		break;
	
	case "lagerbestand" :
		session()->forget('objekt_id');
		$link = route('legacy::lager::index', ['option' => 'lagerbestand'], false);
		$lager_info->lager_auswahl_liste ( $link );
		$form = new mietkonto ();
		$form->erstelle_formular ( "Lagerbestand ->", NULL );
		$lager_info->lagerbestand_anzeigen ();
		$form->ende_formular ();
		break;
	
	case "lagerbestand_bis_form" :
		$l = new lager_v ();
		$link = route('legacy::lager::index', ['option' => 'lagerbestand_bis_form'], false);
		$lager_info->lager_auswahl_liste ( $link );
		if (! session()->has('lager_id')) {
			echo "Bitte wählen Sie ein Lager.";
		} else {
			$f = new formular ();
			$lager_bez = $lager_info->lager_bezeichnung ( session()->get('lager_id') );
			$f->erstelle_formular ( "Lagerbestand vom $lager_bez bis zum... anzeigen", '' );
			$f->datum_feld ( 'Datum bis', 'datum', '', 'datum' );
			$f->check_box_js ( 'pdf_check', '1', 'PDF-Ausgabe', '', 'checked' );
			$f->hidden_feld ( 'option', 'lagerbestand_bis' );
			$f->send_button ( 'send', 'Lagerbestand anzeigen' );
			$f->ende_formular ();
		}
		break;
	
	case "lagerbestand_bis" :
		session()->forget('objekt_id');
		$link = route('legacy::lager::index', ['option' => 'lagerbestand'], false);
		$lager_info->lager_auswahl_liste ( $link );
		$form = new mietkonto ();
		$form->erstelle_formular ( "Lagerbestand ->", NULL );
		if (request()->has('datum')) {
			/* Class_lager) */
			$l = new lager_v ();
			if (!request()->exists('pdf_check')) {
				$l->lagerbestand_anzeigen_bis ( request()->input('datum') );
			} else {
				$l->lagerbestand_anzeigen_bis_pdf ( request()->input('datum') );
			}
		} else {
			fehlermeldung_ausgeben ( "Datum eingeben" );
		}
		$form->ende_formular ();
		break;
	
	case "ra" :
		$link = route('legacy::lager::index', ['option' => 'ra'], false);
		$lager_info->lager_auswahl_liste ( $link );
		if (session()->has('lager_id')) {
			$monat = request()->input('monat');
			$jahr = request()->input('jahr');
			if (empty ( $monat )) {
				$monat = date ( "m" );
			}
			if (empty ( $jahr )) {
				$jahr = date ( "Y" );
			}
			$r = new rechnung ();
			$lager_id = session()->get('lager_id');
			$r->rechnungsausgangsbuch ( 'Lager', $lager_id, $monat, $jahr, 'Rechnung' );
		}
		break;
	
	case "re" :
		$link = route('legacy::lager::index', ['option' => 're'], false);
		$lager_info->lager_auswahl_liste ( $link );
		if (session()->has('lager_id')) {
			$monat = request()->input('monat');
			$jahr = request()->input('jahr');
			if (empty ( $monat )) {
				$monat = date ( "m" );
			}
			if (empty ( $jahr )) {
				$jahr = date ( "Y" );
			}
			$r = new rechnung ();
			$lager_id = session()->get('lager_id');
			$r->rechnungseingangsbuch ( 'Lager', $lager_id, $monat, $jahr, 'Rechnung' );
		}
		break;
	
	case "artikelsuche" :
		$l = new lager ();
		$l->artikel_suche_einkauf_form ();
		break;
	
	case "artikel_suche" :
		if (request()->has('artikel_nr')) {
			$artikel_nr = request()->input('artikel_nr');
			$l = new lager ();
			$l->artikel_suche_einkauf ( $artikel_nr, 'Lager', session()->get('lager_id') );
		}
		break;
	
	case "lieferschein_erfassen" :
		$l = new lager_v ();
		$l->form_lieferschein_erfassen ();
		break;
	
	case "lieferschein_send" :
		$l = new lager_v ();
		if (request()->has('lieferant_id') && request()->has('empfaenger_id') && request()->has('l_nr') && request()->has('l_datum')) {
			$l->lieferschein_speichern ( 'Partner', request()->input('lieferant_id'), 'Partner', request()->input('empfaenger_id'), request()->input('l_datum'), request()->input('l_nr') );
		}
		break;
	
	case "rep_kontierungsdatum" :
		$l = new lager_v ();
		$l->reparatur_kontierungsdatum ();
		break;
} // end switch

