<?php 
/**
 * BERLUSSIMO
 *
 * Hausverwaltungssoftware
 *
 *
 * @copyright    Copyright (c) 2010, Berlus GmbH, Eichkampstraße 161, 14055 Berlin
 * @link         http://www.berlus.de
 * @author       Sanel Sivac & Wolfgang Wehrheim
 * @contact		 software(@)berlus.de
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * 
 * @filesource   $HeadURL$
 * @version      $Revision$
 * @modifiedby   $LastChangedBy$
 * @lastmodified $Date$
 * 
 */

/* Klasse dient zur Aufbereitung der Stammdaten und Details des Objektes, Häuser, Einheiten, Mietverträge, Personen
 * und der Darstellung als PDF und weiterer Formate
 */
class stammdaten {
	
	
	function pdf_stamm_objekt($pdf, $objekt_id){
		$druckdatum = date("d.m.Y");
		$pdf->addText(464,730,7,"<b>Druckdatum: $druckdatum</b>");
		
		#echo "<hr>pdf_stamm_objekt($pdf, $objekt_id)<hr>";
		$o = new objekt();
		$o->get_objekt_infos($objekt_id);
		$o->anzahl_haeuser_objekt($objekt_id);
		$anz_einheiten = $o->anzahl_einheiten_objekt($objekt_id);
		
		echo '<pre>';
		#print_r($o);
		echo "<hr>";
		
		
		$pdf_tab[0]['BEZ'] = 'Objekt Kurzname';
		$pdf_tab[0]['TXT'] = $o->objekt_kurzname;
		
		
		$pdf_tab[1]['BEZ'] = 'Eigentümer/Verwalter';
		$pdf_tab[1]['TXT'] = $o->objekt_eigentuemer;
		
		$pdf_tab[2]['BEZ'] = 'Eigentümer/Gründer';
		$pdf_tab[2]['TXT'] = $o->objekt_eigentuemer_pdf;
		
		
		$pdf_tab[3]['BEZ'] = 'Anzahl Häuser';
		$pdf_tab[3]['TXT'] = $o->anzahl_haeuser;
				
		$pdf_tab[4]['BEZ'] = 'Anzahl Einheiten';
		$pdf_tab[4]['TXT'] = $anz_einheiten;
		
		$o->objekt_informationen($objekt_id);
		
		$pdf_tab[5]['BEZ'] = 'Anzahl Geldkonten';
		$pdf_tab[5]['TXT'] = count($o->geld_konten_arr);
		
		
		$d = new detail;
		$details_arr = $d->finde_alle_details_arr('Objekt',  $objekt_id);
		
		$anz_details = count($details_arr);
		
		if($anz_details){
		//print_r($details_arr);
			$z = 6;
			for($a=0;$a<$anz_details;$a++){
				$pdf_tab[$z]['BEZ'] = $details_arr[$a]['DETAIL_NAME'];
				$pdf_tab[$z]['TXT'] = ucfirst(ltrim(rtrim(strip_tags($details_arr[$a]['DETAIL_INHALT']))));
				$z++;
			}
		}
		
		
		$cols = array('BEZ'=>"Bezeichnung", 'TXT'=>"");
		$pdf->ezTable($pdf_tab,$cols,"Stammdaten Objekt $o->objekt_kurzname",
				array('showHeadings'=>0,'shaded'=>1, 'titleFontSize' => 9, 'fontSize' => 8, 'xPos'=>50,'xOrientation'=>'right',  'width'=>500,'cols'=>array('BEZ'=>array('justification'=>'left', 'width'=>140))));
		unset($pdf_tab);
		
		
		/*Geldkonten*/
		$gk = new gk;
		$gk_ids_arr = $gk->get_zuweisung_kos_arr('Objekt', $objekt_id);
		if(is_array($gk_ids_arr)){
			$anz_gk = count($gk_ids_arr);
			for($g=0;$g<$anz_gk;$g++){
				$gk_id = $gk_ids_arr[$g]['KONTO_ID'];
				$gki = new geldkonto_info;
				$gki->geld_konto_details($gk_id);
				$pdf_gk[$g]['IBAN'] = $gki->IBAN1;
				$pdf_gk[$g]['BIC'] = $gki->BIC;
				$pdf_gk[$g]['BEGUENSTIGTER'] = $gki->beguenstigter;
				$pdf_gk[$g]['BANK'] = $gki->bankname;
			}
			#print_r($gk_ids_arr);
			#die();
		
			$cols = array('BEGUENSTIGTER'=>"Begünstigter", 'IBAN'=>"IBAN", 'BIC'=>"BIC", 'BANK'=>"Bankname");
					$pdf->ezSetDy(-5); //abstand
					$pdf->ezTable($pdf_gk,$cols,"Geldkonten Objekt",
							array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 8, 'xPos'=>50,'xOrientation'=>'right',  'width'=>500,'cols'=>array('BEZ'=>array('justification'=>'left', 'width'=>140))));
			unset($pdf_gk);
		}
		
		
		
		
		
