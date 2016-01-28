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
 * @filesource   $HeadURL$
 * @version      $Revision$
 * @modifiedby   $LastChangedBy$
 * @lastmodified $Date$
 * 
 */

	if(file_exists('pdfclass/class.ezpdf.php')){
	include_once('pdfclass/class.ezpdf.php');
	}
	if(file_exists('classes/class_bpdf.php')){
	include_once('classes/class_bpdf.php');
	}

	
	
	class serienbrief{

	function vorlage_waehlen($empf_typ=null, $kat=null){
	#die($empf_typ);
		if($empf_typ==null && $kat==null){
	$db_abfrage = "SELECT * FROM PDF_VORLAGEN ORDER BY KURZTEXT ASC";
	}

	if($empf_typ && $kat==null){
	$db_abfrage = "SELECT * FROM PDF_VORLAGEN WHERE EMPF_TYP='$empf_typ' ORDER BY KURZTEXT ASC";
	}

	if($empf_typ==null && $kat){
	$db_abfrage = "SELECT * FROM PDF_VORLAGEN WHERE KAT='$kat' ORDER BY KURZTEXT ASC";
	}

	if($empf_typ!=null && $kat!=null){
	$db_abfrage = "SELECT * FROM PDF_VORLAGEN WHERE KAT='$kat' && EMPF_TYP='$empf_typ' ORDER BY KURZTEXT ASC";
	}
		
		
	//ALT	$db_abfrage = "SELECT * FROM PDF_VORLAGEN WHERE EMPF_TYP='$empf_typ' && KAT='$kat' ORDER BY KURZTEXT ASC";
	$result = mysql_query($db_abfrage) or die(mysql_error());	

	
	/*Wenn keine Vorlagen, dann alle anzeigen*/
	$numrows = mysql_numrows($result);
	if(!$numrows){
	$db_abfrage = "SELECT * FROM PDF_VORLAGEN ORDER BY KURZTEXT ASC";
	$result = mysql_query($db_abfrage) or die(mysql_error());
	}
		
	$numrows = mysql_numrows($result);
	
	
	if($numrows){
	start_table();
	$link_kat = "<a href=\"?daten=weg&option=serienbrief\">Alle Kats anzeigen</a>";
	echo "<tr><th>Vorlage / Betreff</th><th>BEARBEITEN</th><th>KAT</th><th>ANSEHEN</th><th>ERSTELLEN</th></tr>";
	echo "<tr><td><b>$empf_typ<b></td><td>$link_kat</td><td></td><td></td><td></td></tr>";
	
		while ($row = mysql_fetch_assoc($result)){
		$dat = $row['DAT'];
		$kurztext = $row['KURZTEXT'];
		$text = $row['TEXT'];
		$kat = $row['KAT'];
		#$link_erstellen = "<a href=\"?daten=bk&option=serienbrief_pdf&vorlagen_dat=$dat&emailsend\">Serienbrief erstellen (PDF & Email)</a>";
		
		if($empf_typ=='Eigentuemer'){
		$link_ansehen = "<a href=\"?daten=weg&option=serienbrief_pdf&vorlagen_dat=$dat\">Serienbrief PDF ansehen</a>";
		$link_kat = "<a href=\"?daten=weg&option=serienbrief&kat=$kat\">$kat</a>";
		}
		
		if($empf_typ=='Partner'){
			$link_ansehen = "<a href=\"?daten=partner&option=serienbrief_pdf&vorlagen_dat=$dat\">Serienbrief PDF ansehen</a>";
			$link_kat = "<a href=\"?daten=partner&option=serienbrief&kat=$kat\">$kat</a>";
			
		}
		
		$link_bearbeiten = "<a href=\"?daten=bk&option=vorlage_bearbeiten&vorlagen_dat=$dat\">Vorlage bearbeiten</a>";
		
		
		echo "<tr><td>$kurztext</td><td>$link_kat</td><td>$link_bearbeiten</td><td>$link_ansehen</td><td></td></tr>";
	#echo "$link";
		}
	end_table();
	}
	else{
	echo "Keine Vorlagen AA3";
	}
	
	}//end function
		

	
function erstelle_brief_vorlage($v_dat, $empf_typ, $empf_id_arr, $option='0'){
	$anz_empf = count($empf_id_arr);
	if($anz_empf>0){

		if($empf_typ=='Eigentuemer'){
		
			 
			
			
		$pdf = new Cezpdf('a4', 'portrait');
	 	$bpdf = new b_pdf;
		$bpdf->b_header($pdf, 'Partner', $_SESSION[partner_id], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6);
		$pdf->ezStopPageNumbers(); //seitennummerierung beenden
		
		$anz_eigentuemer = count($empf_id_arr);
		for ($index = 0; $index < $anz_eigentuemer; $index++) {
			
	 	$e_id = $empf_id_arr[$index];
				
		$weg = new weg();
		$weg->get_eigentumer_id_infos3($e_id);
		$monat = date("m");
		$jahr = date("Y");
		$this->hausgeld_monatlich_de = nummer_punkt2komma($weg->get_sume_hausgeld('Einheit', $weg->einheit_id, $monat, $jahr)*-1);
		$this->hausgeld_monatlich_en = $weg->get_sume_hausgeld('Einheit', $weg->einheit_id, $monat, $jahr)*-1;
		#print_r($weg);
		#die();
		
		
		
		$dets = new detail();
			
		$gk = new geldkonto_info();
		$gk->geld_konto_ermitteln('Objekt', $weg->objekt_id);
	 	
		$bpdf->get_texte($v_dat);
	 		
		
		/*Faltlinie*/
		$pdf->setLineStyle(0.2);
		#$pdf_einzeln->setLineStyle(0.2);
		$pdf->line(5,542,20,542);
		#$pdf_einzeln->line(5,542,20,542);
		
			
		$pdf->ezText($weg->post_anschrift,11);		
		
		
		
		###############################################################				
		$pdf->ezSetDy(-60);
		#$pdf->ezSetDy(-80);
		#$pdf_einzeln->ezSetDy(-80);
		if(!isset($_REQUEST['druckdatum']) or empty($_REQUEST['druckdatum'])){
		$datum_heute = date("d.m.Y");
		}else{
			$datum_heute = $_REQUEST['druckdatum'];
		}
		$p = new partners;
		$p->get_partner_info($_SESSION['partner_id']);
		
		$pdf->ezText("$p->partner_ort, $datum_heute",10, array('justification'=>'right'));
		$pdf->ezText("<b>Objekt: $weg->haus_strasse $weg->haus_nummer, $weg->haus_plz $weg->haus_stadt</b>",10);
		
		$pdf->ezText("<b>Einheit: $weg->einheit_kurzname</b>",10);
		$pdf->ezText("<b>$bpdf->v_kurztext</b>",10);
		
		$pdf->ezSetDy(-30);
		$pdf->ezText("$weg->anrede_brief",10);
		
		#$meine_var{$this->v_text} = $this->v_text;
		eval ("\$bpdf->v_text = \"$bpdf->v_text\";"); ; //Variable ausm Text füllen
	 			
		$pdf->ezText("$bpdf->v_text", 10,  array('justification'=>'full'));
				
		
		/*NEue Seite*/
		if($index < sizeof($empf_id_arr)-1){
		$pdf->ezNewPage();
		}
		
		}
		#die();
		ob_clean(); //ausgabepuffer leeren
		header("Content-type: application/pdf");  // wird von MSIE ignoriert
		$dateiname = "$datum_heute - Serie - $bpdf->v_kurztext.pdf";
		$pdf_opt['Content-Disposition'] = $dateiname;
		$pdf->ezStream($pdf_opt);
		//$pdf->ezStream();
		}
		
		//
		///SERIENBRIEF AN  PARTNER
		//
		
		if($empf_typ=='Partner'){
		
			$pdf = new Cezpdf('a4', 'portrait');
			$bpdf = new b_pdf;
			$bpdf->b_header($pdf, 'Partner', $_SESSION[partner_id], 'portrait', 'pdfclass/fonts/Helvetica.afm', 6);
			$pdf->ezStopPageNumbers(); //seitennummerierung beenden
		
			$anz_eigentuemer = count($empf_id_arr);
			for ($index = 0; $index < $anz_eigentuemer; $index++) {
					
				$e_id = $empf_id_arr[$index];
		
				$pp = new partners();
				$pp->get_partner_info($e_id);
				#print_r($weg);
				#die();
		
		
		
				$dets = new detail();
					
				#$gk = new geldkonto_info();
				#$gk->geld_konto_ermitteln('Objekt', $weg->objekt_id);
				 
				$bpdf->get_texte($v_dat);
		
		
				/*Faltlinie*/
				$pdf->setLineStyle(0.2);
				#$pdf_einzeln->setLineStyle(0.2);
				$pdf->line(5,542,20,542);
				#$pdf_einzeln->line(5,542,20,542);
		
				/*$this->partner_dat = $row['PARTNER_DAT'];
				$this->partner_name = $row['PARTNER_NAME'];
				$this->partner_strasse = $row['STRASSE'];
				$this->partner_hausnr = $row['NUMMER'];
				$this->partner_plz = $row['PLZ'];
				$this->partner_ort = $row['ORT'];
				$this->partner_land = $row['LAND'];*/
				$pdf->ezText("$pp->partner_name\n$pp->partner_strasse $pp->partner_hausnr\n<b>$pp->partner_plz $pp->partner_ort</b>",11);
		
		
		
				###############################################################
				$pdf->ezSetDy(-60);
				#$pdf->ezSetDy(-80);
				#$pdf_einzeln->ezSetDy(-80);
				$datum_heute = date("d.m.Y");
				$p = new partners;
				$p->get_partner_info($_SESSION['partner_id']);
		
				$pdf->ezText("$p->partner_ort, $datum_heute",10, array('justification'=>'right'));
					#	$pdf->ezText("<b>Objekt: $weg->haus_strasse $weg->haus_nummer, $weg->haus_plz $weg->haus_stadt</b>",10);
		
						#$pdf->ezText("<b>Einheit: $weg->einheit_kurzname</b>",10);
						$pdf->ezText("<b>$bpdf->v_kurztext</b>",10);
		
						$pdf->ezSetDy(-30);
						$pdf->ezText("Sehr geehrte Damen und Herren,\n",10);
		
						#$meine_var{$this->v_text} = $this->v_text;
						eval ("\$bpdf->v_text = \"$bpdf->v_text\";"); ; //Variable ausm Text füllen
							
						$pdf->ezText("$bpdf->v_text", 11,  array('justification'=>'full'));
		
		
						/*NEue Seite*/
						if($index < sizeof($empf_id_arr)-1){
						$pdf->ezNewPage();
						}
		
						}
						#die();
						ob_clean(); //ausgabepuffer leeren
						header("Content-type: application/pdf");  // wird von MSIE ignoriert
						$dateiname = "$datum_heute - Serie - $bpdf->v_kurztext.pdf";
						$pdf_opt['Content-Disposition'] = $dateiname;
						$pdf->ezStream($pdf_opt);
						//$pdf->ezStream();
		}
		
		
		
		
	}else{
		die('Keine Empfänger gewählt');
	}	

}
	
	
	
}//ENDE CLASS

?>