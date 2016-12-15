<?php

$kassen_info = new kasse ();
$kassen_info->kassen_auswahl ();

$option = request()->input('option');
switch ($option) {
	
	case "rechnung_an_kasse_erfassen" :
		$form = new mietkonto ();
		$rechnungsformular = new rechnung ();
		$rechnungsformular->form_rechnung_erfassen_an_kasse ();
		$form->ende_formular ();
		break;
	
	case "rechnung_erfassen1" :
		$form = new mietkonto ();
		$form->erstelle_formular ( "Rechnungsdaten überprüfen", NULL );
		echo "<p><b>Eingegebene Rechnungsdaten:</b></p>";
		$clean_arr = post_array_bereinigen ();
		// $form->array_anzeigen($clean_arr);
		foreach ( $clean_arr as $key => $value ) {
			if (($key != 'submit_rechnung1') and ($key != 'option')) {
				// echo "$key " . $value . "<br>";
				$form->hidden_feld ( $key, $value );
			}
		}
		if ($clean_arr ['Aussteller_typ'] == $clean_arr ['Empfaenger_typ'] && $clean_arr ['Aussteller'] == $clean_arr ['Empfaenger']) {
			$fehler = true;
			fehlermeldung_ausgeben ( "Rechnungsaussteller- und Empfänger sind identisch.<br>" );
		}
		
		if (! $fehler) {
			if ($clean_arr ['Empfaenger_typ'] == 'Kasse') {
				$kassen_info = new kasse ();
				$kassen_info->get_kassen_info ( $clean_arr ['Empfaenger'] );
				$partner_info = new partner ();
				$aussteller = $partner_info->get_partner_name ( $clean_arr ['Aussteller'] );
				$empfaenger = "" . $kassen_info->kassen_name . " - " . $kassen_info->kassen_verwalter . "";
			}
			if ($clean_arr ['Empfaenger_typ'] == 'Partner') {
				$partner_info = new partner ();
				$aussteller = $partner_info->get_partner_name ( $clean_arr ['Aussteller'] );
				$empfaenger = $partner_info->get_partner_name ( $clean_arr ['Empfaenger'] );
			}
			echo "Rechnung von: <b>$aussteller</b> an <b>$empfaenger</b> vom $clean_arr[rechnungsdatum]<br>";
			echo "Rechnungsnummer: $clean_arr[rechnungsnummer]<br>";
			echo "Eingangsdatum: $clean_arr[eingangsdatum]<br>";
			if (preg_match ( "/,/i", $clean_arr ['nettobetrag'] )) {
				$clean_arr ['nettobetrag'] = nummer_komma2punkt ( $clean_arr ['nettobetrag'] );
			}
			if (preg_match ( "/,/i", $clean_arr ['bruttobetrag'] )) {
				$clean_arr ['bruttobetrag'] = nummer_komma2punkt ( $clean_arr ['bruttobetrag'] );
			}
			if (preg_match ( "/,/i", $clean_arr ['skontobetrag'] )) {
				$clean_arr ['skontobetrag'] = nummer_komma2punkt ( $clean_arr ['skontobetrag'] );
			}
			
			$netto_betrag_komma = nummer_punkt2komma ( $clean_arr ['nettobetrag'] );
			$brutto_betrag_komma = nummer_punkt2komma ( $clean_arr ['bruttobetrag'] );
			$skonto_betrag_komma = nummer_punkt2komma ( $clean_arr ['skontobetrag'] );
			echo "Nettobetrag: $netto_betrag_komma €<br>";
			echo "Bruttobetrag: $brutto_betrag_komma €<br>";
			echo "Skontobetrag: $skonto_betrag_komma €<br>";
			echo "Skonto in %: $clean_arr[skonto] %<br>";
			$skonto_satz = $clean_arr ['skonto'];
			$ein_prozent = ($clean_arr ['bruttobetrag'] / 100);
			$skonto_in_eur = $ein_prozent * $skonto_satz;
			$skonto_in_eur_komma = nummer_punkt2komma ( $skonto_in_eur );
			$skontobetrag_errechnet = $clean_arr ['bruttobetrag'] - $skonto_in_eur;
			$skontobetrag_errechnet_komma = nummer_punkt2komma ( $skontobetrag_errechnet );
			echo "Fällig am: $clean_arr[faellig_am] <br>";
			echo "Kurzbeschreibung: $clean_arr[kurzbeschreibung] <br>";
			echo "<hr><b>Errechnete Daten:</b><br>Skonto in €: $skonto_in_eur_komma  €<br>";
			echo "Skontobetrag errechnet: $skontobetrag_errechnet_komma €<br>";
			
			$form->hidden_feld ( "option", "rechnung_erfassen2" );
			$form->send_button ( "submit_rechnung2", "Rechnung speichern" );
			echo "<br>";
			backlink ();
		} else {
			backlink ();
		}
		$form->ende_formular ();
		break;
	
	case "rechnung_erfassen2" :
		$form = new mietkonto ();
		$form->erstelle_formular ( "Rechnungsdaten werden gespeichert", NULL );
		echo "<p><b>Gespeicherte Rechnungsdaten:</b></p>";
		$clean_arr = post_array_bereinigen ();
		// $form->array_anzeigen($clean_arr);
		$rechnung = new rechnung ();
		$rechnung->rechnung_speichern ( $clean_arr );
		$form->ende_formular ();
		break;
	
	case "buchungsmaske_kasse" :
		$form = new mietkonto ();
		$form->erstelle_formular ( "Buchungsformular Kasse " . session()->get('kasse'), NULL );
		$kasse = new kasse ();
		$kasse->buchungsmaske_kasse ( session()->get('kasse'));
		$form->ende_formular ();
		break;
	
	case "kassendaten_gesendet" :
		$form = new mietkonto ();
		$form->erstelle_formular ( "Buchungsdaten überprüfen " . session()->get('kasse'), NULL );
		$kasse = new kasse ();
		echo "<b>Gesendete Daten:</b><br>";
		echo "Kasse: ". request()->input('kassen_id') . "<br>";
		echo "Datum: " . request()->input('datum') . "<br>";
		echo "Zahlungstyp: " . request()->input('zahlungstyp') . "<br>";
		echo "Betrag: " . request()->input('betrag') . "<br>";
		echo "Beleg/Text: " . request()->input('beleg_text') . "<br>";
		$form->hidden_feld ( "kassen_id", request()->input('kassen_id') );
		$form->hidden_feld ( "datum", request()->input('datum') );
		$form->hidden_feld ( "zahlungstyp", request()->input('zahlungstyp') );
		$form->hidden_feld ( "betrag", request()->input('betrag') );
		$form->hidden_feld ( "beleg_text", request()->input('beleg_text') );
		$form->hidden_feld ( "kostentraeger_typ", request()->input('kostentraeger_typ') );
		$form->hidden_feld ( "kostentraeger_id", request()->input('kostentraeger_id') );
		$form->hidden_feld ( "beleg_text", request()->input('beleg_text') );
		$form->hidden_feld ( "option", "kassendaten_speichern" );
		$form->send_button ( "submit", "Speichern" );
		$form->ende_formular ();
		break;
	
	case "kassendaten_speichern" :
		$form = new mietkonto ();
		$form->erstelle_formular ( "Buchungsdaten speichern " . session()->get('kasse'), NULL );
		$kasse = new kasse ();
		$kasse->speichern_in_kassenbuch ( request()->input('kassen_id'), request()->input('betrag'), request()->input('datum'), request()->input('zahlungstyp'), request()->input('beleg_text'), request()->input('kostentraeger_typ'), request()->input('kostentraeger_id') );
		$form->ende_formular ();
		break;
	
	case "kassenbuch" :
		$form = new mietkonto ();
		if (!request()->has('jahr')) {
			$jahr = date ( "Y" );
		} else {
			$jahr = request()->input('jahr');
		}
		$form->erstelle_formular ( "Kassenbuch der Kasse " . session()->get('kasse') . " für das Jahr $jahr", NULL );
		$vorjahr = $jahr - 1;
		$jahr_aktuell = date ( "Y" );
		$kassen_id = session()->get('kasse');
		echo "<a href='" . route('legacy::kassen::index', ['option' => 'kassenbuch', 'kasse' => $kassen_id, 'jahr' => $jahr_aktuell]) . "'>Kassenbuch aktuell</a>&nbsp;";
		echo "<a href='" . route('legacy::kassen::index', ['option' => 'kassenbuch', 'kasse' => $kassen_id, 'jahr' => $vorjahr]) . "'>Kassenbuch $vorjahr</a>&nbsp;";
		echo "<a href='" . route('legacy::kassen::index', ['option' => 'kassenbuch_xls', 'kasse' => $kassen_id, 'jahr' => $jahr]) . "'>Exceldatei</a>&nbsp;<hr>";
		$g = new berlussimo_global ();
		$link = route('legacy::kassen::index', ['option' => 'kassenbuch', 'kasse' => $kassen_id], false);
		$g->monate_jahres_links ( $jahr, $link );
		$kasse = new kasse ();
		$monat = request()->input('monat');
		if (! $monat) {
			// $monat = date("m");
			$kasse->kassenbuch_anzeigen ( $jahr, session()->get('kasse'));
		}
		$kasse->monatskassenbuch_anzeigen ( $monat, $jahr, session()->get('kasse'));
		$form->ende_formular ();
		break;
	
	case "kassenbuch_xls" :
		$form = new mietkonto ();
		if (!request()->has('jahr')) {
			$jahr = date ( "Y" );
		} else {
			$jahr = request()->input('jahr');
		}
		$vorjahr = $jahr - 1;
		$jahr_aktuell = date ( "Y" );
		$kasse = new kasse ();
		$kasse->kassenbuch_als_excel ( $jahr, session()->get('kasse') );
		$form->ende_formular ();
		break;
	
	case "kasseneintrag_aendern" :
		$form = new mietkonto ();
		$jahr = date ( "Y" );
		$form->erstelle_formular ( "Kassenbuch der Kasse " . session()->get('kasse') . " für das Jahr $jahr", NULL );
		$kasse = new kasse ();
		$kasse->buchungsmaske_kasse_aendern ( request()->input('eintrag_dat') );
		$form->ende_formular ();
		break;
	
	case "kassendaten_aendern" :
		$k = new kasse ();
		$k->kassenbuch_dat_deaktivieren ( request()->input('kassen_dat_alt') );
		$k->speichern_in_kassenbuch_id ( request()->input('kassen_id'), request()->input('betrag'), request()->input('datum'), request()->input('zahlungstyp'), request()->input('beleg_text'), request()->input('kostentraeger_typ'), request()->input('kostentraeger_id'), request()->input('kassen_buch_id') );
		break;
	
	case "kasseneintrag_loeschen" :
		$k = new kasse ();
		$k->kassenbuch_dat_deaktivieren ( request()->input('eintrag_dat') );
		weiterleiten_in_sec ( route('legacy::kassen::index', ['option' => 'kassenbuch', 'kasse' => 1], false), '1' );
		break;
}