		$this->pdf_stamm_objekt_haus($pdf, $objekt_id);
		#$this->pdf_stamm_haus($pdf, $objekt_id);
		#$pdf->ezTable($pdf_tab);
		#print_r($o);
		#print_r($pdf_tab);
		
		
		/*$p = new partners();
		$p->get_partner_info($o->objekt_eigentuemer_id);
		print_r($p);*/
		#die();
		
		$this->stamm_einheiten_objekt($pdf, $objekt_id);
		
	}
	
	
	
	function pdf_stamm_objekt_haus($pdf, $objekt_id){
		$o = new objekt;
		$o->get_objekt_infos($objekt_id);
		$haus_arr = $o->haeuser_objekt_in_arr($objekt_id);
		if(is_array($haus_arr)){
			#print_r($haus_arr);
			#die();
			$anz_haus = count($haus_arr);
			for($a=0;$a<$anz_haus;$a++){
			$haus_id = $haus_arr[$a]['HAUS_ID'];
			$h = new haus;
			$h->get_haus_info($haus_id);
			$z = $a+1;
			$pdf_tab[$a]['HAUS_ID'] = $haus_id;
			$pdf_tab[$a]['BEZ'] = "Haus $z";
			$pdf_tab[$a]['TXT'] = "$h->haus_strasse $h->haus_nummer, $h->haus_plz $h->haus_stadt";
			
			#print_r($h);
			}
		
			$cols = array('BEZ'=>"Bezeichnung", 'TXT'=>"");
			$pdf->ezSetDy(-10); //abstand
			$pdf->ezTable($pdf_tab,$cols,"Häuser im Objekt $o->objekt_kurzname",
					array('showHeadings'=>0,'shaded'=>1, 'titleFontSize' => 9, 'fontSize' => 8, 'xPos'=>50,'xOrientation'=>'right',  'width'=>500,'cols'=>array('BEZ'=>array('justification'=>'left', 'width'=>140))));
			unset($pdf_tab);
			
			/*Detail Tabellen*/
			for($a=0;$a<$anz_haus;$a++){
				$haus_id = $haus_arr[$a]['HAUS_ID'];
				$this->pdf_stamm_haus($pdf, $haus_id);
			}
			
		
		}
	#die();
	}
	
	
	
	function pdf_stamm_haus($pdf, $haus_id){
		$h = new haus;
		$h->get_haus_info($haus_id);
		
		$anz_einheiten = count($h->liste_aller_einheiten_im_haus($haus_id));
		
		$pdf_tab[0]['BEZ'] = "Anzahl Einheiten";
		$pdf_tab[0]['TXT'] = $anz_einheiten;
		
		$pdf_tab[1]['BEZ'] = "Fläche aus Mietverträgen";
		$pdf_tab[1]['TXT'] = nummer_punkt2komma_t($h->get_qm_gesamt($haus_id))." m²";
		
		
		
		$d = new detail;
		$details_arr = $d->finde_alle_details_arr('Haus',  $haus_id);
		
		$anz_details = count($details_arr);
		
		if($anz_details){
			//print_r($details_arr);
			$z=3;
			for($a=0;$a<$anz_details;$a++){
				$pdf_tab[$z]['BEZ'] = $details_arr[$a]['DETAIL_NAME'];
				$pdf_tab[$z]['TXT'] = ucfirst(ltrim(rtrim(strip_tags($details_arr[$a]['DETAIL_INHALT']))));
				$z++;
			}
		
		
			$cols = array('BEZ'=>"Bezeichnung", 'TXT'=>"");
			$pdf->ezSetDy(-10); //abstand
			$pdf->ezTable($pdf_tab,$cols,"Details vom Haus $h->haus_strasse $h->haus_nummer, $h->haus_plz $h->haus_stadt",
					array('showHeadings'=>0,'shaded'=>1, 'titleFontSize' => 9, 'fontSize' => 8, 'xPos'=>50,'xOrientation'=>'right',  'width'=>500,'cols'=>array('BEZ'=>array('justification'=>'left', 'width'=>140))));
				
		 unset($pdf_tab);
			
			
			
		}
	}

	
	
	function stamm_einheiten_objekt($pdf, $objekt_id){
		$pdf->ezNewPage();
		$druckdatum = date("d.m.Y");
		$pdf->addText(464,730,7,"<b>Druckdatum: $druckdatum</b>");
		$o = new objekt();
		$o->get_objekt_infos($objekt_id);
		$einheit_arr = $o->einheiten_objekt_arr($objekt_id);
		$anz_einheiten = count($einheit_arr);
		#print_r($einheit_arr);
		
		/*Liste Einheiten*/
		$cols = array('EINHEIT_KURZNAME'=>"Einheit", 'TYP'=>"Typ", 'EINHEIT_LAGE'=>"Lage", 'EINHEIT_QM'=>"Einheit m²", 'HAUS_STRASSE'=>"Strasse", 'HAUS_NUMMER'=>"Hausnummer", 'HAUS_PLZ'=>"PLZ", 'HAUS_STADT'=>"Ort");
		$pdf->ezSetDy(-10); //abstand
		$pdf->ezTable($einheit_arr,$cols,"Einheitenliste vom Objekt $o->objekt_kurzname",
		array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 10, 'fontSize' => 8, 'xPos'=>50,'xOrientation'=>'right',  'width'=>500,'cols'=>array('EINHEIT_KURZNAME'=>array('justification'=>'left', 'width'=>90), 'HAUS_STRASSE'=>array('justification'=>'left', 'width'=>80), 'HAUS_NUMMER'=>array('justification'=>'right', 'width'=>25), 'EINHEIT_QM'=>array('justification'=>'right', 'width'=>35), 'HAUS_PLZ'=>array('justification'=>'right', 'width'=>33))));
		
		
		
		
		for($a=0;$a<$anz_einheiten;$a++){
			$pdf->ezNewPage();
			$druckdatum = date("d.m.Y");
			$pdf->addText(464,730,7,"<b>Druckdatum: $druckdatum</b>");
			$einheit_id=$einheit_arr[$a]['EINHEIT_ID'];
			$e = new einheit();
			$e->get_einheit_info($einheit_id);
			#print_r($e);
			
			$z=0;
			$pdf_tab[$z]['BEZ'] = "Objekt";
			$pdf_tab[$z]['TXT'] = $e->objekt_name;
			$z++;
			$pdf_tab[$z]['BEZ'] = "Einheit";
			$pdf_tab[$z]['TXT'] = $e->einheit_kurzname;
			$z++;
			$pdf_tab[$z]['BEZ'] = "Haus";
			$pdf_tab[$z]['TXT'] = "$e->haus_strasse $e->haus_nummer, $e->haus_plz $e->haus_stadt";
			$z++;
			$pdf_tab[$z]['BEZ'] = "Typ";
			$pdf_tab[$z]['TXT'] = $e->typ;
			$z++;
			$pdf_tab[$z]['BEZ'] = "Fläche";
			$pdf_tab[$z]['TXT'] = $e->einheit_qm_d;
			$z++;
			$pdf_tab[$z]['BEZ'] = "Lage";
			$pdf_tab[$z]['TXT'] = $e->einheit_lage;
			$z++;
			if(!empty($e->aufzug_prozent)){
			$pdf_tab[$z]['BEZ'] = "Aufzug %";
			$pdf_tab[$z]['TXT'] = $e->aufzug_prozent;
			$z++;
			}
					
					
		/*Details*/
			$d = new detail;
			$details_arr = $d->finde_alle_details_arr('Einheit',  $einheit_id);
			$anz_details = count($details_arr);
			
			if($anz_details){
				//print_r($details_arr);
				#$z=3;
				for($d=0;$d<$anz_details;$d++){
					$pdf_tab[$z]['BEZ'] = $details_arr[$d]['DETAIL_NAME'];
					$pdf_tab[$z]['TXT'] = ucfirst(ltrim(rtrim(strip_tags($details_arr[$d]['DETAIL_INHALT']))));
					$z++;
				}	
				$cols = array('BEZ'=>"Bezeichnung", 'TXT'=>"");
				$pdf->ezTable($pdf_tab,$cols,"Details zu Einheit $e->einheit_kurzname",
						array('showHeadings'=>0,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 8, 'xPos'=>50,'xOrientation'=>'right',  'width'=>500,'cols'=>array('BEZ'=>array('justification'=>'left', 'width'=>140))));
				unset($pdf_tab);
			}
		
		/*Eigentümer*/
		
		$weg = new weg();
		$et_arr = $weg->get_eigentuemer_arr_2($einheit_id, 'ASC');
		
		if(is_array($et_arr)){
		#	$pdf->ezTable($et_arr);
			$anz_et = count($et_arr);
			for($et=0;$et<$anz_et;$et++){
				$et_id = $et_arr[$et]['ID'];
				$weg = new weg();
				$weg->get_eigentumer_id_infos3($et_id);
			#	print_r($weg);
		#	die();
				
				$pdf_tab_et[$et]['ET_NAME'] = $weg->empf_namen;
				$pdf_tab_et[$et]['ET_NAME1'] = $weg->empf_namen_u;
				$pdf_tab_et[$et]['PERSONEN'] = $weg->anz_personen;
				$pdf_tab_et[$et]['VON'] = date_mysql2german($weg->eigentuemer_von);
				$pdf_tab_et[$et]['BIS'] = date_mysql2german($weg->eigentuemer_bis);
				
					if($weg->einheit_qm!=$weg->einheit_qm_weg){
					$pdf_tab_et[$et]['ET_QM'] = "<b>$weg->einheit_qm_weg_d</b>";
					}else{
					$pdf_tab_et[$et]['ET_QM'] = $weg->einheit_qm_weg_d;
					}
				
				$pdf_tab_et[$et]['ET_CODE'] = $weg->et_code;
				
							
				
				
				#$pdf->ezTable($pdf_tab_et);
				$cols = array('ET_NAME'=>"Eigentümer Namen", 'PERSONEN'=>"Anz. Personen", 'VON'=>"Von",  'BIS'=>"Bis",);
				$etnr=$et+1;
				$ueberschrift = "$etnr. Eigentümer - $weg->einheit_kurzname";
				$pdf->ezSetDy(-5); //abstand
				$pdf->ezTable($pdf_tab_et,$cols,"$ueberschrift",
						array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 8, 'xPos'=>50,'xOrientation'=>'right',  'width'=>500,'cols'=>array('BEZ'=>array('justification'=>'left', 'width'=>140))));
							
				unset($pdf_tab_et);
				
				
				/*Personendetails*/
				for($pp=0;$pp<$weg->anz_personen;$pp++){
					$person_id = $weg->personen_id_arr1[$pp]['PERSON_ID'];
					$pe = new person;
					$pe->get_person_infos($person_id);
					
					/*Details*/
					$d = new detail;
					$details_arr = $d->finde_alle_details_arr('Person',  $person_id);
					$anz_details = count($details_arr);
						
					if($anz_details){
						//print_r($details_arr);
						$z=0;
						for($d=0;$d<$anz_details;$d++){
							if(!empty($details_arr[$d]['DETAIL_NAME'])){
							$pdf_tabp[$z]['BEZ'] = $details_arr[$d]['DETAIL_NAME'];
							$pdf_tabp[$z]['TXT'] = ucfirst(ltrim(rtrim(strip_tags($details_arr[$d]['DETAIL_INHALT']))));
							$z++;
							}
						}
						if(is_array($pdf_tabp)){
						$cols = array('BEZ'=>"Bezeichnung", 'TXT'=>"");
						$pdf->ezSetDy(-5); //abstand
						$pdf->ezTable($pdf_tabp,$cols,"Details zu Person <b>$pe->person_nachname $pe->person_vorname</b>",
								array('showHeadings'=>0,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 8, 'xPos'=>50,'xOrientation'=>'right',  'width'=>500,'cols'=>array('BEZ'=>array('justification'=>'left', 'width'=>140))));
						unset($pdf_tabp);
						}
					}
				}
				
			
			/*Geldkonten*/
				$gk = new gk;
				$gk_ids_arr = $gk->get_zuweisung_kos_arr('Eigentuemer', $et_id);
				if(is_array($gk_ids_arr)){
					$anz_gk = count($gk_ids_arr);
					for($g=0;$g<$anz_gk;$g++){
						$gk_id = $gk_ids_arr[$g]['KONTO_ID'];
						$gki = new geldkonto_info;
						$gki->geld_konto_details($gk_id);
						$pdf_gk[$g]['IBAN'] = $gki->IBAN1;
						$pdf_gk[$g]['BIC'] = $gki->BIC;
						$pdf_gk[$g]['BEGUENSTIGTER'] = $gki->beguenstigter;
						$pdf_gk[$g]['BANK'] = $gki->bankname;
					}
					#print_r($gk_ids_arr);
					#die();
				
					$cols = array('BEGUENSTIGTER'=>"Begünstigter", 'IBAN'=>"IBAN", 'BIC'=>"BIC", 'BANK'=>"Bankname");
					$pdf->ezSetDy(-5); //abstand
					$pdf->ezTable($pdf_gk,$cols,"Geldkontenübersicht des Eigentümers",
							array('showHeadings'=>1,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 8, 'xPos'=>50,'xOrientation'=>'right',  'width'=>500,'cols'=>array('BEZ'=>array('justification'=>'left', 'width'=>140))));
					unset($pdf_gk);
				}
			
			
			}
			
			
			
		}
		
		
		
		/*Mieter*/
		$e = new einheit;
		$mv_ids = $e->get_mietvertrag_ids($einheit_id);
		if(is_array($mv_ids)){
			#$pdf->ezNewPage();
			#print_r($mv_ids);
			#die();
			$anz_mv = count($mv_ids);
			for($m=0;$m<$anz_mv;$m++){
				$mv_id = $mv_ids[$m]['MIETVERTRAG_ID'];
				
				
				
				
				
				$mv = new mietvertraege;
				$mv->get_mietvertrag_infos_aktuell($mv_id);
				$z=0;
				$pdf_mv[$z]['BEZ'] = 'AKTUELL';
				if($mv->mietvertrag_aktuell==1){
				$pdf_mv[$z]['TXT'] = "JA";
				}else{
				$pdf_mv[$z]['TXT'] = "<b>NEIN</b>";
				}
				$z++;
				
				$pdf_mv[$z]['BEZ'] = 'MIETER';
				$pdf_mv[$z]['TXT'] = "$mv->personen_name_string";
				$z++;
				
				$anz_pmv = count($mv->personen_ids);
				$pdf_mv[$z]['BEZ'] = 'PERSONEN';
				$pdf_mv[$z]['TXT'] = $anz_pmv;
				$z++;
				$pdf_mv[$z]['BEZ'] = 'ANSCHRIFT';
				$pdf_mv[$z]['TXT'] = "$mv->haus_strasse $mv->haus_nr, $mv->haus_plz $mv->haus_stadt";
				$z++;
				$pdf_mv[$z]['BEZ'] = 'EINZUG';
				$pdf_mv[$z]['TXT'] = $mv->mietvertrag_von_d;
				$z++;
				$pdf_mv[$z]['BEZ'] = 'AUSZUG';
				$pdf_mv[$z]['TXT'] = $mv->mietvertrag_bis_d;
				$z++;
				$pdf_mv[$z]['BEZ'] = 'EINHEIT_TYP';
				$pdf_mv[$z]['TXT'] = $mv->einheit_typ;
				$z++;
				$pdf_mv[$z]['BEZ'] = 'ANREDE';
				$pdf_mv[$z]['TXT'] = ltrim(rtrim($mv->mv_anrede));
				$z++;
				$pdf_mv[$z]['BEZ'] = 'ANZ_ZUSTELL';
				$pdf_mv[$z]['TXT'] = $mv->anz_verzugsanschriften;
				$z++;
				$pdf_mv[$z]['BEZ'] = 'ANZ_VERZUG';
				$pdf_mv[$z]['TXT'] = $mv->anz_verzugsanschriften;
				$z++;
				
				/*Saldo berechnen*/
				$mza = new miete();
				$mza->mietkonto_berechnung($mv_id);
				
				$pdf_mv[$z]['BEZ'] = "<b>MIETER SALDO ".date("d.m.Y")."</b>";
				$pdf_mv[$z]['TXT'] = "<b>$mza->erg EUR</b>";
				$z++;
				unset($mza);
				
				
				/*Details MV*/
				$d = new detail;
				$details_arr = $d->finde_alle_details_arr('Mietvertrag',  $mv_id);
				$anz_details = count($details_arr);
				
				if($anz_details){
					//print_r($details_arr);
					#$z=0;
					for($d=0;$d<$anz_details;$d++){
						if(!empty($details_arr[$d]['DETAIL_NAME'])){
							$pdf_mv[$z]['BEZ'] = $details_arr[$d]['DETAIL_NAME'];
							$pdf_mv[$z]['TXT'] = ucfirst(ltrim(rtrim(strip_tags($details_arr[$d]['DETAIL_INHALT']))));
							$z++;
						}
					}		
				}
					
				/*Details zu den Mietern bzw. Personen aus dem Mietvertrag, Tel, etc*/
				$pdf->ezNewPage();
				$druckdatum = date("d.m.Y");
				$pdf->addText(464,730,7,"<b>Druckdatum: $druckdatum</b>");
				
				$cols = array('BEZ'=>"Bezeichnung", 'TXT'=>"");
				$pdf->ezTable($pdf_mv,$cols,"Mietvertragsdaten $mv->einheit_kurzname | $mv->personen_name_string</b>",
						array('showHeadings'=>0,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 8, 'xPos'=>50,'xOrientation'=>'right',  'width'=>500,'cols'=>array('BEZ'=>array('justification'=>'left', 'width'=>140))));
				$pdf->ezSetDy(-5); //abstand
				unset($pdf_mv);
				
				#print_r($mv);
				#die();
				$z=0;
				for($ppe=0;$ppe<$anz_pmv;$ppe++){
					$person_id_mv = $mv->personen_ids[$ppe]['PERSON_MIETVERTRAG_PERSON_ID'];
					$pe = new person;
					$pe->get_person_infos($person_id_mv);
					
					$p_a = $ppe+1;
					$pdf_pe[$z]['BEZ'] = "<b>MIETER $p_a</b>";
					$pdf_pe[$z]['TXT'] = "<b>$pe->person_nachname $pe->person_vorname</b>";
					$z++;
					$pdf_pe[$z]['BEZ'] = "GEBURTSTAG";
					$pdf_pe[$z]['TXT'] = $pe->person_geburtstag;
					$z++;
					
					
					/*Details PERSON aus MV*/
					$d = new detail;
					$details_arr = $d->finde_alle_details_arr('Person',  $person_id_mv);
					$anz_details = count($details_arr);
					
					if($anz_details){
						//print_r($details_arr);
						#$z=0;
						for($d=0;$d<$anz_details;$d++){
							if(!empty($details_arr[$d]['DETAIL_NAME'])){
								$pdf_pe[$z]['BEZ'] = $details_arr[$d]['DETAIL_NAME'];
								$pdf_pe[$z]['TXT'] = ucfirst(ltrim(rtrim(strip_tags($details_arr[$d]['DETAIL_INHALT']))));
								$z++;
							}
						}
					}
					
				}
				
				$cols = array('BEZ'=>"Bezeichnung", 'TXT'=>"");
				$pdf->ezTable($pdf_pe,$cols,"Informationen über Personen im Mietvertrag</b>",
						array('showHeadings'=>0,'shaded'=>1, 'titleFontSize' => 8, 'fontSize' => 8, 'xPos'=>50,'xOrientation'=>'right',  'width'=>500,'cols'=>array('BEZ'=>array('justification'=>'left', 'width'=>140))));
				$pdf->ezSetDy(-5); //abstand
				unset($pdf_pe);
				
				
				/*Mietdefinition zum MV*/
				$me = new mietentwicklung;
				$jahr = date("Y");
				$monat = date("m");
				#$me->get_mietentwicklung_infos($mv_id, $jahr, $monat);
				#natsort($me->kostenkategorien);
				$me->pdf_mietentwicklung($pdf, $mv_id);
				
				#$this->plotfile_me($pdf, $mv_id, 570, 150);
				
				$pdf->ezNewPage();
				$druckdatum = date("d.m.Y");
				$pdf->addText(464,730,7,"<b>Druckdatum: $druckdatum</b>");
				$arr_stat = $this->me_12($mv_id,2013);
				$this->plot2pdf($pdf, $mv_id, $arr_stat, 2013, 570,150);
				
				$pdf->ezSetDy(-160); //abstand
				$arr_stat = $this->me_12($mv_id,2014);
				$this->plot2pdf($pdf, $mv_id, $arr_stat, 2014, 570,150);
				
				$pdf->ezSetDy(-160); //abstand
				$arr_stat = $this->me_12($mv_id,2015);
				$this->plot2pdf($pdf, $mv_id, $arr_stat,2015, 570,150);
				
				$pdf->ezSetDy(-160); //abstand
				$arr_stat = $this->me_12($mv_id,2016);
				$this->plot2pdf($pdf, $mv_id, $arr_stat,2016, 570,150);
				
				
				/*Mietkontenblatt*/
				#$pdf->ezNewPage();
				#$druckdatum = date("d.m.Y");
				#$pdf->addText(464,730,7,"<b>Druckdatum: $druckdatum</b>");
				#$mz = new miete();
				#$mz->mkb2pdf_mahnung_letzter_nullstand($pdf,$mv_id);
				#unset($mz);
				#echo '<pre>';
				#print_r($mz);
				#die();
				
				
				
				unset($pdf_mv);
			}
		}
		
		
		
		}//ende for Einheiten
		#die();
	}

	function me_12($mv_id, $jahr){
		$mvs = new mietvertraege();;
		$mvs->get_mietvertrag_infos_aktuell($mv_id);
		
		$mk = new mietkonto;
		#$von = date("Y-m-d");
		$von = "$jahr-01-01";
		$bis_j = date("Y")+1;
		$bis = $bis_j.'-'.date("m-d");
		$bis = "$jahr-12-31";
		$mz = new miete;
		
		$monate = $mz->diff_in_monaten($von, $bis);
		
		#$jahr = date("Y");
		$monat = 0;
		$z = 0;
		for($a=0;$a<$monate;$a++){
			$monat = sprintf('%02d',$monat+1);
			$mk->kaltmiete_monatlich($mv_id,$monat,$jahr);
		
			$arr_stat[$z][0] = "$monat/$jahr\n$mk->ausgangs_kaltmiete\nEUR\n";
			$arr_stat[$z][1] = $mk->ausgangs_kaltmiete;
		
			if($monat=='12'){
				$monat=0;
				$jahr++;
			}
			$z++;
		}
		return $arr_stat;
	}
	
	function plot2pdf($pdf, $mv_id, $arr_stat, $jahr, $w=800, $h=600){
		$mvs = new mietvertraege();;
		$mvs->get_mietvertrag_infos_aktuell($mv_id);
		
		#require_once 'phplot.php';
		include_once(HAUPT_PATH.'/'.BERLUS_PATH."/classes/phplot.php");
		$plot = new PHPlot($w,$h);
		$plot->SetImageBorderType('plain');
		$plot->SetPlotType('bars');
		$plot->SetDataType('text-data');
		$plot->SetDataValues($arr_stat);
		
		# Main plot title:
		$plot->SetTitle('MIETENTWICKLUNG'." $jahr | $mvs->einheit_kurzname \n $mvs->personen_name_string");
		
		# No 3-D shading of the bars:
		$plot->SetShading(0);
		
		# Make a legend for the 3 data sets plotted:
		#$plot->SetLegend(array('Mieteinnahmen', 'Leerstand'));
		
		$plot->SetLegend(array('MIETE'));
		
		# Turn off X tick labels and ticks because they don't apply here:
		$plot->SetXTickLabelPos('none');
		$plot->SetXTickPos('none');
		#$plot->SetYLabelFontSize(8);
		
		//Draw it
		$plot->SetIsInline(true);
		$img = $plot->DrawGraph();
		$px = 'px';
		
		#echo "<hr>$plot->img ";
		#$plot->PrintImageFrame();
		#$hhh = $plot->PrintImage();
		$ima = $plot->EncodeImage();
		
		#echo "<a style=\"width:$w$px;heigth:$h$px;\" href=\"?option=stat_mv_big&mv_id=$mv_id\"><img style=\"width:$w$px;heigth:$h$px;\" src=\"$plot->img\"></img></a>";
		#die();
		#echo "<img src=\"$ima\">";
		#die();
		if($mvs->mietvertrag_aktuell==1){
		$pdf->addPngFromFile($ima,$pdf->x+10,$pdf->y-$h,$w,$h);
		}	
	}
	
	
	
	function plotfile_me($pdf, $mv_id, $w=800, $h=600){
		$mvs = new mietvertraege();;
		$mvs->get_mietvertrag_infos_aktuell($mv_id);
		$mk = new mietkonto;
		$datum_mietdefinition = $mk->datum_1_mietdefinition($mv_id);
		#echo "<h1>$datum_mietdefinition</h1>";
		$a_dat = explode('-',$datum_mietdefinition);
		$jahr_a = date("Y")-2;
		$jahr_e = date("Y")+3;
		$jahre = $jahr_e - $jahr_a;
		$z = 0;
		for($jahr=$jahr_a;$jahr<=$jahr_e;$jahr++){
			$monat = date("m");
			$mk->kaltmiete_monatlich($mv_id,$monat,$jahr);
			if($jahr>$jahr_a){
		
				$miete_vorjahr = $arr_stat[$z-1][1];
				$prozent = ($mk->ausgangs_kaltmiete - $miete_vorjahr)/($miete_vorjahr/100);
			}else{
				$prozent = 0;
			}
			$prozent = nummer_punkt2komma($prozent);
			$arr_stat[$z][0] = "$jahr\n$mk->ausgangs_kaltmiete\nEUR\n$prozent %";
			$arr_stat[$z][1] = $mk->ausgangs_kaltmiete;
		
		
			$z++;
		}
		#print_r($arr_stat);
		
		require_once 'phplot.php';
		$plot = new PHPlot($w,$h);
		$plot->SetImageBorderType('plain');
		$plot->SetPlotType('bars');
		$plot->SetDataType('text-data');
		$plot->SetDataValues($arr_stat);
		
		# Main plot title:
		$plot->SetTitle('MIETENTWICKLUNG'." $mvs->einheit_kurzname \n $mvs->personen_name_string");
		
		# No 3-D shading of the bars:
		$plot->SetShading(0);
		
		# Make a legend for the 3 data sets plotted:
		#$plot->SetLegend(array('Mieteinnahmen', 'Leerstand'));
		
		$plot->SetLegend(array('MIETE'));
		
		# Turn off X tick labels and ticks because they don't apply here:
		$plot->SetXTickLabelPos('none');
		$plot->SetXTickPos('none');
		
		
		//Draw it
		$plot->SetIsInline(true);
		$img = $plot->DrawGraph();
		$px = 'px';
		
		#echo "<hr>$plot->img ";
		#$plot->PrintImageFrame();
		#$hhh = $plot->PrintImage();
		$ima = $plot->EncodeImage();
		
		#echo "<a style=\"width:$w$px;heigth:$h$px;\" href=\"?option=stat_mv_big&mv_id=$mv_id\"><img style=\"width:$w$px;heigth:$h$px;\" src=\"$plot->img\"></img></a>";
		#die();
		#echo "<img src=\"$ima\">";
		#die();
		if($mvs->mietvertrag_aktuell==1){
		$pdf->ezNewPage();
		$druckdatum = date("d.m.Y");
		$pdf->addText(464,730,7,"<b>Druckdatum: $druckdatum</b>");
		$pdf->addPngFromFile($ima,$pdf->x+10,$pdf->y-$h,$w,$h);
		}
	}
	
	
	
}//end class




?>