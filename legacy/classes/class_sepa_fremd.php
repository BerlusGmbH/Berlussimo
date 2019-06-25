<?php

// Einfache PHP-Klasse zur Erzeugung von SEPA-XML-Dateien mit Gutschriften oder Lastschriften
// Es findet keinerlei Fehlerkontrolle und/oder Plausi-Check statt!!!
// Version 1.1 - unterschiedliche Funktionsparameter zu Version 1.0!!!
// Alles weitere auf http://www.kontopruef.de/ktosepasimple.shtml
class PmtInf {
	public $FCtgyPurp, $FDatum, $FSeqTp;
	private $FBuchungen, $FSumme;
	public function __construct($aCtgyPurp, $aDatum, $aSeqTp) {
		$this->FCtgyPurp = $aCtgyPurp;
		$this->FDatum = $aDatum;
		$this->FSeqTp = $aSeqTp;
		$this->FBuchungen = array ();
		$this->FSumme = 0.00;
	}
	public function Add($aBetrag, $aName, $aIban, $aBic = NULL, $aPurp = NULL, $aRef = NULL, $aVerwend = NULL, $aMandatRef = NULL, $aMandatDate = NULL, $aOldMandatRef = NULL, $aOldName = NULL, $aOldCreditorId = NULL, $aOldIban = NULL, $aOldBic = NULL) {
		$myBuchung = array ();
		$myBuchung ['BETRAG'] = $aBetrag;
		$myBuchung ['NAME'] = $aName;
		$myBuchung ['IBAN'] = $aIban;
		$myBuchung ['BIC'] = $aBic;
		$myBuchung ['PURP'] = $aPurp;
		$myBuchung ['REF'] = $aRef;
		$myBuchung ['VERWEND'] = $aVerwend;
		$myBuchung ['MANDATREF'] = $aMandatRef;
		$myBuchung ['MANDATDATE'] = $aMandatDate;
		$myBuchung ['OLDMANDATREF'] = $aOldMandatRef;
		$myBuchung ['OLDNAME'] = $aOldName;
		$myBuchung ['OLDCREDITORID'] = $aOldCreditorId;
		$myBuchung ['OLDIBAN'] = $aOldIban;
		$myBuchung ['OLDBIC'] = $aOldBic;
		$this->FBuchungen [] = $myBuchung;
		$this->FSumme += $aBetrag;
	}
	public function Get($aPmtInfId, $aType, $aAuftraggeber, $aIban, $aBic, $aCreditorId, $sammelbetrag = 1) {
		$myLast = $aType != 'TRF';
		$result = "    <PmtInf>\n";
		$myPmtInfId = $aPmtInfId;
		if (! empty ( $this->FCtgyPurp ))
			$myPmtInfId .= '-' . $this->FCtgyPurp;
		if (! empty ( $this->FSeqTp ))
			$myPmtInfId .= '-' . $this->FSeqTp;
		$result .= '      <PmtInfId>' . $myPmtInfId . "</PmtInfId>\n";
		$result .= '      <PmtMtd>' . ($myLast ? 'DD' : 'TRF') . "</PmtMtd>\n";
		if ($sammelbetrag == 1) {
			// $result.=' <BtchBookg>TRUE'."</BtchBookg>\n";
		}
		if ($sammelbetrag == 0) {
            $result .= '      <BtchBookg>false' . "</BtchBookg>\n";
		}
		$result .= '      <NbOfTxs>' . count ( $this->FBuchungen ) . "</NbOfTxs>\n";
		$result .= '      <CtrlSum>' . sprintf ( '%.2f', $this->FSumme ) . "</CtrlSum>\n";
		$result .= "      <PmtTpInf>\n";
		$result .= "        <SvcLvl>\n";
		$result .= "          <Cd>SEPA</Cd>\n";
		$result .= "        </SvcLvl>\n";
		if ($myLast) {
			$result .= "        <LclInstrm>\n";
			$result .= '          <Cd>' . $aType . "</Cd>\n";
			$result .= "        </LclInstrm>\n";
			$result .= '        <SeqTp>' . $this->FSeqTp . "</SeqTp>\n";
		}
		if (! empty ( $this->FCtgyPurp )) {
			$result .= "        <CtgyPurp>\n";
			$result .= '          <Cd>' . $this->FCtgyPurp . "</Cd>\n";
			$result .= "        </CtgyPurp>\n";
		}
		$result .= "      </PmtTpInf>\n";
		// Ausfuehrungsdatum
		$tag = $myLast ? 'ReqdColltnDt' : 'ReqdExctnDt';
		$result .= '      <' . $tag . '>' . $this->FDatum . '</' . $tag . ">\n";
		// Eigene Daten
		$tag = $myLast ? 'Cdtr' : 'Dbtr';
		$result .= '      <' . $tag . ">\n";
		$result .= '        <Nm>' . $aAuftraggeber . "</Nm>\n";
		$result .= '      </' . $tag . ">\n";
		$tag2 = $tag . 'Acct';
		$result .= '      <' . $tag2 . ">\n";
		$result .= "        <Id>\n";
		$result .= '          <IBAN>' . $aIban . "</IBAN>\n";
		$result .= "        </Id>\n";
		$result .= '      </' . $tag2 . ">\n";
		$tag2 = $tag . 'Agt';
		$result .= '      <' . $tag2 . ">\n";
		$result .= "        <FinInstnId>\n";
		if (! empty ( $aBic ))
			$result .= '          <BIC>' . $aBic . "</BIC>\n";
		else {
			$result .= "          <Othr>\n";
			$result .= "            <Id>NOTPROVIDED</Id>\n";
			$result .= "          </Othr>\n";
		}
		$result .= "        </FinInstnId>\n";
		$result .= '      </' . $tag2 . ">\n";
		$result .= "      <ChrgBr>SLEV</ChrgBr>\n";
		if ($myLast) {
			$result .= "      <CdtrSchmeId>\n";
			$result .= "        <Id>\n";
			$result .= "          <PrvtId>\n";
			$result .= "            <Othr>\n";
			$result .= '              <Id>' . $aCreditorId . "</Id>\n";
			$result .= "              <SchmeNm>\n";
			$result .= "                <Prtry>SEPA</Prtry>\n";
			$result .= "              </SchmeNm>\n";
			$result .= "            </Othr>\n";
			$result .= "          </PrvtId>\n";
			$result .= "        </Id>\n";
			$result .= "      </CdtrSchmeId>\n";
		}
		// Schleife ueber alle Buchungen
		foreach ( $this->FBuchungen as $myBuchung ) {
			$result .= $myLast ? "      <DrctDbtTxInf>\n" : "        <CdtTrfTxInf>\n";
			$result .= "        <PmtId>\n";
			$result .= '          <EndToEndId>' . (empty ( $myBuchung ['REF'] ) ? 'NOTPROVIDED' : $myBuchung ['REF']) . "</EndToEndId>\n";
			$result .= "        </PmtId>\n";
			if ($myLast) {
				$result .= '        <InstdAmt Ccy="EUR">' . sprintf ( '%.2f', $myBuchung ['BETRAG'] ) . "</InstdAmt>\n";
				$result .= "        <DrctDbtTx>\n";
				$result .= "          <MndtRltdInf>\n";
				$result .= '            <MndtId>' . $myBuchung ['MANDATREF'] . "</MndtId>\n";
				$result .= '            <DtOfSgntr>' . $myBuchung ['MANDATDATE'] . "</DtOfSgntr>\n";
				$amendmentinfo = ! empty ( $myBuchung ['OLDMANDATREF'] ) || ! empty ( $myBuchung ['OLDNAME'] ) || ! empty ( $myBuchung ['OLDCREDITORID'] ) || ! empty ( $myBuchung ['OLDIBAN'] ) || ! empty ( $myBuchung ['OLDBIC'] );
				$result .= '            <AmdmntInd>' . ($amendmentinfo ? 'true' : 'false') . "</AmdmntInd>\n";
				if ($amendmentinfo) {
					$result .= "            <AmdmntInfDtls>\n";
					if (! empty ( $myBuchung ['OLDMANDATREF'] ))
						$result .= '              <OrgnlMndtId>' . $myBuchung ['OLDMANDATREF'] . "</OrgnlMndtId>\n";
					if (! empty ( $myBuchung ['OLDNAME'] ) or ! empty ( $myBuchung ['OLDCREDITORID'] )) {
						$result .= "              <OrgnlCdtrSchmeId>\n";
						if (! empty ( $myBuchung ['OLDNAME'] ))
							$result .= '                <Nm>' . $myBuchung ['OLDNAME'] . "</Nm>\n";
						if (! empty ( $myBuchung ['OLDCREDITORID'] )) {
							$result .= "                <Id>\n";
							$result .= "                  <PrvtId>\n";
							$result .= "                    <Othr>\n";
							$result .= '                      <Id>' . $myBuchung ['OLDCREDITORID'] . "</Id>\n";
							$result .= "                      <SchmeNm>\n";
							$result .= "                        <Prtry>SEPA</Prtry>\n";
							$result .= "                      </SchmeNm>\n";
							$result .= "                    </Othr>\n";
							$result .= "                  </PrvtId>\n";
							$result .= "                </Id>\n";
						}
						$result .= "              </OrgnlCdtrSchmeId>\n";
					}
					if (! empty ( $myBuchung ['OLDIBAN'] )) {
						$result .= "              <OrgnlDbtrAcct>\n";
						$result .= "                <Id>\n";
						$result .= '                  <IBAN>' . $myBuchung ['OLDIBAN'] . "</IBAN>\n";
						$result .= "                </Id>\n";
						$result .= "              </OrgnlDbtrAcct>\n";
					}
					if (! empty ( $myBuchung ['OLDBIC'] )) {
						$result .= "              <OrgnlDbtrAgt>\n";
						$result .= "                <FinInstnId>\n";
						$result .= "                  <Othr>\n";
						$result .= '                    <Id>' . $myBuchung ['OLDBIC'] . "</Id>\n";
						$result .= "                  </Othr>\n";
						$result .= "                </FinInstnId>\n";
						$result .= "              </OrgnlDbtrAgt>\n";
					}
					$result .= "            </AmdmntInfDtls>\n";
				}
				$result .= "          </MndtRltdInf>\n";
				$result .= "        </DrctDbtTx>\n";
			} else {
				$result .= "        <Amt>\n";
				$result .= '          <InstdAmt Ccy="EUR">' . sprintf ( '%.2f', $myBuchung ['BETRAG'] ) . "</InstdAmt>\n";
				$result .= "        </Amt>\n";
			}
			$tag = $myLast ? 'Dbtr' : 'Cdtr';
			$tag2 = $tag . 'Agt';
			if (! empty ( $myBuchung ['BIC'] )) {
				$result .= '        <' . $tag2 . ">\n";
				$result .= "          <FinInstnId>\n";
				$result .= '            <BIC>' . $myBuchung ['BIC'] . "</BIC>\n";
				$result .= "          </FinInstnId>\n";
				$result .= '        </' . $tag2 . ">\n";
			} else {
				if ($myLast) {
					$result .= '        <' . $tag2 . ">\n";
					$result .= "          <FinInstnId>\n";
					$result .= "            <Othr>\n";
					$result .= "              <Id>NOTPROVIDED</Id>\n";
					$result .= "            </Othr>\n";
					$result .= "          </FinInstnId>\n";
					$result .= '        </' . $tag2 . ">\n";
				}
			}
			$result .= '        <' . $tag . ">\n";
			$result .= '          <Nm>' . $myBuchung ['NAME'] . "</Nm>\n";
			$result .= '        </' . $tag . ">\n";
			$tag2 = $tag . 'Acct';
			$result .= '        <' . $tag2 . ">\n";
			$result .= "          <Id>\n";
			$result .= '            <IBAN>' . $myBuchung ['IBAN'] . "</IBAN>\n";
			$result .= "          </Id>\n";
			$result .= '        </' . $tag2 . ">\n";
			if (! empty ( $myBuchung ['PURP'] )) {
				$result .= "        <Purp>\n";
				$result .= '          <Cd>' . $myBuchung ['PURP'] . "</Cd>\n";
				$result .= "        </Purp>\n";
			}
			if (! empty ( $myBuchung ['VERWEND'] )) {
				$result .= "        <RmtInf>\n";
				$result .= '          <Ustrd>' . $myBuchung ['VERWEND'] . "</Ustrd>\n";
				$result .= "        </RmtInf>\n";
			}
			// $result.=' <SeqTp>'.$this->FSeqTp."</SeqTp>\n";
			$result .= $myLast ? "      </DrctDbtTxInf>\n" : "        </CdtTrfTxInf>\n";
		}
		
		// Ende der Schleife, Schlussausgaben
		$result .= "    </PmtInf>\n";
		return $result;
	}
}
class KtoSepaSimple {
	private $FVersion, $FPmtInf, $FAnzahl, $FSumme;
	public function __construct() {
		$this->FVersion = '3';
		$this->FPmtInf = array ();
		$this->FAnzahl = 0;
		$this->FSumme = 0.00;
	}
	private function GetPmtInf($aDatum, $aCtgyPurp, $aSeqTp) {
		foreach ( $this->FPmtInf as $myPmtInf ) {
			if ($myPmtInf->FDatum == $aDatum and $myPmtInf->FCtgyPurp == $aCtgyPurp and $myPmtInf->FSeqTp == $aSeqTp)
				return $myPmtInf;
		}
		$myPmtInf = new PmtInf ( $aCtgyPurp, $aDatum, $aSeqTp );
		$this->FPmtInf [] = $myPmtInf;
		return $myPmtInf;
	}
	public function Add($aDatum, $aBetrag, $aName, $aIban, $aBic = NULL, $aCtgyPurp = NULL, $aPurp = NULL, $aRef = NULL, $aVerwend = NULL, $aSeqTp = NULL, $aMandatRef = NULL, $aMandatDate = NULL, $aOldMandatRef = NULL, $aOldName = NULL, $aOldCreditorId = NULL, $aOldIban = NULL, $aOldBic = NULL) {
		$myPmtInf = $this->GetPmtInf ( $aDatum, $aCtgyPurp, $aSeqTp );
		$myPmtInf->Add ( $aBetrag, $aName, $aIban, $aBic, $aPurp, $aRef, $aVerwend, $aMandatRef, $aMandatDate, $aOldMandatRef, $aOldName, $aOldCreditorId, $aOldIban, $aOldBic );
		$this->FAnzahl ++;
		$this->FSumme += $aBetrag;
		if ($aDatum < '2013-11-01')
			$this->FVersion = '2';
	}
	public function GetXML($aType, $aMsgId, $aPmtInfId, $aInitgPty, $aAuftraggeber, $aIban, $aBic, $aCreditorId = NULL, $sammelbetrag = 1) {
		// Diverse Vorbelegungen
		$myLast = $aType != 'TRF';
		$pain = $myLast ? 'pain.008.00' . $this->FVersion . '.02' : 'pain.001.00' . $this->FVersion . '.03';
		$urn = 'urn:iso:std:iso:20022:tech:xsd:' . $pain;
		// Header schreiben
		$result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$result .= '<Document xmlns="' . $urn . "\"\n";
		$result .= "  xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n";
		$result .= '  xsi:schemaLocation="' . $urn . ' ' . $pain . ".xsd\">\n";
		$result .= $myLast ? "  <CstmrDrctDbtInitn>\n" : "  <CstmrCdtTrfInitn>\n";
		// Group Header
		$result .= "    <GrpHdr>\n";
		$result .= '      <MsgId>' . $aMsgId . "</MsgId>\n";
		$result .= '      <CreDtTm>' . date ( 'Y-m-d\TH:i:s' ) . "</CreDtTm>\n";
		$result .= '      <NbOfTxs>' . $this->FAnzahl . "</NbOfTxs>\n";
		$result .= '      <CtrlSum>' . sprintf ( '%.2f', $this->FSumme ) . "</CtrlSum>\n";
		$result .= "      <InitgPty>\n";
		$result .= '        <Nm>' . $aInitgPty . "</Nm>\n";
		$result .= "      </InitgPty>\n";
		$result .= "    </GrpHdr>\n";
		// Payment Information(s)
		foreach ( $this->FPmtInf as $myPmtInf ) {
			$result .= $myPmtInf->Get ( $aPmtInfId, $aType, $aAuftraggeber, $aIban, $aBic, $aCreditorId, $sammelbetrag );
		}
		// Ende
		$result .= $myLast ? "  </CstmrDrctDbtInitn>\n" : "  </CstmrCdtTrfInitn>\n";
		$result .= "</Document>\n";
		return $result;
	}
}
