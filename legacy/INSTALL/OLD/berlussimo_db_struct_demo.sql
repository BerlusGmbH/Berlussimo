-- phpMyAdmin SQL Dump
-- version 2.8.2.4
-- http://www.phpmyadmin.net
-- 
-- Host: localhost:3306
-- Erstellungszeit: 02. November 2010 um 09:26
-- Server Version: 4.1.13
-- PHP-Version: 5.2.6
-- 
-- Datenbank: `sanels_berlussimo_1v`
-- 

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `BAUSTELLEN`
-- 

CREATE TABLE `BAUSTELLEN` (
  `DAT` int(6) NOT NULL auto_increment,
  `KOSTENTRAEGER_TYP` varchar(30) collate latin1_german2_ci NOT NULL default '',
  `KOSTENTRAEGER_ID` int(6) NOT NULL default '0',
  `A_DATUM` date NOT NULL default '0000-00-00',
  `E_DATUM` date NOT NULL default '0000-00-00',
  `BESCHREIBUNG` varchar(50) collate latin1_german2_ci NOT NULL default '',
  PRIMARY KEY  (`DAT`,`KOSTENTRAEGER_TYP`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `BAUSTELLEN`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `BENUTZER`
-- 

CREATE TABLE `BENUTZER` (
  `benutzer_id` int(7) NOT NULL auto_increment,
  `benutzername` varchar(20) collate latin1_german2_ci NOT NULL default '',
  `passwort` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `STUNDENSATZ` decimal(10,2) NOT NULL default '0.00',
  `GEB_DAT` date default NULL,
  `GEWERK_ID` int(7) NOT NULL default '0',
  `EINTRITT` date NOT NULL default '0000-00-00',
  `AUSTRITT` date NOT NULL default '0000-00-00',
  `URLAUB` int(2) default NULL,
  `STUNDEN_PW` decimal(4,2) NOT NULL default '0.00',
  PRIMARY KEY  (`benutzer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `BENUTZER`
-- 

INSERT INTO `BENUTZER` (`benutzer_id`, `benutzername`, `passwort`, `STUNDENSATZ`, `GEB_DAT`, `GEWERK_ID`, `EINTRITT`, `AUSTRITT`, `URLAUB`, `STUNDEN_PW`) VALUES (1, 'demo', 'demo', 10.00, '1978-12-12', 0, '2010-01-01', '0000-00-00', 25, 40.00);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `BENUTZER_MODULE`
-- 

CREATE TABLE `BENUTZER_MODULE` (
  `BM_DAT` int(7) NOT NULL auto_increment,
  `BM_ID` int(7) NOT NULL default '0',
  `BENUTZER_ID` int(7) NOT NULL default '0',
  `MODUL_NAME` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`BM_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `BENUTZER_MODULE`
-- 

INSERT INTO `BENUTZER_MODULE` (`BM_DAT`, `BM_ID`, `BENUTZER_ID`, `MODUL_NAME`, `AKTUELL`) VALUES (1, 1, 1, '*', '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `BENUTZER_PARTNER`
-- 

CREATE TABLE `BENUTZER_PARTNER` (
  `BP_DAT` int(7) NOT NULL auto_increment,
  `BP_BENUTZER_ID` int(7) NOT NULL default '0',
  `BP_PARTNER_ID` int(7) NOT NULL default '0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`BP_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `BENUTZER_PARTNER`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `BK_ABRECHNUNGEN`
-- 

CREATE TABLE `BK_ABRECHNUNGEN` (
  `B_DAT` int(6) NOT NULL auto_increment,
  `B_ID` int(6) NOT NULL default '0',
  `PROFIL_ID` int(6) NOT NULL default '0',
  `PROFIL_BEZ` varchar(100) collate latin1_german2_ci NOT NULL default '',
  `PROFIL_JAHR` int(4) NOT NULL default '0',
  `WIRT_E` int(6) NOT NULL default '0',
  `WIRT_NAME` varchar(100) collate latin1_german2_ci NOT NULL default '',
  `DATUM` date NOT NULL default '0000-00-00',
  `ANZ_EINHEITEN` int(6) NOT NULL default '0',
  `QM_GESAMT` decimal(10,2) NOT NULL default '0.00',
  `QM_WOHNRAUM` decimal(10,2) NOT NULL default '0.00',
  `QM_GEWERBE` decimal(10,2) NOT NULL default '0.00',
  `ANZ_KONTEN` int(5) NOT NULL default '0',
  `ANZ_ABRECHNUNGEN` varchar(6) collate latin1_german2_ci NOT NULL default '',
  `ERSTELLT` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `ERSTELLER` varchar(30) collate latin1_german2_ci NOT NULL default '',
  `PARTNER_ID` int(6) NOT NULL default '0',
  `KONTENRAHMEN_ID` int(6) NOT NULL default '0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`B_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `BK_ABRECHNUNGEN`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `BK_ABRECHNUNGEN_KONTEN`
-- 

CREATE TABLE `BK_ABRECHNUNGEN_KONTEN` (
  `BK_A_DAT` int(7) NOT NULL auto_increment,
  `BK_A_ID` int(7) NOT NULL default '0',
  `B_ID` int(7) NOT NULL default '0',
  `BK_K_ID` int(7) NOT NULL default '0',
  `KONTO` int(7) NOT NULL default '0',
  `KONTO_BEZ` varchar(100) collate latin1_german2_ci NOT NULL default '',
  `G_KOSTEN` decimal(10,2) NOT NULL default '0.00',
  `G_KOSTEN_WO` decimal(10,2) NOT NULL default '0.00',
  `G_KOSTEN_GE` decimal(10,2) NOT NULL default '0.00',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`BK_A_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `BK_ABRECHNUNGEN_KONTEN`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `BK_ANPASSUNG`
-- 

CREATE TABLE `BK_ANPASSUNG` (
  `AN_DAT` int(6) NOT NULL auto_increment,
  `AN_ID` int(6) NOT NULL default '0',
  `GRUND` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `FEST_BETRAG` decimal(10,2) NOT NULL default '0.00',
  `KEY_ID` int(6) NOT NULL default '0',
  `PROFIL_ID` int(6) NOT NULL default '0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`AN_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `BK_ANPASSUNG`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `BK_BERECHNUNG_BUCHUNGEN`
-- 

CREATE TABLE `BK_BERECHNUNG_BUCHUNGEN` (
  `BK_BE_DAT` int(6) NOT NULL auto_increment,
  `BK_BE_ID` int(6) NOT NULL default '0',
  `BUCHUNG_ID` int(6) NOT NULL default '0',
  `BK_K_ID` int(6) NOT NULL default '0',
  `BK_PROFIL_ID` int(6) NOT NULL default '0',
  `KEY_ID` int(6) NOT NULL default '0',
  `ANTEIL` decimal(6,3) NOT NULL default '0.000',
  `KOSTENTRAEGER_TYP` varchar(30) collate latin1_german2_ci NOT NULL default '',
  `KOSTENTRAEGER_ID` int(7) NOT NULL default '0',
  `HNDL_BETRAG` decimal(10,3) NOT NULL default '0.000',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`BK_BE_DAT`),
  KEY `BUCHUNG_ID` (`BUCHUNG_ID`,`BK_K_ID`,`BK_PROFIL_ID`,`KOSTENTRAEGER_TYP`,`KOSTENTRAEGER_ID`,`AKTUELL`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=3 ;

-- 
-- Daten für Tabelle `BK_BERECHNUNG_BUCHUNGEN`
-- 

INSERT INTO `BK_BERECHNUNG_BUCHUNGEN` (`BK_BE_DAT`, `BK_BE_ID`, `BUCHUNG_ID`, `BK_K_ID`, `BK_PROFIL_ID`, `KEY_ID`, `ANTEIL`, `KOSTENTRAEGER_TYP`, `KOSTENTRAEGER_ID`, `HNDL_BETRAG`, `AKTUELL`) VALUES (1, 1, 2, 1, 1, 1, 100.000, 'Wirtschaftseinheit', 1, 0.000, '1'),
(2, 2, 3, 2, 1, 1, 100.000, 'Wirtschaftseinheit', 1, 0.000, '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `BK_EINZEL_ABRECHNUNGEN`
-- 

CREATE TABLE `BK_EINZEL_ABRECHNUNGEN` (
  `BK_E_DAT` int(7) NOT NULL auto_increment,
  `BK_E_ID` int(7) NOT NULL default '0',
  `B_ID` int(7) NOT NULL default '0',
  `EMPF` varchar(255) collate latin1_german2_ci NOT NULL default '',
  `ZEITRAUM` varchar(255) collate latin1_german2_ci NOT NULL default '',
  `MIETERNUMMER` varchar(100) collate latin1_german2_ci NOT NULL default '',
  `EINHEIT_ID` int(7) NOT NULL default '0',
  `EINHEIT_NAME` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `G_KOSTEN` decimal(10,2) NOT NULL default '0.00',
  `G_HNDL` decimal(10,2) NOT NULL default '0.00',
  `VORSCHUSS` decimal(10,2) NOT NULL default '0.00',
  `SALDO` decimal(10,2) NOT NULL default '0.00',
  `SALDO_ART` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`BK_E_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `BK_EINZEL_ABRECHNUNGEN`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `BK_EINZEL_ABR_ZEILEN`
-- 

CREATE TABLE `BK_EINZEL_ABR_ZEILEN` (
  `BK_Z_DAT` int(7) NOT NULL auto_increment,
  `BK_Z_ID` int(7) NOT NULL default '0',
  `BK_E_ID` int(7) NOT NULL default '0',
  `KONTO_ID` int(7) NOT NULL default '0',
  `KONTO_BEZ` varchar(100) collate latin1_german2_ci NOT NULL default '',
  `G_KOSTEN` decimal(10,2) NOT NULL default '0.00',
  `G_HNDL` decimal(10,2) NOT NULL default '0.00',
  `VERTEILER` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `IHRE_ME` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `ANTEIL_HNDL` decimal(10,2) NOT NULL default '0.00',
  `BETEILIGUNG` decimal(10,2) NOT NULL default '0.00',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`BK_Z_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `BK_EINZEL_ABR_ZEILEN`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `BK_GENERAL_KEYS`
-- 

CREATE TABLE `BK_GENERAL_KEYS` (
  `GKEY_DAT` int(7) NOT NULL auto_increment,
  `GKEY_ID` int(7) NOT NULL default '0',
  `GKEY_NAME` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `G_VAR` varchar(30) collate latin1_german2_ci NOT NULL default '',
  `E_VAR` varchar(20) collate latin1_german2_ci NOT NULL default '',
  `ME` varchar(10) collate latin1_german2_ci NOT NULL default '',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`GKEY_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=3 ;

-- 
-- Daten für Tabelle `BK_GENERAL_KEYS`
-- 

INSERT INTO `BK_GENERAL_KEYS` (`GKEY_DAT`, `GKEY_ID`, `GKEY_NAME`, `G_VAR`, `E_VAR`, `ME`, `AKTUELL`) VALUES (1, 1, 'm² je Einheit', 'g_einheit_qm', 'einheit_qm', 'm²', '1'),
(2, 2, 'durch Anzahl Einheiten', 'g_anzahl_einheiten', 'anzahl_einheiten', 'ME', '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `BK_KONTEN`
-- 

CREATE TABLE `BK_KONTEN` (
  `BK_K_DAT` int(6) NOT NULL auto_increment,
  `BK_K_ID` int(6) NOT NULL default '0',
  `KONTO` varchar(10) collate latin1_german2_ci NOT NULL default '',
  `KONTO_BEZ` varchar(100) collate latin1_german2_ci NOT NULL default '',
  `BK_PROFIL_ID` int(6) NOT NULL default '0',
  `GENKEY_ID` int(6) NOT NULL default '0',
  `HNDL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`BK_K_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=3 ;

-- 
-- Daten für Tabelle `BK_KONTEN`
-- 

INSERT INTO `BK_KONTEN` (`BK_K_DAT`, `BK_K_ID`, `KONTO`, `KONTO_BEZ`, `BK_PROFIL_ID`, `GENKEY_ID`, `HNDL`, `AKTUELL`) VALUES (1, 1, '2000', 'Grundsteuer', 1, 1, '0', '1'),
(2, 2, '3000', 'Versicherungen', 1, 1, '0', '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `BK_PROFILE`
-- 

CREATE TABLE `BK_PROFILE` (
  `BK_DAT` int(6) NOT NULL auto_increment,
  `BK_ID` int(6) NOT NULL default '0',
  `BEZEICHNUNG` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `TYP` varchar(20) collate latin1_german2_ci NOT NULL default '',
  `TYP_ID` int(6) NOT NULL default '0',
  `JAHR` decimal(4,0) NOT NULL default '0',
  `BERECHNUNGS_DATUM` date NOT NULL default '0000-00-00',
  `VERRECHNUNGS_DATUM` date NOT NULL default '0000-00-00',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`BK_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `BK_PROFILE`
-- 

INSERT INTO `BK_PROFILE` (`BK_DAT`, `BK_ID`, `BEZEICHNUNG`, `TYP`, `TYP_ID`, `JAHR`, `BERECHNUNGS_DATUM`, `VERRECHNUNGS_DATUM`, `AKTUELL`) VALUES (1, 1, 'Muster Betriebskostenabrechnung 2010', 'Wirtschaftseinheit', 1, 2010, '2010-12-01', '2011-02-01', '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `DETAIL`
-- 

CREATE TABLE `DETAIL` (
  `DETAIL_DAT` int(11) NOT NULL auto_increment,
  `DETAIL_ID` int(11) NOT NULL default '0',
  `DETAIL_NAME` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `DETAIL_INHALT` text collate latin1_german2_ci NOT NULL,
  `DETAIL_BEMERKUNG` text collate latin1_german2_ci NOT NULL,
  `DETAIL_AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  `DETAIL_ZUORDNUNG_TABELLE` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `DETAIL_ZUORDNUNG_ID` varchar(6) collate latin1_german2_ci NOT NULL default '',
  PRIMARY KEY  (`DETAIL_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=17 ;

-- 
-- Daten für Tabelle `DETAIL`
-- 

INSERT INTO `DETAIL` (`DETAIL_DAT`, `DETAIL_ID`, `DETAIL_NAME`, `DETAIL_INHALT`, `DETAIL_BEMERKUNG`, `DETAIL_AKTUELL`, `DETAIL_ZUORDNUNG_TABELLE`, `DETAIL_ZUORDNUNG_ID`)
VALUES (1, 0, 'Geschlecht', 'männlich', '', '1', 'Person', '1'),
  (2, 1, 'Telefon', '030 89784477', 'Stand 02.11.2010', '1', 'Person', '1'),
  (3, 2, 'Handy', '030 89784479', 'Stand 02.11.2010', '1', 'Person', '1'),
  (4, 3, 'Geschlecht', 'weiblich', '', '1', 'Person', '2'),
  (5, 4, 'Telefon', '11111111', 'Stand 02.11.2010', '1', 'Person', '2'),
  (6, 5, 'Handy', '22222222', 'Stand 02.11.2010', '1', 'Person', '2'),
  (7, 6, 'Einzugsermächtigung', 'JA', '', '1', 'Mietvertrag', '2'),
  (8, 7, 'Autoeinzugsart', 'Aktuelles Saldo komplett', '', '1', 'Mietvertrag', '2'),
  (9, 8, 'Kontoinhaber-AutoEinzug', 'Melanie Mustermann', '', '1', 'Mietvertrag', '2'),
  (10, 9, 'Kontonummer-AutoEinzug', '1234567', '', '1', 'Mietvertrag', '2'),
  (11, 10, 'BLZ-AutoEinzug', '1234321', '', '1', 'Mietvertrag', '2'),
  (12, 11, 'Bankname-AutoEinzug', 'Melanies Bank', '', '1', 'Mietvertrag', '2'),
  (13, 12, 'Baujahr', '1919', '', '1', 'Objekt', '1'),
  (14, 13, 'Wohnlage', 'gut', '', '1', 'Haus', '1'),
  (15, 14, 'Ausstattungsklasse', '1', 'Feld aus dem Mietspiegel', '1', 'Einheit', '1'),
  (16, 15, 'Ausstattungsklasse', '1', 'Feld aus dem Mietspiegel', '1', 'Einheit', '2');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `DETAIL_KATEGORIEN`
-- 

CREATE TABLE `DETAIL_KATEGORIEN` (
  `DETAIL_KAT_ID` int(6) NOT NULL auto_increment,
  `DETAIL_KAT_NAME` varchar(100) collate latin1_german2_ci NOT NULL default '',
  `DETAIL_KAT_KATEGORIE` varchar(30) collate latin1_german2_ci NOT NULL default '',
  `DETAIL_KAT_AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`DETAIL_KAT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=73 ;

-- 
-- Daten für Tabelle `DETAIL_KATEGORIEN`
-- 

INSERT INTO `DETAIL_KATEGORIEN` (`DETAIL_KAT_ID`, `DETAIL_KAT_NAME`, `DETAIL_KAT_KATEGORIE`, `DETAIL_KAT_AKTUELL`)
VALUES (1, 'Heizungsart', 'Einheit', '1'),
  (2, 'Warmwasser', 'Einheit', '1'),
  (3, 'Zimmeranzahl', 'Einheit', '1'),
  (4, 'Eigentümer', 'Objekt', '1'),
  (5, 'Baujahr', 'Objekt', '1'),
  (6, 'Garage', 'Objekt', '1'),
  (7, 'Fussboden', 'Einheit', '1'),
  (8, 'Etagenanzahl', 'Haus', '1'),
  (9, 'Kabelfernsehen', 'Haus', '1'),
  (10, 'Aufzug', 'Haus', '1'),
  (11, 'Fenstertyp', 'Einheit', '1'),
  (12, 'Tiere', 'Mietvertrag', '1'),
  (13, 'Vereinbarung', 'Mietvertrag', '1'),
  (14, 'Telefon', 'Person', '1'),
  (15, 'Fax', 'Person', '1'),
  (16, 'Handy', 'Person', '1'),
  (17, 'Strasse / Nr', 'Person', '1'),
  (18, 'PLZ', 'Person', '1'),
  (19, 'Geschlecht', 'Person', '1'),
  (20, 'Bauherr', 'Objekt', '1'),
  (25, 'Keller', 'Mietvertrag', '1'),
  (26, 'BK', 'Mietvertrag', '1'),
  (27, 'HK', 'Mietvertrag', '1'),
  (28, 'KALTMIETE', 'Mietvertrag', '1'),
  (29, 'Fensteranzahl', 'Einheit', '1'),
  (30, 'Balkon', 'Einheit', '1'),
  (31, 'Parkplatz', 'Haus', '1'),
  (32, 'Parkplatzanzahl', 'Haus', '1'),
  (34, 'Bauherrin', 'Objekt', '1'),
  (35, 'Sanierungsjahr', 'Objekt', '1'),
  (36, 'Zaun', 'Objekt', '1'),
  (37, 'MGF', 'Mietvertrag', '1'),
  (38, 'ME', 'Mietvertrag', '1'),
  (39, 'Geld Konto Nummer', 'Objekt', '1'),
  (40, 'Geld Konto Bankleitzahl', 'Objekt', '1'),
  (41, 'Geld Konto Institut', 'Objekt', '1'),
  (42, 'Geld Konto Bankleitzahl	', 'Haus', '1'),
  (43, 'gekündigt am', 'Mietvertrag', '1'),
  (44, 'gekündigt zum', 'Mietvertrag', '1'),
  (45, 'Kontonummer', 'Mietvertrag', '1'),
  (46, 'BLZ', 'Mietvertrag', '1'),
  (52, 'Bemerkung', 'Einheit', '1'),
  (54, 'Kaution', 'Mietvertrag', '1'),
  (55, 'Miete kalt', 'Einheit', '1'),
  (56, 'Heizkosten Vorauszahlung', 'Einheit', '1'),
  (57, 'Nebenkosten Vorauszahlung', 'Einheit', '1'),
  (58, 'Hinweis', 'Person', '1'),
  (59, 'Verzugsanschrift', 'Person', '1'),
  (60, 'Aushangtafel', 'Haus', '1'),
  (61, 'Mandanten-Nr', 'Partner', '1'),
  (62, 'Anrede', 'Person', '1'),
  (63, 'Kontoinhaber-AutoEinzug', 'Mietvertrag', '0'),
  (64, 'Bankname', 'Mietvertrag', '1'),
(65, 'Lieferschein', 'RECHNUNGEN', '1'),
  (66, 'Geschlecht', 'Partner', '1'),
  (67, 'Wohnlage', 'Haus', '1'),
  (68, 'Ausstattungsklasse', 'Einheit', '1'),
  (69, 'Erdgeschosswohnung', 'Einheit', '1'),
  (70, 'Ohne SH, ohne Bad, mit IWC', 'Einheit', '1'),
  (71, 'Sondermerkmal', 'Einheit', '1'),
  (72, 'NB mit SH oder Bad und mit IWC', 'Einheit', '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `DETAIL_UNTERKATEGORIEN`
-- 

CREATE TABLE `DETAIL_UNTERKATEGORIEN` (
  `UKAT_DAT` int(6) NOT NULL auto_increment,
  `KATEGORIE_ID` int(6) NOT NULL default '0',
  `UNTERKATEGORIE_NAME` varchar(30) collate latin1_german2_ci NOT NULL default '',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`UKAT_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=51 ;

-- 
-- Daten für Tabelle `DETAIL_UNTERKATEGORIEN`
-- 

INSERT INTO `DETAIL_UNTERKATEGORIEN` (`UKAT_DAT`, `KATEGORIE_ID`, `UNTERKATEGORIE_NAME`, `AKTUELL`) VALUES (1, 1, 'GEH', '1'),
(2, 1, 'Zentral', '1'),
(3, 1, 'Nachtspeicher', '1'),
(4, 1, 'Ofenheizung', '1'),
(5, 7, 'Laminat', '1'),
(6, 7, 'Spanplatte', '1'),
(7, 7, 'Holzdielen', '1'),
(8, 7, 'Parkett', '1'),
(9, 7, 'Beton', '1'),
(10, 11, 'Holzfenster', '1'),
(11, 11, 'Kunststofffenster', '1'),
(12, 19, 'männlich', '1'),
(13, 10, 'vorhanden', '1'),
(14, 10, 'nicht vorhanden', '1'),
(15, 25, 'vorhanden', '1'),
(16, 25, 'nicht vorhanden', '1'),
(17, 30, 'vorhanden', '1'),
(23, 36, 'Jägerzaun', '1'),
(19, 31, 'nicht vorhanden', '1'),
(20, 31, 'vorhanden', '1'),
(22, 19, 'weiblich', '1'),
(24, 36, 'Drahtzaun', '1'),
(25, 36, 'Maschendrahtzaun', '1'),
(26, 47, 'JA', '1'),
(27, 47, 'NEIN', '1'),
(28, 51, 'Nur die  Summe aus Vertrag', '1'),
(29, 51, 'Ratenzahlung', '1'),
(30, 51, 'Aktuelles Saldo komplett', '1'),
(31, 60, 'IKEA 3 x A4', '1'),
(32, 60, 'alt', '1'),
(33, 66, 'Firma', '1'),
(34, 66, 'männlich', '1'),
(35, 66, 'weiblich', '1'),
(36, 67, 'einfach', '1'),
(37, 67, 'mittel', '1'),
(38, 67, 'gut', '1'),
(39, 68, '1', '1'),
(40, 68, '2', '1'),
(41, 68, '3', '1'),
(42, 68, '4', '1'),
(43, 68, '5', '1'),
(44, 68, '6', '1'),
(45, 68, '7', '1'),
(46, 68, '8', '1'),
(47, 68, '9', '1'),
(48, 68, '10', '1'),
(49, 68, '11', '1'),
(50, 69, 'JA', '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `EINHEIT`
-- 

CREATE TABLE `EINHEIT` (
  `EINHEIT_DAT` int(11) NOT NULL auto_increment,
  `EINHEIT_ID` int(11) NOT NULL default '0',
  `EINHEIT_QM` decimal(6,2) NOT NULL default '0.00',
  `EINHEIT_LAGE` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `HAUS_ID` int(11) NOT NULL default '0',
  `EINHEIT_AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  `EINHEIT_KURZNAME` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `TYP` enum('Wohnraum','Gewerbe','Stellplatz','Garage','Keller','Freiflaeche') collate latin1_german2_ci NOT NULL default 'Wohnraum',
  UNIQUE KEY `EINHEIT_DAT` (`EINHEIT_DAT`),
  KEY `EINHEIT_ID` (`EINHEIT_ID`),
  KEY `HAUS_ID` (`HAUS_ID`),
  KEY `EINHEIT_KURZNAME` (`EINHEIT_KURZNAME`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=5 ;

-- 
-- Daten für Tabelle `EINHEIT`
-- 

INSERT INTO `EINHEIT` (`EINHEIT_DAT`, `EINHEIT_ID`, `EINHEIT_QM`, `EINHEIT_LAGE`, `HAUS_ID`, `EINHEIT_AKTUELL`, `EINHEIT_KURZNAME`, `TYP`) VALUES (1, 1, 250.00, 'VPL', 1, '1', 'FON-001', 'Wohnraum'),
(2, 2, 250.00, 'V1L', 1, '1', 'FON-002', 'Gewerbe'),
(3, 3, 0.00, 'Stellplatz', 1, '1', 'P1', 'Stellplatz'),
(4, 4, 50.00, 'V1R', 1, '1', 'FON-003', 'Wohnraum');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `FEIERTAGE`
-- 

CREATE TABLE `FEIERTAGE` (
  `F_DAT` int(6) NOT NULL auto_increment,
  `DATUM` date NOT NULL default '0000-00-00',
  `FEIERTAG` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`F_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `FEIERTAGE`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `FOOTER_ZEILE`
-- 

CREATE TABLE `FOOTER_ZEILE` (
  `FOOTER_DAT` int(6) NOT NULL auto_increment,
  `FOOTER_ID` int(6) NOT NULL default '0',
  `FOOTER_TYP` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `FOOTER_TYP_ID` int(6) NOT NULL default '0',
  `ZAHLUNGSHINWEIS` mediumtext collate latin1_german2_ci,
  `ZEILE1` varchar(200) collate latin1_german2_ci NOT NULL default '',
  `ZEILE2` varchar(200) collate latin1_german2_ci NOT NULL default '',
  `HEADER` varchar(200) collate latin1_german2_ci default NULL,
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`FOOTER_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `FOOTER_ZEILE`
-- 

INSERT INTO `FOOTER_ZEILE` (`FOOTER_DAT`, `FOOTER_ID`, `FOOTER_TYP`, `FOOTER_TYP_ID`, `ZAHLUNGSHINWEIS`, `ZEILE1`, `ZEILE2`, `HEADER`, `AKTUELL`)
VALUES (1, 1, 'Partner', 1, 'Mit freundlichen Grüßen\r\n<br>\r\n<br>\r\nIhre Hausverwaltung',
        'Muster Hausverwaltung Berlus GmbH - Fontanestr. 1 - 14193 Berlin - Geschäftsführer: xxx xxxx  -',
        'Bankverbindung: Berlussimo Bank Berlin - BLZ: 100 xx 00 - Konto-Nr.: xxxx - Steuernummer: xxxx',
        'Musterhausverwaltung Berlus GmbH * Fontanestr. 1 * 14193 Berlin * Inhaber xxx xxxxxxxxx * Telefon: 89784477 * Fax: 89784479 * Email: info@berlus.de',
        '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `GELD_KONTEN`
-- 

CREATE TABLE `GELD_KONTEN` (
  `KONTO_DAT` int(4) NOT NULL auto_increment,
  `KONTO_ID` int(4) NOT NULL default '0',
  `BEZEICHNUNG` varchar(50) collate latin1_german2_ci default NULL,
  `BEGUENSTIGTER` varchar(100) collate latin1_german2_ci NOT NULL default '',
  `KONTONUMMER` varchar(15) collate latin1_german2_ci NOT NULL default '',
  `BLZ` varchar(15) collate latin1_german2_ci NOT NULL default '',
  `INSTITUT` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`KONTO_DAT`),
  KEY `KONTO_ID` (`KONTO_ID`),
  KEY `KONTONUMMER` (`KONTONUMMER`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=4 ;

-- 
-- Daten für Tabelle `GELD_KONTEN`
-- 

INSERT INTO `GELD_KONTEN` (`KONTO_DAT`, `KONTO_ID`, `BEZEICHNUNG`, `BEGUENSTIGTER`, `KONTONUMMER`, `BLZ`, `INSTITUT`, `AKTUELL`) VALUES (1, 1, 'Hausverwaltung\r\nMSUTER - Konto', 'Hausverwaltung\r\nMSUTER', '123123321', '100000999', 'Berlussimo Bank', '1'),
(2, 2, 'Geldkonto Musterobjekt', 'Muster Hausverwaltungq', '222212121', '1212121212', 'Berlussimo Bank Berlin', '1'),
(3, 3, 'Kautionskonto Musterobjekt', 'Muster Hausverwaltung', '0580400001', '1212121212', 'Berlussimo Bank Berlin', '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `GELD_KONTEN_ZUWEISUNG`
-- 

CREATE TABLE `GELD_KONTEN_ZUWEISUNG` (
  `ZUWEISUNG_DAT` int(7) NOT NULL auto_increment,
  `ZUWEISUNG_ID` int(7) NOT NULL default '0',
  `KONTO_ID` int(4) NOT NULL default '0',
  `KOSTENTRAEGER_TYP` varchar(30) collate latin1_german2_ci NOT NULL default '',
  `KOSTENTRAEGER_ID` int(7) NOT NULL default '0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`ZUWEISUNG_DAT`),
  KEY `KONTO_ID` (`KONTO_ID`),
  KEY `KOSTENTRAEGER_TYP` (`KOSTENTRAEGER_TYP`),
  KEY `KOSTENTRAEGER_ID` (`KOSTENTRAEGER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=4 ;

-- 
-- Daten für Tabelle `GELD_KONTEN_ZUWEISUNG`
-- 

INSERT INTO `GELD_KONTEN_ZUWEISUNG` (`ZUWEISUNG_DAT`, `ZUWEISUNG_ID`, `KONTO_ID`, `KOSTENTRAEGER_TYP`, `KOSTENTRAEGER_ID`, `AKTUELL`) VALUES (1, 1, 1, 'Partner', 1, '1'),
(2, 2, 2, 'Objekt', 1, '1'),
(3, 3, 3, 'Objekt', 1, '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `GELD_KONTO_BUCHUNGEN`
-- 

CREATE TABLE `GELD_KONTO_BUCHUNGEN` (
  `GELD_KONTO_BUCHUNGEN_DAT` int(7) NOT NULL auto_increment,
  `GELD_KONTO_BUCHUNGEN_ID` int(7) NOT NULL default '0',
  `G_BUCHUNGSNUMMER` int(6) NOT NULL default '0',
  `KONTO_AUSZUGSNUMMER` int(7) NOT NULL default '0',
  `ERFASS_NR` varchar(20) collate latin1_german2_ci default NULL,
  `BETRAG` decimal(10,2) NOT NULL default '0.00',
  `VERWENDUNGSZWECK` mediumtext collate latin1_german2_ci,
  `GELDKONTO_ID` int(7) NOT NULL default '0',
  `KONTENRAHMEN_KONTO` int(6) NOT NULL default '0',
  `DATUM` date NOT NULL default '0000-00-00',
  `KOSTENTRAEGER_TYP` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `KOSTENTRAEGER_ID` int(7) NOT NULL default '0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`GELD_KONTO_BUCHUNGEN_DAT`),
  KEY `KONTENRAHMEN_KONTO` (`KONTENRAHMEN_KONTO`),
  KEY `KOSTENTRAEGER_TYP` (`KOSTENTRAEGER_TYP`),
  KEY `KOSTENTRAEGER_ID` (`KOSTENTRAEGER_ID`),
  KEY `DATUM` (`DATUM`),
  KEY `GELDKONTO_ID` (`GELDKONTO_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=4 ;

-- 
-- Daten für Tabelle `GELD_KONTO_BUCHUNGEN`
-- 

INSERT INTO `GELD_KONTO_BUCHUNGEN` (`GELD_KONTO_BUCHUNGEN_DAT`, `GELD_KONTO_BUCHUNGEN_ID`, `G_BUCHUNGSNUMMER`, `KONTO_AUSZUGSNUMMER`, `ERFASS_NR`, `BETRAG`, `VERWENDUNGSZWECK`, `GELDKONTO_ID`, `KONTENRAHMEN_KONTO`, `DATUM`, `KOSTENTRAEGER_TYP`, `KOSTENTRAEGER_ID`, `AKTUELL`) VALUES (1, 1, 1, 1, '1', 10000.00, 'Geldkontostand, Übernahme vom 01.11.2010', 2, 80001, '2010-11-02', 'Objekt', 1, '1'),
(2, 2, 2, 1, '2', -2500.00, 'Grundsteuer 3. Quartal', 2, 2000, '2010-11-02', 'Objekt', 1, '1'),
(3, 3, 3, 1, '3', -1000.00, 'Berlussimo Versicherungen 2010', 2, 3000, '2010-11-02', 'Objekt', 1, '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `GEWERKE`
-- 

CREATE TABLE `GEWERKE` (
  `G_DAT` int(7) NOT NULL auto_increment,
  `G_ID` int(7) NOT NULL default '0',
  `BEZEICHNUNG` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`G_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `GEWERKE`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `HAUS`
-- 

CREATE TABLE `HAUS` (
  `HAUS_DAT` int(11) NOT NULL auto_increment,
  `HAUS_ID` int(11) NOT NULL default '0',
  `HAUS_STRASSE` varchar(200) character set utf8 NOT NULL default '',
  `HAUS_NUMMER` varchar(6) collate latin1_german2_ci NOT NULL default '',
  `HAUS_STADT` varchar(200) collate latin1_german2_ci NOT NULL default '',
  `HAUS_PLZ` int(11) NOT NULL default '0',
  `HAUS_QM` decimal(5,2) NOT NULL default '0.00',
  `HAUS_AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  `OBJEKT_ID` int(11) NOT NULL default '0',
  UNIQUE KEY `HAUS_DAT` (`HAUS_DAT`),
  KEY `HAUS_ID` (`HAUS_ID`),
  KEY `OBJEKT_ID` (`OBJEKT_ID`),
  KEY `HAUS_STRASSE` (`HAUS_STRASSE`),
  KEY `HAUS_NUMMER` (`HAUS_NUMMER`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `HAUS`
-- 

INSERT INTO `HAUS` (`HAUS_DAT`, `HAUS_ID`, `HAUS_STRASSE`, `HAUS_NUMMER`, `HAUS_STADT`, `HAUS_PLZ`, `HAUS_QM`, `HAUS_AKTUELL`, `OBJEKT_ID`) VALUES (1, 1, 'Fontanestr.', '1', 'Berlin', 14193, 500.00, '1', 1);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `KASSEN`
-- 

CREATE TABLE `KASSEN` (
  `KASSEN_DAT` int(4) NOT NULL auto_increment,
  `KASSEN_ID` int(4) NOT NULL default '0',
  `KASSEN_NAME` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `KASSEN_VERWALTER` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`KASSEN_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `KASSEN`
-- 

INSERT INTO `KASSEN` (`KASSEN_DAT`, `KASSEN_ID`, `KASSEN_NAME`, `KASSEN_VERWALTER`, `AKTUELL`) VALUES (1, 1, 'MUSTERKASSE', 'Hr. Mustermann', '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `KASSEN_BUCH`
-- 

CREATE TABLE `KASSEN_BUCH` (
  `KASSEN_BUCH_DAT` int(7) NOT NULL auto_increment,
  `KASSEN_BUCH_ID` int(7) NOT NULL default '0',
  `KASSEN_ID` int(4) NOT NULL default '0',
  `ZAHLUNGSTYP` enum('Einnahmen','Ausgaben') collate latin1_german2_ci NOT NULL default 'Einnahmen',
  `BETRAG` decimal(10,2) NOT NULL default '0.00',
  `DATUM` date NOT NULL default '0000-00-00',
  `BELEG_TEXT` varchar(200) collate latin1_german2_ci NOT NULL default '',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  `KOSTENTRAEGER_TYP` varchar(30) collate latin1_german2_ci default NULL,
  `KOSTENTRAEGER_ID` int(6) default NULL,
  PRIMARY KEY  (`KASSEN_BUCH_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `KASSEN_BUCH`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `KASSEN_PARTNER`
-- 

CREATE TABLE `KASSEN_PARTNER` (
  `DAT` int(6) NOT NULL auto_increment,
  `KASSEN_ID` int(6) NOT NULL default '0',
  `PARTNER_ID` int(6) NOT NULL default '0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `KASSEN_PARTNER`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `KONTENRAHMEN`
-- 

CREATE TABLE `KONTENRAHMEN` (
  `KONTENRAHMEN_DAT` int(6) NOT NULL auto_increment,
  `KONTENRAHMEN_ID` int(6) NOT NULL default '0',
  `NAME` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`KONTENRAHMEN_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `KONTENRAHMEN`
-- 

INSERT INTO `KONTENRAHMEN` (`KONTENRAHMEN_DAT`, `KONTENRAHMEN_ID`, `NAME`, `AKTUELL`) VALUES (1, 1, 'Musterkontenrahmen', '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `KONTENRAHMEN_GRUPPEN`
-- 

CREATE TABLE `KONTENRAHMEN_GRUPPEN` (
  `KONTENRAHMEN_GRUPPEN_DAT` int(7) NOT NULL auto_increment,
  `KONTENRAHMEN_GRUPPEN_ID` int(7) NOT NULL default '0',
  `BEZEICHNUNG` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`KONTENRAHMEN_GRUPPEN_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=6 ;

-- 
-- Daten für Tabelle `KONTENRAHMEN_GRUPPEN`
-- 

INSERT INTO `KONTENRAHMEN_GRUPPEN` (`KONTENRAHMEN_GRUPPEN_DAT`, `KONTENRAHMEN_GRUPPEN_ID`, `BEZEICHNUNG`, `AKTUELL`) VALUES (1, 1, 'Mieteinnahmen', '1'),
(2, 2, 'Ausgaben für Reinigung', '1'),
(3, 3, 'Steuern', '1'),
(4, 4, 'Versicherungen', '1'),
(5, 5, 'Fremdgeld', '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `KONTENRAHMEN_KONTEN`
-- 

CREATE TABLE `KONTENRAHMEN_KONTEN` (
  `KONTENRAHMEN_KONTEN_DAT` int(7) NOT NULL auto_increment,
  `KONTENRAHMEN_KONTEN_ID` int(7) NOT NULL default '0',
  `KONTO` int(6) NOT NULL default '0',
  `BEZEICHNUNG` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `GRUPPE` int(7) NOT NULL default '0',
  `KONTO_ART` int(7) NOT NULL default '0',
  `KONTENRAHMEN_ID` int(6) NOT NULL default '0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`KONTENRAHMEN_KONTEN_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=5 ;

-- 
-- Daten für Tabelle `KONTENRAHMEN_KONTEN`
-- 

INSERT INTO `KONTENRAHMEN_KONTEN` (`KONTENRAHMEN_KONTEN_DAT`, `KONTENRAHMEN_KONTEN_ID`, `KONTO`, `BEZEICHNUNG`, `GRUPPE`, `KONTO_ART`, `KONTENRAHMEN_ID`, `AKTUELL`) VALUES (1, 1, 80001, 'Einnahmen aus Mieten', 1, 1, 1, '1'),
(2, 2, 1000, 'Reinigunsmittel', 2, 2, 1, '1'),
(3, 3, 2000, 'Grundsteuer', 3, 2, 1, '1'),
(4, 4, 3000, 'Gebäudeversicherung', 4, 2, 1, '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `KONTENRAHMEN_KONTOARTEN`
-- 

CREATE TABLE `KONTENRAHMEN_KONTOARTEN` (
  `KONTENRAHMEN_KONTOART_DAT` int(7) NOT NULL auto_increment,
  `KONTENRAHMEN_KONTOART_ID` int(7) NOT NULL default '0',
  `KONTOART` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`KONTENRAHMEN_KONTOART_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=3 ;

-- 
-- Daten für Tabelle `KONTENRAHMEN_KONTOARTEN`
-- 

INSERT INTO `KONTENRAHMEN_KONTOARTEN` (`KONTENRAHMEN_KONTOART_DAT`, `KONTENRAHMEN_KONTOART_ID`, `KONTOART`, `AKTUELL`) VALUES (1, 1, 'Einnahmen', '1'),
(2, 2, 'Ausgaben', '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `KONTENRAHMEN_ZUWEISUNG`
-- 

CREATE TABLE `KONTENRAHMEN_ZUWEISUNG` (
  `DAT` int(6) NOT NULL auto_increment,
  `ID` int(6) NOT NULL default '0',
  `TYP` varchar(30) collate latin1_german2_ci NOT NULL default '',
  `TYP_ID` int(6) NOT NULL default '0',
  `KONTENRAHMEN_ID` int(6) NOT NULL default '0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=3 ;

-- 
-- Daten für Tabelle `KONTENRAHMEN_ZUWEISUNG`
-- 

INSERT INTO `KONTENRAHMEN_ZUWEISUNG` (`DAT`, `ID`, `TYP`, `TYP_ID`, `KONTENRAHMEN_ID`, `AKTUELL`) VALUES (1, 1, 'Objekt', 1, 1, '1'),
(2, 2, 'GELDKONTO', 2, 1, '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `KONTIERUNG_POSITIONEN`
-- 

CREATE TABLE `KONTIERUNG_POSITIONEN` (
  `KONTIERUNG_DAT` int(7) NOT NULL auto_increment,
  `KONTIERUNG_ID` int(7) NOT NULL default '0',
  `BELEG_NR` int(7) NOT NULL default '0',
  `POSITION` int(7) NOT NULL default '0',
  `MENGE` decimal(10,2) NOT NULL default '0.00',
  `EINZEL_PREIS` decimal(10,4) NOT NULL default '0.0000',
  `GESAMT_SUMME` decimal(10,2) NOT NULL default '0.00',
  `MWST_SATZ` int(2) NOT NULL default '0',
  `SKONTO` decimal(3,2) default NULL,
  `RABATT_SATZ` decimal(4,2) NOT NULL default '0.00',
  `KONTENRAHMEN_KONTO` int(7) NOT NULL default '0',
  `KOSTENTRAEGER_TYP` varchar(30) collate latin1_german2_ci NOT NULL default '',
  `KOSTENTRAEGER_ID` int(7) NOT NULL default '0',
  `KONTIERUNGS_DATUM` date NOT NULL default '0000-00-00',
  `VERWENDUNGS_JAHR` decimal(4,0) NOT NULL default '0',
  `WEITER_VERWENDEN` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`KONTIERUNG_DAT`),
  KEY `BELEG_NR` (`BELEG_NR`),
  KEY `KOSTENTRAEGER_TYP` (`KOSTENTRAEGER_TYP`),
  KEY `KOSTENTRAEGER_ID` (`KOSTENTRAEGER_ID`),
  KEY `POSITION` (`POSITION`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `KONTIERUNG_POSITIONEN`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `LAGER`
-- 

CREATE TABLE `LAGER` (
  `LAGER_DAT` int(4) NOT NULL auto_increment,
  `LAGER_ID` int(4) NOT NULL default '0',
  `LAGER_NAME` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `LAGER_VERWALTER` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`LAGER_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `LAGER`
-- 

INSERT INTO `LAGER` (`LAGER_DAT`, `LAGER_ID`, `LAGER_NAME`, `LAGER_VERWALTER`, `AKTUELL`) VALUES (1, 1, 'Musterlager', 'Hr. Mustermann', '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `LAGER_PARTNER`
-- 

CREATE TABLE `LAGER_PARTNER` (
  `DAT` int(6) NOT NULL auto_increment,
  `LAGER_ID` int(6) NOT NULL default '0',
  `PARTNER_ID` int(6) NOT NULL default '0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `LAGER_PARTNER`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `LEISTUNGSKATALOG`
-- 

CREATE TABLE `LEISTUNGSKATALOG` (
  `LK_DAT` int(7) NOT NULL auto_increment,
  `LK_ID` int(7) NOT NULL default '0',
  `BEZEICHNUNG` varchar(160) collate latin1_german2_ci NOT NULL default '',
  `GEWERK` int(11) default NULL,
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`LK_DAT`),
  KEY `LK_ID` (`LK_ID`),
  KEY `BEZEICHNUNG` (`BEZEICHNUNG`),
  KEY `GEWERK` (`GEWERK`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `LEISTUNGSKATALOG`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `LIEFERSCHEINE`
-- 

CREATE TABLE `LIEFERSCHEINE` (
  `L_DAT` int(7) NOT NULL auto_increment,
  `L_ID` int(7) NOT NULL default '0',
  `DATUM` date NOT NULL default '0000-00-00',
  `LI_TYP` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `LI_ID` int(7) NOT NULL default '0',
  `EMPF_TYP` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `EMPF_ID` int(7) NOT NULL default '0',
  `L_NR` int(30) NOT NULL default '0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`L_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `LIEFERSCHEINE`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `LIEFERSCHEINE_OK`
-- 

CREATE TABLE `LIEFERSCHEINE_OK` (
  `L_DAT` int(7) NOT NULL auto_increment,
  `L_ID` int(7) NOT NULL default '0',
  `DATUM` date NOT NULL default '0000-00-00',
  `LI_TYP` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `LI_ID` int(7) NOT NULL default '0',
  `EMPF_TYP` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `EMPF_ID` int(7) NOT NULL default '0',
  `L_NR` int(30) NOT NULL default '0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`L_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `LIEFERSCHEINE_OK`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `LV`
-- 

CREATE TABLE `LV` (
  `LV_ID` int(7) NOT NULL auto_increment,
  `TEXT` varchar(100) collate latin1_german2_ci NOT NULL default '',
  `GEWERK_ID` int(6) NOT NULL default '0',
  `PROJEKT_ID` int(1) NOT NULL default '0',
  PRIMARY KEY  (`LV_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `LV`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `LV_GLIEDERUNG`
-- 

CREATE TABLE `LV_GLIEDERUNG` (
  `G_ID` int(11) NOT NULL auto_increment,
  `NR` decimal(10,0) NOT NULL default '0',
  `TEXT` varchar(255) collate latin1_german2_ci NOT NULL default '',
  PRIMARY KEY  (`G_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `LV_GLIEDERUNG`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `LV_K_ARTIKEL`
-- 

CREATE TABLE `LV_K_ARTIKEL` (
  `A_ID` int(6) NOT NULL auto_increment,
  `P_ID` int(6) NOT NULL default '0',
  `BEZEICHNUNG` varchar(255) collate latin1_german2_ci NOT NULL default '',
  `ZEITWERT` decimal(10,2) NOT NULL default '0.00',
  PRIMARY KEY  (`A_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `LV_K_ARTIKEL`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `LV_K_POSITIONEN`
-- 

CREATE TABLE `LV_K_POSITIONEN` (
  `P_ID` int(6) NOT NULL auto_increment,
  `TEXT` varchar(255) collate latin1_german2_ci NOT NULL default '',
  `GEWERK_ID` int(1) NOT NULL default '0',
  `G_ID` int(7) NOT NULL default '0',
  PRIMARY KEY  (`P_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `LV_K_POSITIONEN`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `LV_POSITIONEN`
-- 

CREATE TABLE `LV_POSITIONEN` (
  `LVP_ID` int(7) NOT NULL auto_increment,
  `LV_ID` int(7) NOT NULL default '0',
  `G_ID` int(7) NOT NULL default '0',
  `P_ID` int(7) NOT NULL default '0',
  `A_ID` int(7) NOT NULL default '0',
  `ARTIKEL_NR` varchar(30) collate latin1_german2_ci NOT NULL default '',
  `ARTL_LIEFERANT` int(7) NOT NULL default '0',
  `PREIS` decimal(10,2) NOT NULL default '0.00',
  `ZEITWERT` decimal(10,0) NOT NULL default '0',
  PRIMARY KEY  (`LVP_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `LV_POSITIONEN`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `LV_PROJEKTE`
-- 

CREATE TABLE `LV_PROJEKTE` (
  `PROJEKT_ID` int(7) NOT NULL auto_increment,
  `TEXT` varchar(255) collate latin1_german2_ci NOT NULL default '',
  `AUTHOR` varchar(20) collate latin1_german2_ci NOT NULL default '',
  `ART` enum('Altbau','Neubau','Sanierung','Teilsanierung') collate latin1_german2_ci NOT NULL default 'Altbau',
  PRIMARY KEY  (`PROJEKT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `LV_PROJEKTE`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `MIETENTWICKLUNG`
-- 

CREATE TABLE `MIETENTWICKLUNG` (
  `MIETENTWICKLUNG_DAT` int(11) NOT NULL auto_increment,
  `MIETENTWICKLUNG_ID` int(11) NOT NULL default '0',
  `KOSTENTRAEGER_TYP` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `KOSTENTRAEGER_ID` int(11) NOT NULL default '0',
  `KOSTENKATEGORIE` varchar(100) collate latin1_german2_ci NOT NULL default '',
  `ANFANG` date NOT NULL default '0000-00-00',
  `ENDE` date NOT NULL default '0000-00-00',
  `BETRAG` decimal(10,2) NOT NULL default '0.00',
  `MIETENTWICKLUNG_AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`MIETENTWICKLUNG_DAT`),
  KEY `KOSTENTRAEGER_TYP` (`KOSTENTRAEGER_TYP`),
  KEY `KOSTENTRAEGER_ID` (`KOSTENTRAEGER_ID`),
  KEY `BETRAG` (`BETRAG`),
  KEY `ANFANG` (`ANFANG`),
  KEY `ENDE` (`ENDE`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=7 ;

-- 
-- Daten für Tabelle `MIETENTWICKLUNG`
-- 

INSERT INTO `MIETENTWICKLUNG` (`MIETENTWICKLUNG_DAT`, `MIETENTWICKLUNG_ID`, `KOSTENTRAEGER_TYP`, `KOSTENTRAEGER_ID`, `KOSTENKATEGORIE`, `ANFANG`, `ENDE`, `BETRAG`, `MIETENTWICKLUNG_AKTUELL`)
VALUES (1, 1, 'Mietvertrag', 1, 'Miete kalt', '2010-11-01', '0000-00-00', 250.00, '1'),
  (2, 2, 'Mietvertrag', 1, 'Heizkosten Vorauszahlung', '2010-11-01', '0000-00-00', 50.00, '1'),
  (3, 3, 'Mietvertrag', 1, 'Nebenkosten Vorauszahlung', '2010-11-01', '0000-00-00', 100.00, '1'),
  (4, 4, 'Mietvertrag', 2, 'Miete kalt', '2010-11-01', '0000-00-00', 500.00, '1'),
  (5, 5, 'Mietvertrag', 2, 'Heizkosten Vorauszahlung', '2010-11-01', '0000-00-00', 100.00, '1'),
  (6, 6, 'Mietvertrag', 2, 'Nebenkosten Vorauszahlung', '2010-11-01', '0000-00-00', 100.00, '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `MIETER_MAHNLISTEN`
-- 

CREATE TABLE `MIETER_MAHNLISTEN` (
  `MAHN_DAT` int(7) NOT NULL auto_increment,
  `DATUM` date NOT NULL default '0000-00-00',
  `MIETVERTRAG_ID` int(11) NOT NULL default '0',
  `SALDO` decimal(10,2) NOT NULL default '0.00',
  `ZAHLUNGSFRIST_Z` date NOT NULL default '0000-00-00',
  `ZAHLUNGSFRIST_M` date NOT NULL default '0000-00-00',
  `MAHN_GEB` decimal(6,2) NOT NULL default '0.00',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`MAHN_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=3 ;

-- 
-- Daten für Tabelle `MIETER_MAHNLISTEN`
-- 

INSERT INTO `MIETER_MAHNLISTEN` (`MAHN_DAT`, `DATUM`, `MIETVERTRAG_ID`, `SALDO`, `ZAHLUNGSFRIST_Z`, `ZAHLUNGSFRIST_M`, `MAHN_GEB`, `AKTUELL`) VALUES (1, '2010-11-02', 1, -400.00, '2010-10-22', '0000-00-00', 0.00, '1'),
(2, '2010-11-02', 2, -700.00, '0000-00-00', '0000-00-00', 0.00, '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `MIETSPIEGEL`
-- 

CREATE TABLE `MIETSPIEGEL` (
  `DAT` int(7) NOT NULL auto_increment,
  `JAHR` varchar(4) collate latin1_german2_ci NOT NULL default '2009',
  `FELD` char(3) collate latin1_german2_ci NOT NULL default '',
  `U_WERT` decimal(4,2) NOT NULL default '0.00',
  `M_WERT` decimal(4,2) NOT NULL default '0.00',
  `O_WERT` decimal(4,2) NOT NULL default '0.00',
  PRIMARY KEY  (`DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=108 ;

-- 
-- Daten für Tabelle `MIETSPIEGEL`
-- 

INSERT INTO `MIETSPIEGEL` (`DAT`, `JAHR`, `FELD`, `U_WERT`, `M_WERT`, `O_WERT`) VALUES (1, '2009', 'A5', 4.30, 4.94, 6.25),
(2, '2009', 'D5', 4.04, 4.73, 5.77),
(3, '2009', 'G5', 4.15, 4.55, 5.27),
(4, '2009', 'G4', 4.19, 4.95, 5.62),
(5, '2009', 'J4', 3.57, 4.55, 5.82),
(6, '2009', 'A2', 3.11, 5.07, 6.93),
(7, '2009', 'A4', 4.80, 5.40, 6.29),
(8, '2009', 'A6', 3.79, 5.08, 6.34),
(9, '2009', 'A7', 3.94, 4.51, 5.46),
(10, '2009', 'A10', 4.43, 5.58, 6.01),
(11, '2009', 'B2', 3.16, 5.15, 6.70),
(12, '2009', 'B4', 4.53, 5.70, 6.36),
(13, '2009', 'B5', 4.12, 4.64, 5.53),
(14, '2009', 'B6', 3.97, 5.14, 6.50),
(15, '2009', 'B7', 4.50, 4.96, 6.07),
(16, '2009', 'B10', 5.06, 6.11, 6.66),
(17, '2009', 'C2', 3.68, 4.89, 5.36),
(18, '2009', 'C3', 3.38, 3.56, 4.01),
(19, '2009', 'C4', 5.35, 6.22, 6.70),
(20, '2009', 'C5', 4.49, 5.70, 6.87),
(21, '2009', 'C6', 4.49, 5.88, 6.69),
(22, '2009', 'C7', 5.70, 6.53, 6.91),
(23, '2009', 'C10', 4.73, 5.55, 6.37),
(24, '2009', 'D1', 3.14, 3.51, 4.30),
(25, '2009', 'D2', 3.12, 4.54, 6.03),
(26, '2009', 'D3', 2.98, 3.21, 3.44),
(27, '2009', 'D4', 4.27, 4.89, 5.80),
(28, '2009', 'D6', 3.94, 4.72, 6.10),
(29, '2009', 'D7', 3.83, 4.29, 4.90),
(30, '2009', 'D8', 4.78, 5.52, 6.38),
(31, '2009', 'D10', 4.23, 4.80, 5.24),
(32, '2009', 'D11', 5.90, 6.41, 6.80),
(33, '2009', 'E1', 3.14, 3.21, 3.45),
(34, '2009', 'E2', 3.77, 4.85, 6.05),
(35, '2009', 'E3', 3.13, 3.44, 3.59),
(36, '2009', 'E4', 3.91, 4.81, 5.65),
(37, '2009', 'E5', 4.27, 4.72, 5.41),
(38, '2009', 'E6', 4.19, 4.86, 5.60),
(39, '2009', 'E7', 4.38, 4.96, 5.28),
(40, '2009', 'E8', 4.84, 5.97, 7.02),
(41, '2009', 'E10', 4.32, 5.04, 5.60),
(42, '2009', 'E11', 5.69, 7.03, 7.56),
(43, '2009', 'F1', 2.61, 2.85, 3.10),
(44, '2009', 'F2', 4.85, 5.42, 6.06),
(45, '2009', 'F4', 4.59, 5.45, 6.49),
(46, '2009', 'F5', 4.15, 4.55, 5.27),
(47, '2009', 'F6', 4.14, 4.82, 5.49),
(48, '2009', 'F7', 4.65, 5.36, 6.40),
(49, '2009', 'F8', 5.81, 6.80, 7.50),
(50, '2009', 'F10', 4.66, 4.97, 5.95),
(51, '2009', 'F11', 6.00, 7.17, 8.47),
(52, '2009', 'G1', 1.82, 3.29, 4.38),
(53, '2009', 'G2', 3.65, 4.60, 6.00),
(54, '2009', 'G3', 3.00, 3.17, 3.46),
(55, '2009', 'G6', 3.61, 4.18, 5.34),
(56, '2009', 'G7', 3.91, 4.10, 4.43),
(57, '2009', 'G8', 4.25, 5.38, 6.96),
(58, '2009', 'G9', 5.00, 6.37, 6.95),
(59, '2009', 'G10', 3.87, 4.36, 4.69),
(60, '2009', 'G11', 4.98, 6.13, 7.18),
(61, '2009', 'H1', 2.99, 3.40, 4.20),
(62, '2009', 'H2', 3.78, 4.85, 6.00),
(63, '2009', 'H3', 2.91, 3.21, 3.48),
(64, '2009', 'H4', 4.07, 4.70, 5.40),
(65, '2009', 'H5', 4.45, 5.02, 5.74),
(66, '2009', 'H6', 3.90, 4.51, 5.20),
(67, '2009', 'H7', 4.03, 4.62, 5.19),
(68, '2009', 'H8', 3.72, 5.56, 6.82),
(69, '2009', 'H9', 5.40, 5.80, 5.92),
(70, '2009', 'H10', 3.94, 4.51, 5.04),
(71, '2009', 'H11', 5.77, 6.87, 7.50),
(72, '2009', 'I1', 2.63, 3.08, 4.21),
(73, '2009', 'I2', 3.77, 5.08, 6.33),
(74, '2009', 'I3', 3.05, 3.12, 3.25),
(75, '2009', 'I4', 4.62, 5.38, 6.65),
(76, '2009', 'I5', 4.34, 4.98, 5.71),
(77, '2009', 'I6', 4.18, 4.70, 5.18),
(78, '2009', 'I7', 4.85, 5.49, 6.04),
(79, '2009', 'I8', 5.90, 7.44, 9.05),
(80, '2009', 'I9', 5.26, 6.62, 8.13),
(81, '2009', 'J1', 2.53, 2.77, 3.40),
(82, '2009', 'J2', 3.45, 4.46, 5.54),
(83, '2009', 'J4', 3.57, 4.55, 5.82),
(84, '2009', 'J7', 3.72, 4.04, 4.16),
(85, '2009', 'J10', 3.57, 4.29, 5.07),
(86, '2009', 'J11', 4.35, 5.96, 7.15),
(87, '2009', 'K1', 2.92, 3.15, 3.61),
(88, '2009', 'K2', 3.71, 4.53, 5.50),
(89, '2009', 'K4', 4.40, 4.81, 5.34),
(90, '2009', 'K5', 4.58, 5.25, 5.89),
(91, '2009', 'K6', 3.29, 5.12, 6.38),
(92, '2009', 'K7', 3.99, 4.96, 5.62),
(93, '2009', 'K8', 5.85, 6.57, 7.80),
(94, '2009', 'K9', 4.65, 7.04, 8.00),
(95, '2009', 'K10', 3.86, 4.32, 4.95),
(96, '2009', 'K11', 5.36, 6.54, 7.55),
(97, '2009', 'L1', 2.85, 3.11, 3.53),
(98, '2009', 'L2', 4.24, 5.18, 6.57),
(99, '2009', 'L3', 2.81, 3.04, 3.27),
(100, '2009', 'L4', 4.40, 5.00, 6.00),
(101, '2009', 'L5', 3.83, 4.80, 5.94),
(102, '2009', 'L6', 4.20, 5.96, 10.28),
(103, '2009', 'L7', 5.23, 6.50, 8.17),
(104, '2009', 'L8', 6.01, 7.35, 8.51),
(105, '2009', 'L9', 6.53, 7.46, 8.35),
(106, '2009', 'L10', 4.27, 4.57, 5.14),
(107, '2009', 'L11', 5.02, 7.21, 9.00);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `MIETVERTRAG`
-- 

CREATE TABLE `MIETVERTRAG` (
  `MIETVERTRAG_DAT` int(11) NOT NULL auto_increment,
  `MIETVERTRAG_ID` int(11) NOT NULL default '0',
  `MIETVERTRAG_VON` date NOT NULL default '0000-00-00',
  `MIETVERTRAG_BIS` date NOT NULL default '0000-00-00',
  `EINHEIT_ID` int(11) NOT NULL default '0',
  `MIETVERTRAG_AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`MIETVERTRAG_DAT`),
  KEY `EINHEIT_ID` (`EINHEIT_ID`),
  KEY `MIETVERTRAG_ID` (`MIETVERTRAG_ID`),
  KEY `MIETVERTRAG_BIS` (`MIETVERTRAG_BIS`),
  KEY `MIETVERTRAG_VON` (`MIETVERTRAG_VON`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=3 ;

-- 
-- Daten für Tabelle `MIETVERTRAG`
-- 

INSERT INTO `MIETVERTRAG` (`MIETVERTRAG_DAT`, `MIETVERTRAG_ID`, `MIETVERTRAG_VON`, `MIETVERTRAG_BIS`, `EINHEIT_ID`, `MIETVERTRAG_AKTUELL`) VALUES (1, 1, '2010-11-01', '0000-00-00', 1, '1'),
(2, 2, '2010-11-01', '0000-00-00', 2, '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `MONATSABSCHLUSS`
-- 

CREATE TABLE `MONATSABSCHLUSS` (
  `ABSCHLUSS_DAT` int(7) NOT NULL auto_increment,
  `MIETVERTRAG_ID` int(7) NOT NULL default '0',
  `DATUM` date NOT NULL default '0000-00-00',
  `BETRAG` decimal(10,2) NOT NULL default '0.00',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ABSCHLUSS_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `MONATSABSCHLUSS`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `MS_SONDERMERKMALE`
-- 

CREATE TABLE `MS_SONDERMERKMALE` (
  `DAT` int(7) NOT NULL auto_increment,
  `JAHR` varchar(4) collate latin1_german2_ci NOT NULL default '',
  `MERKMAL` varchar(100) collate latin1_german2_ci NOT NULL default '',
  `WERT` decimal(4,2) NOT NULL default '0.00',
  `A_KLASSE` int(2) default NULL,
  PRIMARY KEY  (`DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=12 ;

-- 
-- Daten für Tabelle `MS_SONDERMERKMALE`
-- 

INSERT INTO `MS_SONDERMERKMALE` (`DAT`, `JAHR`, `MERKMAL`, `WERT`, `A_KLASSE`) VALUES (7, '2009', 'Erdgeschosswohnung', -0.19, NULL),
(8, '2009', 'Ohne SH, ohne Bad, mit IWC', -0.33, 1),
(9, '2009', 'Ohne SH, ohne Bad, mit IWC', -0.37, 3),
(10, '2009', 'NB mit SH oder Bad und mit IWC', -1.27, 5),
(11, '2009', 'NB mit SH oder Bad und mit IWC', -0.98, 6);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `OBJEKT`
-- 

CREATE TABLE `OBJEKT` (
  `OBJEKT_DAT` int(11) NOT NULL auto_increment,
  `OBJEKT_ID` int(11) NOT NULL default '0',
  `OBJEKT_AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  `OBJEKT_KURZNAME` varchar(20) character set latin1 collate latin1_german1_ci NOT NULL default '',
  `EIGENTUEMER_PARTNER` int(7) NOT NULL default '0',
  UNIQUE KEY `OBJEKT_DAT` (`OBJEKT_DAT`),
  KEY `OBJEKT_KURZNAME` (`OBJEKT_KURZNAME`),
  KEY `OBJEKT_ID` (`OBJEKT_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `OBJEKT`
-- 

INSERT INTO `OBJEKT` (`OBJEKT_DAT`, `OBJEKT_ID`, `OBJEKT_AKTUELL`, `OBJEKT_KURZNAME`, `EIGENTUEMER_PARTNER`) VALUES (1, 1, '1', 'Musterobjekt', 1);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `OBJEKT_PARTNER`
-- 

CREATE TABLE `OBJEKT_PARTNER` (
  `DAT` int(6) NOT NULL auto_increment,
  `OBJEKT_ID` int(6) NOT NULL default '0',
  `PARTNER_ID` int(6) NOT NULL default '0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  `VON` date NOT NULL default '0000-00-00',
  `BIS` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`DAT`),
  KEY `OBJEKT_ID` (`OBJEKT_ID`),
  KEY `PARTNER_ID` (`PARTNER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `OBJEKT_PARTNER`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `PARTNER_LIEFERANT`
-- 

CREATE TABLE `PARTNER_LIEFERANT` (
  `PARTNER_DAT` int(7) NOT NULL auto_increment,
  `PARTNER_ID` int(7) NOT NULL default '0',
  `PARTNER_NAME` mediumtext collate latin1_german2_ci NOT NULL,
  `STRASSE` varchar(100) collate latin1_german2_ci NOT NULL default '',
  `NUMMER` varchar(10) collate latin1_german2_ci NOT NULL default '',
  `PLZ` varchar(10) collate latin1_german2_ci NOT NULL default '',
  `ORT` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `LAND` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`PARTNER_DAT`),
  KEY `PARTNER_ID` (`PARTNER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `PARTNER_LIEFERANT`
-- 

INSERT INTO `PARTNER_LIEFERANT` (`PARTNER_DAT`, `PARTNER_ID`, `PARTNER_NAME`, `STRASSE`, `NUMMER`, `PLZ`, `ORT`, `LAND`, `AKTUELL`) VALUES (1, 1, 'Hausverwaltung\r\nMUSTER', 'Fontanestr.', '1', '14193', 'Berlin', 'Deutschland', '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `PDF_VORLAGEN`
-- 

CREATE TABLE `PDF_VORLAGEN` (
  `DAT` int(7) NOT NULL auto_increment,
  `KURZTEXT` varchar(100) collate latin1_german2_ci NOT NULL default '',
  `TEXT` mediumtext collate latin1_german2_ci NOT NULL,
  PRIMARY KEY  (`DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `PDF_VORLAGEN`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `PERSON`
-- 

CREATE TABLE `PERSON` (
  `PERSON_DAT` int(11) NOT NULL auto_increment,
  `PERSON_ID` int(11) NOT NULL default '0',
  `PERSON_NACHNAME` varchar(200) character set latin1 collate latin1_german1_ci NOT NULL default '',
  `PERSON_VORNAME` varchar(200) collate latin1_german2_ci NOT NULL default '',
  `PERSON_GEBURTSTAG` date NOT NULL default '0000-00-00',
  `PERSON_AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`PERSON_DAT`),
  KEY `PERSON_ID` (`PERSON_ID`),
  KEY `PERSON_NACHNAME` (`PERSON_NACHNAME`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=3 ;

-- 
-- Daten für Tabelle `PERSON`
-- 

INSERT INTO `PERSON` (`PERSON_DAT`, `PERSON_ID`, `PERSON_NACHNAME`, `PERSON_VORNAME`, `PERSON_GEBURTSTAG`, `PERSON_AKTUELL`) VALUES (1, 1, 'Mustermann', 'Max', '1978-12-12', '1'),
(2, 2, 'Musterfrau', 'Melanie', '1976-10-01', '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `PERSON_MIETVERTRAG`
-- 

CREATE TABLE `PERSON_MIETVERTRAG` (
  `PERSON_MIETVERTRAG_DAT` int(11) NOT NULL auto_increment,
  `PERSON_MIETVERTRAG_ID` int(11) NOT NULL default '0',
  `PERSON_MIETVERTRAG_PERSON_ID` int(11) NOT NULL default '0',
  `PERSON_MIETVERTRAG_MIETVERTRAG_ID` int(11) NOT NULL default '0',
  `PERSON_MIETVERTRAG_AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`PERSON_MIETVERTRAG_DAT`),
  KEY `PERSON_MIETVERTRAG_MIETVERTRAG_ID` (`PERSON_MIETVERTRAG_MIETVERTRAG_ID`),
  KEY `PERSON_MIETVERTRAG_PERSON_ID` (`PERSON_MIETVERTRAG_PERSON_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=3 ;

-- 
-- Daten für Tabelle `PERSON_MIETVERTRAG`
-- 

INSERT INTO `PERSON_MIETVERTRAG` (`PERSON_MIETVERTRAG_DAT`, `PERSON_MIETVERTRAG_ID`, `PERSON_MIETVERTRAG_PERSON_ID`, `PERSON_MIETVERTRAG_MIETVERTRAG_ID`, `PERSON_MIETVERTRAG_AKTUELL`) VALUES (1, 1, 1, 1, '1'),
(2, 2, 2, 2, '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `PHP_ABFRAGEN`
-- 

CREATE TABLE `PHP_ABFRAGEN` (
  `ABFRAGE_ID` varchar(30) collate latin1_german2_ci NOT NULL default '',
  `KUERZEL` varchar(30) collate latin1_german2_ci NOT NULL default '',
  `BESCHREIBUNG` varchar(200) collate latin1_german2_ci NOT NULL default '',
  `SQL_ABFRAGE` varchar(200) collate latin1_german2_ci NOT NULL default '',
  PRIMARY KEY  (`ABFRAGE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- 
-- Daten für Tabelle `PHP_ABFRAGEN`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `POSITIONEN_KATALOG`
-- 

CREATE TABLE `POSITIONEN_KATALOG` (
  `KATALOG_DAT` int(7) NOT NULL auto_increment,
  `KATALOG_ID` int(7) NOT NULL default '0',
  `ART_LIEFERANT` int(7) NOT NULL default '0',
  `ARTIKEL_NR` varchar(20) character set utf8 NOT NULL default '',
  `BEZEICHNUNG` mediumtext collate latin1_german2_ci NOT NULL,
  `LISTENPREIS` decimal(10,4) NOT NULL default '0.0000',
  `RABATT_SATZ` decimal(4,2) NOT NULL default '0.00',
  `EINHEIT` varchar(20) collate latin1_german2_ci default NULL,
  `MWST_SATZ` int(2) NOT NULL default '0',
  `SKONTO` decimal(3,2) default NULL,
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`KATALOG_DAT`),
  KEY `ARTIKEL_NR` (`ARTIKEL_NR`),
  KEY `ART_LIEFERANT` (`ART_LIEFERANT`),
  KEY `LISTENPREIS` (`LISTENPREIS`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `POSITIONEN_KATALOG`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `PROTOKOLL`
-- 

CREATE TABLE `PROTOKOLL` (
  `PROTOKOLL_DAT` int(11) NOT NULL auto_increment,
  `PROTOKOLL_WER` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `PROTOKOLL_COMPUTER` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `PROTOKOLL_WANN` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `PROTOKOLL_TABELE` varchar(25) collate latin1_german2_ci NOT NULL default '',
  `PROTOKOLL_DAT_NEU` int(11) NOT NULL default '0',
  `PROTOKOLL_DAT_ALT` int(11) NOT NULL default '0',
  PRIMARY KEY  (`PROTOKOLL_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=44 ;

-- 
-- Daten für Tabelle `PROTOKOLL`
-- 

INSERT INTO `PROTOKOLL` (`PROTOKOLL_DAT`, `PROTOKOLL_WER`, `PROTOKOLL_COMPUTER`, `PROTOKOLL_WANN`, `PROTOKOLL_TABELE`, `PROTOKOLL_DAT_NEU`, `PROTOKOLL_DAT_ALT`) VALUES (1, 'demo', '95.89.5.82', '2010-11-02 08:31:29', 'PARTNER_LIEFERANT', 1, 0),
(2, 'demo', '95.89.5.82', '2010-11-02 08:31:29', 'GELD_KONTEN', 1, 0),
(3, 'demo', '95.89.5.82', '2010-11-02 08:31:39', 'OBJEKT', 1, 0),
(4, 'demo', '95.89.5.82', '2010-11-02 08:32:44', 'HAUS', 1, 0),
(5, 'demo', '95.89.5.82', '2010-11-02 08:33:16', 'EINHEIT', 1, 0),
(6, 'demo', '95.89.5.82', '2010-11-02 08:33:30', 'EINHEIT', 2, 0),
(7, 'demo', '95.89.5.82', '2010-11-02 08:33:52', 'PERSON', 1, 0),
(8, 'demo', '95.89.5.82', '2010-11-02 08:38:11', 'PERSON', 2, 0),
(9, 'demo', '95.89.5.82', '2010-11-02 08:38:41', 'MIETVERTRAG', 1, 0),
(10, 'demo', '95.89.5.82', '2010-11-02 08:38:41', 'PERSON_MIETVERTRAG', 1, 0),
(11, 'demo', '95.89.5.82', '2010-11-02 08:38:41', 'MIETENTWICKLUNG', 0, 1),
(12, 'demo', '95.89.5.82', '2010-11-02 08:38:41', 'MIETENTWICKLUNG', 0, 2),
(13, 'demo', '95.89.5.82', '2010-11-02 08:38:41', 'MIETENTWICKLUNG', 0, 3),
(14, 'demo', '95.89.5.82', '2010-11-02 08:39:49', 'MIETVERTRAG', 2, 0),
(15, 'demo', '95.89.5.82', '2010-11-02 08:39:49', 'PERSON_MIETVERTRAG', 2, 0),
(16, 'demo', '95.89.5.82', '2010-11-02 08:39:49', 'MIETENTWICKLUNG', 0, 4),
(17, 'demo', '95.89.5.82', '2010-11-02 08:39:49', 'MIETENTWICKLUNG', 0, 5),
(18, 'demo', '95.89.5.82', '2010-11-02 08:39:49', 'MIETENTWICKLUNG', 0, 6),
(19, 'demo', '95.89.5.82', '2010-11-02 08:39:49', 'DETAIL', 7, 0),
(20, 'demo', '95.89.5.82', '2010-11-02 08:39:49', 'DETAIL', 8, 0),
(21, 'demo', '95.89.5.82', '2010-11-02 08:39:49', 'DETAIL', 9, 0),
(22, 'demo', '95.89.5.82', '2010-11-02 08:39:49', 'DETAIL', 10, 0),
(23, 'demo', '95.89.5.82', '2010-11-02 08:39:49', 'DETAIL', 11, 0),
(24, 'demo', '95.89.5.82', '2010-11-02 08:39:49', 'DETAIL', 12, 0),
(25, 'demo', '95.89.5.82', '2010-11-02 08:53:05', 'KONTENRAHMEN', 1, 0),
(26, 'demo', '95.89.5.82', '2010-11-02 08:53:20', 'KONTENRAHMEN_KONTOARTEN', 1, 0),
(27, 'demo', '95.89.5.82', '2010-11-02 08:53:25', 'KONTENRAHMEN_KONTOARTEN', 2, 0),
(28, 'demo', '95.89.5.82', '2010-11-02 08:53:45', 'KONTENRAHMEN_GRUPPEN', 1, 0),
(29, 'demo', '95.89.5.82', '2010-11-02 08:54:19', 'KONTENRAHMEN_KONTEN', 1, 0),
(30, 'demo', '95.89.5.82', '2010-11-02 08:54:50', 'KONTENRAHMEN_GRUPPEN', 2, 0),
(31, 'demo', '95.89.5.82', '2010-11-02 08:55:06', 'KONTENRAHMEN_KONTEN', 2, 0),
(32, 'demo', '95.89.5.82', '2010-11-02 08:55:29', 'KONTENRAHMEN_ZUWEISUNG', 1, 0),
(33, 'demo', '95.89.5.82', '2010-11-02 08:59:46', 'KONTENRAHMEN_GRUPPEN', 3, 0),
(34, 'demo', '95.89.5.82', '2010-11-02 08:59:51', 'KONTENRAHMEN_GRUPPEN', 4, 0),
(35, 'demo', '95.89.5.82', '2010-11-02 08:59:56', 'KONTENRAHMEN_GRUPPEN', 5, 0),
(36, 'demo', '95.89.5.82', '2010-11-02 09:00:11', 'KONTENRAHMEN_KONTEN', 3, 0),
(37, 'demo', '95.89.5.82', '2010-11-02 09:00:48', 'KONTENRAHMEN_KONTEN', 4, 0),
(38, 'demo', '95.89.5.82', '2010-11-02 09:02:02', 'KONTENRAHMEN_ZUWEISUNG', 2, 0),
(39, 'demo', '95.89.5.82', '2010-11-02 09:02:36', 'GELD_KONTO_BUCHUNGEN', 1, 0),
(40, 'demo', '95.89.5.82', '2010-11-02 09:03:01', 'GELD_KONTO_BUCHUNGEN', 2, 0),
(41, 'demo', '95.89.5.82', '2010-11-02 09:03:49', 'GELD_KONTO_BUCHUNGEN', 3, 0),
(42, 'demo', '95.89.5.82', '2010-11-02 09:21:53', 'EINHEIT', 3, 0),
(43, 'demo', '95.89.5.82', '2010-11-02 09:22:25', 'EINHEIT', 4, 0);

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `RECHNUNGEN`
-- 

CREATE TABLE `RECHNUNGEN` (
  `RECHNUNG_DAT` int(7) NOT NULL auto_increment,
  `BELEG_NR` int(7) NOT NULL default '0',
  `RECHNUNGSNUMMER` varchar(20) collate latin1_german2_ci default NULL,
  `AUSTELLER_AUSGANGS_RNR` int(7) NOT NULL default '0',
  `EMPFAENGER_EINGANGS_RNR` int(7) NOT NULL default '0',
  `RECHNUNGSTYP` enum('Rechnung','Stornorechnung','Gutschrift','Kassenbeleg','Buchungsbeleg') collate latin1_german2_ci NOT NULL default 'Rechnung',
  `RECHNUNGSDATUM` date NOT NULL default '0000-00-00',
  `EINGANGSDATUM` date NOT NULL default '0000-00-00',
  `NETTO` decimal(10,2) NOT NULL default '0.00',
  `BRUTTO` decimal(10,2) NOT NULL default '0.00',
  `SKONTOBETRAG` decimal(10,2) NOT NULL default '0.00',
  `AUSSTELLER_TYP` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `AUSSTELLER_ID` int(7) NOT NULL default '0',
  `EMPFAENGER_TYP` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `EMPFAENGER_ID` int(7) NOT NULL default '0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  `STATUS_ERFASST` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  `STATUS_VOLLSTAENDIG` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  `STATUS_ZUGEWIESEN` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  `STATUS_ZAHLUNG_FREIGEGEBEN` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  `STATUS_BEZAHLT` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  `STATUS_BESTAETIGT` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  `FAELLIG_AM` date NOT NULL default '0000-00-00',
  `BEZAHLT_AM` date NOT NULL default '0000-00-00',
  `KURZBESCHREIBUNG` mediumtext collate latin1_german2_ci NOT NULL,
  `EMPFANGS_GELD_KONTO` int(4) NOT NULL default '0',
  PRIMARY KEY  (`RECHNUNG_DAT`),
  KEY `AUSSTELLER_TYP` (`AUSSTELLER_TYP`),
  KEY `AUSSTELLER_ID` (`AUSSTELLER_ID`),
  KEY `EMPFAENGER_TYP` (`EMPFAENGER_TYP`),
  KEY `EMPFAENGER_ID` (`EMPFAENGER_ID`),
  KEY `BELEG_NR` (`BELEG_NR`),
  KEY `AKTUELL` (`AKTUELL`),
  KEY `RECHNUNGSDATUM` (`RECHNUNGSDATUM`),
  KEY `STATUS_ERFASST` (`STATUS_ERFASST`),
  KEY `STATUS_VOLLSTAENDIG` (`STATUS_VOLLSTAENDIG`),
  KEY `STATUS_ZUGEWIESEN` (`STATUS_ZUGEWIESEN`),
  KEY `STATUS_ZAHLUNG_FREIGEGEBEN` (`STATUS_ZAHLUNG_FREIGEGEBEN`),
  KEY `STATUS_BEZAHLT` (`STATUS_BEZAHLT`),
  KEY `STATUS_BESTAETIGT` (`STATUS_BESTAETIGT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `RECHNUNGEN`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `RECHNUNGEN_POSITIONEN`
-- 

CREATE TABLE `RECHNUNGEN_POSITIONEN` (
  `RECHNUNGEN_POS_DAT` int(7) NOT NULL auto_increment,
  `RECHNUNGEN_POS_ID` int(7) NOT NULL default '0',
  `POSITION` int(7) NOT NULL default '0',
  `BELEG_NR` int(7) NOT NULL default '0',
  `U_BELEG_NR` int(7) default NULL,
  `ART_LIEFERANT` int(6) NOT NULL default '0',
  `ARTIKEL_NR` varchar(20) collate latin1_german2_ci NOT NULL default '',
  `MENGE` decimal(10,2) NOT NULL default '0.00',
  `PREIS` decimal(10,4) NOT NULL default '0.0000',
  `MWST_SATZ` int(2) NOT NULL default '0',
  `RABATT_SATZ` decimal(4,2) NOT NULL default '0.00',
  `SKONTO` decimal(3,2) default NULL,
  `GESAMT_NETTO` decimal(10,2) NOT NULL default '0.00',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`RECHNUNGEN_POS_DAT`),
  KEY `BELEG_NR` (`BELEG_NR`),
  KEY `U_BELEG_NR` (`U_BELEG_NR`),
  KEY `AKTUELL` (`AKTUELL`),
  KEY `POSITION` (`POSITION`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `RECHNUNGEN_POSITIONEN`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `RECHNUNG_KUERZEL`
-- 

CREATE TABLE `RECHNUNG_KUERZEL` (
  `RK_DAT` int(4) NOT NULL auto_increment,
  `AUSSTELLER_TYP` varchar(30) collate latin1_german2_ci NOT NULL default '',
  `AUSSTELLER_ID` int(6) NOT NULL default '0',
  `KUERZEL` varchar(20) collate latin1_german2_ci default NULL,
  `VON` date NOT NULL default '0000-00-00',
  `BIS` date NOT NULL default '0000-00-00',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`RK_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `RECHNUNG_KUERZEL`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `STUNDENZETTEL`
-- 

CREATE TABLE `STUNDENZETTEL` (
  `ZETTEL_DAT` int(7) NOT NULL auto_increment,
  `ZETTEL_ID` int(7) NOT NULL default '0',
  `BENUTZER_ID` int(7) NOT NULL default '0',
  `BESCHREIBUNG` varchar(100) collate latin1_german2_ci NOT NULL default '',
  `ERFASSUNGSDATUM` date NOT NULL default '0000-00-00',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`ZETTEL_DAT`),
  KEY `BENUTZER_ID` (`BENUTZER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `STUNDENZETTEL`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `STUNDENZETTEL_POS`
-- 

CREATE TABLE `STUNDENZETTEL_POS` (
  `ST_DAT` int(7) NOT NULL auto_increment,
  `ST_ID` int(7) NOT NULL default '0',
  `ZETTEL_ID` int(7) NOT NULL default '0',
  `DATUM` date NOT NULL default '0000-00-00',
  `BEGINN` varchar(20) collate latin1_german2_ci NOT NULL default '',
  `ENDE` varchar(20) collate latin1_german2_ci NOT NULL default '',
  `LEISTUNG_ID` int(11) NOT NULL default '0',
  `DAUER_MIN` decimal(4,0) NOT NULL default '0',
  `KOSTENTRAEGER_TYP` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `KOSTENTRAEGER_ID` int(7) NOT NULL default '0',
  `HINWEIS` mediumtext collate latin1_german2_ci,
  `IN_BELEG` int(7) NOT NULL default '0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`ST_DAT`),
  KEY `DATUM` (`DATUM`),
  KEY `ZETTEL_ID` (`ZETTEL_ID`),
  KEY `LEISTUNG_ID` (`LEISTUNG_ID`),
  KEY `KOSTENTRAEGER_TYP` (`KOSTENTRAEGER_TYP`),
  KEY `KOSTENTRAEGER_ID` (`KOSTENTRAEGER_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `STUNDENZETTEL_POS`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `TRANSFER_TAB`
-- 

CREATE TABLE `TRANSFER_TAB` (
  `MIETVERTRAG_ID` int(6) default NULL,
  `EINHEIT_ID` int(6) NOT NULL default '0',
  `EINHEIT_KURZNAME` varchar(40) collate latin1_german2_ci NOT NULL default '',
  `FM_Kurzname` varchar(40) collate latin1_german2_ci NOT NULL default '',
  `FM_Einheitenname` varchar(40) collate latin1_german2_ci NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- 
-- Daten für Tabelle `TRANSFER_TAB`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `UEBERWEISUNG`
-- 

CREATE TABLE `UEBERWEISUNG` (
  `U_DAT` int(7) NOT NULL auto_increment,
  `DTAUS_ID` int(7) default NULL,
  `DATUM` date NOT NULL default '0000-00-00',
  `A_KONTO_ID` int(7) NOT NULL default '0',
  `E_KONTO_ID` int(7) NOT NULL default '0',
  `BETRAG` decimal(10,2) NOT NULL default '0.00',
  `BETRAGS_ART` enum('Bruttobetrag','Nettobetrag','Skontobetrag') collate latin1_german2_ci NOT NULL default 'Bruttobetrag',
  `VZWECK1` varchar(27) collate latin1_german2_ci NOT NULL default '',
  `VZWECK2` varchar(27) collate latin1_german2_ci NOT NULL default '',
  `VZWECK3` varchar(27) collate latin1_german2_ci NOT NULL default '',
  `BUCHUNGSTEXT` mediumtext collate latin1_german2_ci,
  `ZAHLUNGSART` enum('VOLL','TEIL') collate latin1_german2_ci NOT NULL default 'VOLL',
  `BEZUGSTAB` enum('MIETVERTRAG','RECHNUNG') collate latin1_german2_ci NOT NULL default 'MIETVERTRAG',
  `BEZUGS_ID` int(7) NOT NULL default '0',
  `AUSZUGSNR` varchar(10) collate latin1_german2_ci default NULL,
  `AUSZUGS_DATUM` date default NULL,
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`U_DAT`),
  KEY `DATUM` (`DATUM`),
  KEY `DTAUS_ID` (`DTAUS_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `UEBERWEISUNG`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `URLAUB`
-- 

CREATE TABLE `URLAUB` (
  `U_DAT` int(7) NOT NULL auto_increment,
  `BENUTZER_ID` int(7) NOT NULL default '0',
  `ANTRAG_D` date default NULL,
  `DATUM` date NOT NULL default '0000-00-00',
  `ANTEIL` decimal(2,1) NOT NULL default '0.0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`U_DAT`),
  KEY `BENUTZER_ID` (`BENUTZER_ID`),
  KEY `ANTEIL` (`ANTEIL`),
  KEY `DATUM` (`DATUM`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `URLAUB`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `URLAUB_EINST`
-- 

CREATE TABLE `URLAUB_EINST` (
  `UE_DAT` int(2) NOT NULL auto_increment,
  `DATUM` date NOT NULL default '0000-00-00',
  `ANTEIL` decimal(2,1) NOT NULL default '0.0',
  PRIMARY KEY  (`UE_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `URLAUB_EINST`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `VERPACKUNGS_E`
-- 

CREATE TABLE `VERPACKUNGS_E` (
  `V_ID` int(7) NOT NULL auto_increment,
  `V_EINHEIT` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `BEZEICHNUNG` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`V_ID`),
  KEY `V_EINHEIT` (`V_EINHEIT`),
  KEY `BEZEICHNUNG` (`BEZEICHNUNG`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `VERPACKUNGS_E`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `WARTUNGEN`
-- 

CREATE TABLE `WARTUNGEN` (
  `DAT` int(7) NOT NULL auto_increment,
  `GERAETE_ID` int(7) NOT NULL default '0',
  `PLAN_ID` int(7) NOT NULL default '0',
  `WARTUNGSDATUM` date default NULL,
  `BENUTZER_ID` int(11) NOT NULL default '0',
  `BEMERKUNG` mediumtext collate latin1_german2_ci NOT NULL,
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`DAT`),
  KEY `PLAN_ID` (`PLAN_ID`),
  KEY `GERAETE_ID` (`GERAETE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `WARTUNGEN`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `WARTUNGSPLAN`
-- 

CREATE TABLE `WARTUNGSPLAN` (
  `DAT` int(7) NOT NULL auto_increment,
  `PLAN_ID` int(7) NOT NULL default '0',
  `PLAN_BEZEICHNUNG` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `INTERVALL` int(3) NOT NULL default '0',
  `INTERVALL_PERIOD` enum('DAY','MONTH','YEAR') collate latin1_german2_ci NOT NULL default 'DAY',
  `GEWERK_ID` int(7) NOT NULL default '0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`DAT`),
  KEY `PLAN_ID` (`PLAN_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `WARTUNGSPLAN`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `WARTUNG_ZUWEISUNG`
-- 

CREATE TABLE `WARTUNG_ZUWEISUNG` (
  `DAT` int(7) NOT NULL auto_increment,
  `GERAETE_ID` int(7) NOT NULL default '0',
  `PLAN_ID` int(7) NOT NULL default '0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`DAT`),
  KEY `GERAETE_ID` (`GERAETE_ID`),
  KEY `PLAN_ID` (`PLAN_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `WARTUNG_ZUWEISUNG`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `WIRT_EINHEITEN`
-- 

CREATE TABLE `WIRT_EINHEITEN` (
  `W_DAT` int(7) NOT NULL auto_increment,
  `W_ID` int(7) NOT NULL default '0',
  `W_NAME` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`W_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `WIRT_EINHEITEN`
-- 

INSERT INTO `WIRT_EINHEITEN` (`W_DAT`, `W_ID`, `W_NAME`, `AKTUELL`) VALUES (1, 1, 'WE aus Musterobjekt', '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `WIRT_EIN_TAB`
-- 

CREATE TABLE `WIRT_EIN_TAB` (
  `WZ_DAT` int(7) NOT NULL auto_increment,
  `WZ_ID` int(7) NOT NULL default '0',
  `W_ID` int(7) NOT NULL default '0',
  `EINHEIT_ID` int(7) NOT NULL default '0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`WZ_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=3 ;

-- 
-- Daten für Tabelle `WIRT_EIN_TAB`
-- 

INSERT INTO `WIRT_EIN_TAB` (`WZ_DAT`, `WZ_ID`, `W_ID`, `EINHEIT_ID`, `AKTUELL`) VALUES (1, 1, 1, 1, '1'),
(2, 2, 1, 2, '1');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `W_GERAETE`
-- 

CREATE TABLE `W_GERAETE` (
  `DAT` int(7) NOT NULL auto_increment,
  `GERAETE_ID` int(7) NOT NULL default '0',
  `BEZEICHNUNG` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `HERSTELLER` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `BAUJAHR` varchar(4) collate latin1_german2_ci NOT NULL default '',
  `IM_EINSATZ` date NOT NULL default '0000-00-00',
  `KOSTENTRAEGER_TYP` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `KOSTENTRAEGER_ID` int(7) NOT NULL default '0',
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`DAT`),
  KEY `GERAETE_ID` (`GERAETE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `W_GERAETE`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `W_TERMINE`
-- 

CREATE TABLE `W_TERMINE` (
  `DAT` int(7) NOT NULL auto_increment,
  `PLAN_ID` int(7) NOT NULL default '0',
  `DATUM_FAELLIG` date NOT NULL default '0000-00-00',
  `TERMIN` datetime NOT NULL default '0000-00-00 00:00:00',
  `DAUER_MIN` char(3) collate latin1_german2_ci NOT NULL default '',
  `GERAETE_ID` int(7) NOT NULL default '0',
  `BENUTZER_ID` int(7) NOT NULL default '0',
  `ABGESAGT` enum('0','1') collate latin1_german2_ci default NULL,
  `ABGESAGT_AM` date default NULL,
  `ABGESAGT_VON` enum('Kunde','Wartungsfirma') collate latin1_german2_ci default NULL,
  `GRUND` varchar(200) collate latin1_german2_ci default '',
  `ABSAGE_AUFGENOMMEN` int(7) default NULL,
  `ABSAGE_RECHTZEITIG` enum('0','1') collate latin1_german2_ci default NULL,
  `AKTUELL` enum('0','1') collate latin1_german2_ci NOT NULL default '0',
  PRIMARY KEY  (`DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `W_TERMINE`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `ZUGRIFF_ERROR`
-- 

CREATE TABLE `ZUGRIFF_ERROR` (
  `Z_DAT` int(7) NOT NULL auto_increment,
  `BENUTZER_ID` int(7) NOT NULL default '0',
  `NAME` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `ZEIT` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `MODUL_NAME` varchar(50) collate latin1_german2_ci NOT NULL default '',
  `IP` varchar(15) collate latin1_german2_ci NOT NULL default '',
  `HOST` varchar(100) collate latin1_german2_ci NOT NULL default '',
  PRIMARY KEY  (`Z_DAT`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `ZUGRIFF_ERROR`
-- 

