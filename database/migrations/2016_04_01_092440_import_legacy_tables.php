<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class ImportLegacyTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(
            "CREATE TABLE IF NOT EXISTS `BAUSTELLEN` (
  `DAT` int(6) NOT NULL AUTO_INCREMENT,
  `KOSTENTRAEGER_TYP` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOSTENTRAEGER_ID` int(6) NOT NULL,
  `A_DATUM` date NOT NULL,
  `E_DATUM` date NOT NULL,
  `BESCHREIBUNG` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`,`KOSTENTRAEGER_TYP`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `BAUSTELLEN_EXT` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ID` int(7) NOT NULL,
  `BEZ` varchar(200) NOT NULL,
  `PARTNER_ID` int(7) NOT NULL,
  `AKTIV` enum('0','1') NOT NULL,
  `AKTUELL` enum('0','1') NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `BAU_BELEG` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `BELEG_NR` int(7) NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `BELEG2RG` (
  `DAT` int(11) NOT NULL AUTO_INCREMENT,
  `BELEG_NR` int(7) NOT NULL,
  `EMPF_P_ID` int(7) NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        if (!Schema::hasTable('BENUTZER') && !Schema::hasTable('persons')) {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS `BENUTZER` (
  `benutzer_id` int(7) NOT NULL AUTO_INCREMENT,
  `benutzername` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `passwort` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `STUNDENSATZ` decimal(10,2) NOT NULL,
  `GEB_DAT` date DEFAULT NULL,
  `GEWERK_ID` int(7) NOT NULL,
  `EINTRITT` date NOT NULL,
  `AUSTRITT` date NOT NULL,
  `URLAUB` int(2) DEFAULT NULL,
  `STUNDEN_PW` decimal(4,2) NOT NULL,
  KEY `benutzer_id` (`benutzer_id`),
  KEY `benutzername` (`benutzername`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=2 ;"
            );

            DB::insert(
                "INSERT INTO `BENUTZER` (`benutzer_id`, `benutzername`, `passwort`, `STUNDENSATZ`, `GEB_DAT`, `GEWERK_ID`, `EINTRITT`, `AUSTRITT`, `URLAUB`, `STUNDEN_PW`) VALUES
(1, 'admin', 'password', '50.00', '1978-12-12', 1, '2015-01-01', '0000-00-00', 30, '40.00');"
            );
        }

        if (!Schema::hasTable('BENUTZER_MODULE') && !Schema::hasTable('permissions')) {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS `BENUTZER_MODULE` (
  `BM_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `BM_ID` int(7) NOT NULL,
  `BENUTZER_ID` int(7) NOT NULL,
  `MODUL_NAME` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`BM_DAT`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=3 ;"
            );

            DB::insert(
                "INSERT INTO `BENUTZER_MODULE` (`BM_DAT`, `BM_ID`, `BENUTZER_ID`, `MODUL_NAME`, `AKTUELL`) VALUES
(2, 0, 1, '*', '1');"
            );
        }

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `BENUTZER_PARTNER` (
  `BP_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `BP_BENUTZER_ID` int(7) NOT NULL,
  `BP_PARTNER_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`BP_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `BK_ABRECHNUNGEN_KONTEN` (
  `BK_A_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `BK_A_ID` int(7) NOT NULL,
  `B_ID` int(7) NOT NULL,
  `BK_K_ID` int(7) NOT NULL,
  `KONTO` int(7) NOT NULL,
  `KONTO_BEZ` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `G_KOSTEN` decimal(10,2) NOT NULL,
  `G_KOSTEN_WO` decimal(10,2) NOT NULL,
  `G_KOSTEN_GE` decimal(10,2) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`BK_A_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `BK_ANPASSUNG` (
  `AN_DAT` int(6) NOT NULL AUTO_INCREMENT,
  `AN_ID` int(6) NOT NULL,
  `GRUND` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `FEST_BETRAG` decimal(10,2) NOT NULL,
  `KEY_ID` int(6) NOT NULL,
  `PROFIL_ID` int(6) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`AN_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `BK_BERECHNUNG_BUCHUNGEN` (
  `BK_BE_DAT` int(6) NOT NULL AUTO_INCREMENT,
  `BK_BE_ID` int(6) NOT NULL,
  `BUCHUNG_ID` int(6) NOT NULL,
  `BK_K_ID` int(6) NOT NULL,
  `BK_PROFIL_ID` int(6) NOT NULL,
  `KEY_ID` int(6) NOT NULL,
  `ANTEIL` decimal(7,4) NOT NULL,
  `KOSTENTRAEGER_TYP` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOSTENTRAEGER_ID` int(7) NOT NULL,
  `HNDL_BETRAG` decimal(10,3) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`BK_BE_DAT`),
  KEY `BUCHUNG_ID` (`BUCHUNG_ID`,`BK_K_ID`,`BK_PROFIL_ID`,`KOSTENTRAEGER_TYP`,`KOSTENTRAEGER_ID`,`AKTUELL`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `BK_EINZEL_ABRECHNUNGEN` (
  `BK_E_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `BK_E_ID` int(7) NOT NULL,
  `B_ID` int(7) NOT NULL,
  `EMPF` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ZEITRAUM` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MIETERNUMMER` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `EINHEIT_ID` int(7) NOT NULL,
  `EINHEIT_NAME` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `G_KOSTEN` decimal(10,2) NOT NULL,
  `G_HNDL` decimal(10,2) NOT NULL,
  `VORSCHUSS` decimal(10,2) NOT NULL,
  `SALDO` decimal(10,2) NOT NULL,
  `SALDO_ART` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`BK_E_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `BK_EINZEL_ABR_ZEILEN` (
  `BK_Z_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `BK_Z_ID` int(7) NOT NULL,
  `BK_E_ID` int(7) NOT NULL,
  `KONTO_ID` int(7) NOT NULL,
  `KONTO_BEZ` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `G_KOSTEN` decimal(10,2) NOT NULL,
  `G_HNDL` decimal(10,2) NOT NULL,
  `VERTEILER` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `IHRE_ME` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ANTEIL_HNDL` decimal(10,2) NOT NULL,
  `BETEILIGUNG` decimal(10,2) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`BK_Z_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        if (!Schema::hasTable('BK_GENERAL_KEYS')) {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS `BK_GENERAL_KEYS` (
  `GKEY_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `GKEY_ID` int(7) NOT NULL,
  `GKEY_NAME` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `G_VAR` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `E_VAR` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ME` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`GKEY_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
            );

            DB::insert(
                "INSERT INTO BK_GENERAL_KEYS (GKEY_ID, GKEY_NAME, G_VAR, E_VAR, ME, AKTUELL) VALUES
(1, 'm² je Einheit', 'g_einheit_qm', 'einheit_qm', 'm²', '1'),
(2, 'durch Anzahl Einheiten', 'g_anzahl_einheiten', 'anzahl_einheiten', 'ME', '1'),
(3, 'Miteigentumsanteile', 'g_mea', 'e_mea', 'MEA', '1'),
(4, 'Pauschal nach Verbrauch', 'g_verbrauch', 'e_verbrauch', '€', '1'),
(5, 'Nach Aufzugsprozent ', 'g_aufzug_prozent', 'e_aufzug_prozent', '%', '1') ;"
            );
        }

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `BK_KONTEN` (
  `BK_K_DAT` int(6) NOT NULL AUTO_INCREMENT,
  `BK_K_ID` int(6) NOT NULL,
  `KONTO` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KONTO_BEZ` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `BK_PROFIL_ID` int(6) NOT NULL,
  `GENKEY_ID` int(6) NOT NULL,
  `HNDL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`BK_K_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `BK_PROFILE` (
  `BK_DAT` int(6) NOT NULL AUTO_INCREMENT,
  `BK_ID` int(6) NOT NULL,
  `BEZEICHNUNG` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TYP` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TYP_ID` int(6) NOT NULL,
  `JAHR` decimal(4,0) NOT NULL,
  `BERECHNUNGS_DATUM` date NOT NULL,
  `VERRECHNUNGS_DATUM` date NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`BK_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        if (!Schema::hasTable('DETAIL')) {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS `DETAIL` (
  `DETAIL_DAT` int(11) NOT NULL AUTO_INCREMENT,
  `DETAIL_ID` int(11) NOT NULL,
  `DETAIL_NAME` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DETAIL_INHALT` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DETAIL_BEMERKUNG` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DETAIL_AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `DETAIL_ZUORDNUNG_TABELLE` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DETAIL_ZUORDNUNG_ID` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DETAIL_DAT`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=10 ;"
            );

            DB::insert(
                "INSERT INTO `DETAIL` (`DETAIL_DAT`, `DETAIL_ID`, `DETAIL_NAME`, `DETAIL_INHALT`, `DETAIL_BEMERKUNG`, `DETAIL_AKTUELL`, `DETAIL_ZUORDNUNG_TABELLE`, `DETAIL_ZUORDNUNG_ID`) VALUES
(1, 0, 'Geschlecht', 'männlich', 'admin-18.11.2015 12:55', '1', 'Person', '1'),
(2, 1, 'Telefon', '030 89 78 44 77', 'Stand 18.11.2015', '1', 'Person', '1'),
(3, 2, 'Email', 'software@hausverwaltung.de', 'Stand 18.11.2015', '1', 'Person', '1'),
(4, 3, 'Zustellanschrift', 'Max Mustermann\r\nc/o BerlusGmbH\r\nMustermannstrasse1\r\n<b>14055 Berlin</b>', '', '1', 'Person', '1'),
(5, 4, 'Telefon', '030 89 78 44 77', 'admin 18.11.2015 13:07:14', '1', 'Partner', '1'),
(6, 5, 'Fax', '030 89 78 44 79', 'admin 18.11.2015 13:07:14', '1', 'Partner', '1'),
(7, 6, 'Email', 'software@hausverwaltung.de', 'admin 18.11.2015 13:07:14', '1', 'Partner', '1');"
            );
        }

        if (!Schema::hasTable('DETAIL_KATEGORIEN')) {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS `DETAIL_KATEGORIEN` (
  `DETAIL_KAT_ID` int(6) NOT NULL AUTO_INCREMENT,
  `DETAIL_KAT_NAME` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DETAIL_KAT_KATEGORIE` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DETAIL_KAT_AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DETAIL_KAT_ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=174 ;"
            );

            DB::insert(
                "INSERT INTO `DETAIL_KATEGORIEN` (`DETAIL_KAT_ID`, `DETAIL_KAT_NAME`, `DETAIL_KAT_KATEGORIE`, `DETAIL_KAT_AKTUELL`) VALUES
(1, 'Heizungsart', 'Einheit', '1'),
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
(72, 'NB mit SH oder Bad und mit IWC', 'Einheit', '1'),
(73, 'Anschrift', 'Person', '1'),
(74, 'Email', 'Person', '1'),
(75, 'WEG-Anteile', 'Einheit', '1'),
(76, 'Gesamtanteile', 'Objekt', '1'),
(77, 'WEG-Bezeichnung', 'Objekt', '1'),
(78, 'Kautionshinweis', 'Mietvertrag', '1'),
(79, 'Hauswart-Tel.', 'Objekt', '1'),
(80, 'Zustellanschrift', 'Person', '1'),
(81, 'Tel.', 'Partner', '1'),
(82, 'Wohnlage', 'Partner', '1'),
(83, 'Bautyp', 'Einheit', '1'),
(84, 'Vermietung', 'Objekt', '1'),
(85, 'Optiert', 'Objekt', '1'),
(86, 'Bankverbindung', 'Person', '1'),
(87, 'Titel', 'Person', '1'),
(88, 'Telefon', 'Partner', '1'),
(89, 'Anschrift', 'Partner', '1'),
(90, 'Gesamtanteile', 'Wirtschaftseinheit', '1'),
(91, 'WEG-Fläche', 'Einheit', '1'),
(92, 'Alte Nr', 'Einheit', '1'),
(93, 'Heizfläche', 'Einheit', '1'),
(94, 'Souterrain', 'Einheit', '1'),
(95, 'Brennstoff', 'Objekt', '1'),
(96, 'Legionellenproben', 'Objekt', '1'),
(97, 'Fersehversorgung', 'Objekt', '1'),
(98, 'Untermieter:', 'Mietvertrag', '1'),
(99, 'Mit SH oder Bad mit IWC', 'Einheit', '1'),
(100, 'Hinweis_zu_Einheit', 'Einheit', '1'),
(101, 'Kleinreparaturklausel', 'Mietvertrag', '1'),
(102, 'WEG-KaltmieteINS', 'Einheit', '1'),
(103, 'Hinweis_zum_Objekt', 'Objekt', '1'),
(104, 'GLAEUBIGER_ID', 'GELD_KONTEN', '1'),
(105, 'Zustellanschrift', 'Mietvertrag', '1'),
(106, 'Passnr', 'Person', '1'),
(107, 'Stromzähler', 'Einheit', '1'),
(108, 'Nutzen-Lastenwechsel', 'Objekt', '1'),
(109, 'Verwaltungsübernahme', 'Objekt', '1'),
(110, 'Gaszähler', 'Einheit', '1'),
(111, 'Betreut durch', 'Person', '1'),
(112, 'Vorverwaltung:', 'Objekt', '1'),
(113, 'HKV-Nummer(n)', 'Objekt', '1'),
(114, '§557 BGB Index', 'Mietvertrag', '1'),
(115, 'Versicherung', 'Objekt', '1'),
(116, 'Mahnsperre', 'Mietvertrag', '1'),
(117, 'Rep-Freigabe', 'Partner', '1'),
(118, 'Fax', 'Partner', '1'),
(119, 'Telefon', 'Benutzer', '1'),
(120, 'Wasserzähler-Nr.:', 'Einheit', '1'),
(121, 'Abnahmetermin', 'Mietvertrag', '1'),
(122, 'Übergabetermin', 'Mietvertrag', '0'),
(123, 'Hinweis', 'Mietvertrag', '1'),
(124, 'Email', 'Partner', '1'),
(125, 'Abrechnungszeitraum', 'Objekt', '1'),
(126, 'Schornsteinfeger', 'Objekt', '1'),
(127, 'Schlüsseldienst', 'Objekt', '1'),
(128, 'WEG-Aufzugprozent', 'Einheit', '1'),
(129, 'E-Mail-Hinweis', 'Person', '1'),
(130, 'Müllhinweise', 'Objekt', '1'),
(131, 'Beschreibung', 'SEPA_UEBERWEISUNG', '1'),
(132, 'Heizungsfirma', 'Objekt', '1'),
(133, 'Zähler-Nr. HBL:', 'Objekt', '1'),
(134, 'allg. Hinweis  zum Keller', 'Objekt', '1'),
(135, 'Aufzugs-Firma:', 'Objekt', '1'),
(136, 'Versicherungsschaden', 'Einheit', '1'),
(137, 'Winterdienst', 'Objekt', '1'),
(138, 'Kabelversorger', 'Objekt', '1'),
(139, 'HK-Abrechnungsfirma', 'Objekt', '1'),
(140, 'Dachzugang', 'Objekt', '1'),
(141, 'Versicherungsschaden_Objekt', 'Objekt', '1'),
(142, 'ET-Vollmacht', 'Mietvertrag', '1'),
(143, 'RWA-Anlagen', 'Objekt', '1'),
(144, 'Stromzähler Aufzug', 'Objekt', '1'),
(145, 'Notrufnummer Aufzug', 'Objekt', '1'),
(146, 'FK - Fristlose Kündigung', 'Mietvertrag', '1'),
(147, 'RZK - Räumnungs- und Zahlungsklage', 'Mietvertrag', '1'),
(148, 'ET-Besichtigung', 'Mietvertrag', '1'),
(149, 'POA-Details', 'Einheit', '1'),
(150, 'Wasserzähler:', 'Objekt', '1'),
(151, 'Sicherheitseinbehalt Kaution', 'Mietvertrag', '1'),
(152, 'INS-Garantiemonate', 'Objekt', '1'),
(153, 'INS-Kundenbetreuer', 'Person', '1'),
(154, 'Mietminderung', 'Einheit', '1'),
(155, 'gew. Mietminderung', 'Mietvertrag', '1'),
(156, 'Keller', 'Einheit', '1'),
(157, 'Sanierung', 'Einheit', '1'),
(158, 'GVZ-Termin', 'Mietvertrag', '1'),
(159, 'MAV', 'Mietvertrag', '1'),
(160, 'GEH-Baujahr', 'Einheit', '1'),
(161, 'Ratenzahlungsvereinbarung', 'Mietvertrag', '1'),
(162, 'Liegenschafts-Nr.:', 'Haus', '1'),
(163, 'Liegenschaft:', 'Objekt', '1'),
(164, 'Kontaktdaten Grundstücksnachbar', 'Objekt', '1'),
(165, 'MS-Objekt-OST', 'Objekt', '1'),
(166, 'WEG seit:', 'Objekt', '1'),
(167, 'Hausreinigung', 'Objekt', '1'),
(168, 'Gerichtstermin', 'Mietvertrag', '1'),
(169, 'Schönheitsreparaturklausel', 'Mietvertrag', '1'),
(170, 'Hundehaltung', 'Mietvertrag', '1'),
(171, 'Gaszähler Haus', 'Objekt', '1'),
(172, 'Energieausweis vorhanden', 'Haus', '1'),
(173, 'Kabelnutzung ', 'Einheit', '1');"
            );
        }

        if (!Schema::hasTable('DETAIL_UNTERKATEGORIEN')) {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS `DETAIL_UNTERKATEGORIEN` (
  `UKAT_DAT` int(6) NOT NULL AUTO_INCREMENT,
  `KATEGORIE_ID` int(6) NOT NULL,
  `UNTERKATEGORIE_NAME` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`UKAT_DAT`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=69 ;"
            );

            DB::insert(
                "INSERT INTO `DETAIL_UNTERKATEGORIEN` (`UKAT_DAT`, `KATEGORIE_ID`, `UNTERKATEGORIE_NAME`, `AKTUELL`) VALUES
(1, 1, 'GEH', '1'),
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
(50, 69, 'JA', '1'),
(51, 85, 'JA', '1'),
(52, 85, 'NEIN', '1'),
(53, 1, 'keine', '1'),
(54, 30, 'ja', '1'),
(55, 30, 'nein', '1'),
(56, 99, 'JA', '1'),
(64, 165, 'JA', '1'),
(67, 172, 'JA', '1'),
(68, 172, 'NEIN', '1');"
            );
        }

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `EINHEIT` (
  `EINHEIT_DAT` int(11) NOT NULL AUTO_INCREMENT,
  `EINHEIT_ID` int(11) NOT NULL,
  `EINHEIT_QM` decimal(6,2) NOT NULL,
  `EINHEIT_LAGE` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `HAUS_ID` int(11) NOT NULL,
  `EINHEIT_AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `EINHEIT_KURZNAME` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TYP` enum('Wohnraum','Gewerbe','Stellplatz','Garage','Keller','Freiflaeche','Wohneigentum','Werbeflaeche') COLLATE utf8mb4_unicode_ci NOT NULL,
  UNIQUE KEY `EINHEIT_DAT` (`EINHEIT_DAT`),
  KEY `EINHEIT_ID` (`EINHEIT_ID`),
  KEY `HAUS_ID` (`HAUS_ID`),
  KEY `EINHEIT_KURZNAME` (`EINHEIT_KURZNAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `FENSTER_EINGEBAUT` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `R_BELEG_ID` int(7) NOT NULL,
  `POS` int(5) NOT NULL,
  `EINHEIT_ID` int(7) NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `FENSTER_LIEFERUNG` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `R_BELEG_ID` int(7) NOT NULL,
  `POS` int(4) NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `FOOTER_ZEILE` (
  `FOOTER_DAT` int(6) NOT NULL AUTO_INCREMENT,
  `FOOTER_ID` int(6) NOT NULL,
  `FOOTER_TYP` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `FOOTER_TYP_ID` int(6) NOT NULL,
  `ZAHLUNGSHINWEIS` mediumtext COLLATE utf8mb4_unicode_ci,
  `ZEILE1` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ZEILE2` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `HEADER` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`FOOTER_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        if (!Schema::hasTable('GELD_KONTEN')) {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS `GELD_KONTEN` (
  `KONTO_DAT` int(4) NOT NULL AUTO_INCREMENT,
  `KONTO_ID` int(4) NOT NULL,
  `BEZEICHNUNG` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `BEGUENSTIGTER` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KONTONUMMER` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `BLZ` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `IBAN` varchar(35) COLLATE utf8mb4_unicode_ci NOT NULL,
  `BIC` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `INSTITUT` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`KONTO_DAT`),
  KEY `KONTO_ID` (`KONTO_ID`),
  KEY `KONTONUMMER` (`KONTONUMMER`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=2 ;"
            );

            DB::insert(
                "INSERT INTO `GELD_KONTEN` (`KONTO_DAT`, `KONTO_ID`, `BEZEICHNUNG`, `BEGUENSTIGTER`, `KONTONUMMER`, `BLZ`, `IBAN`, `BIC`, `INSTITUT`, `AKTUELL`) VALUES
(1, 1, 'Mustermann GmbH Konto', 'Mustermann GmbH', '800101561', '10050000', 'DE10100500000800101561', 'BELADEBEXXX', 'Berliner Sparkasse', '1');"
            );
        }

        if (!Schema::hasTable('GELD_KONTEN_ZUWEISUNG')) {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS `GELD_KONTEN_ZUWEISUNG` (
  `ZUWEISUNG_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ZUWEISUNG_ID` int(7) NOT NULL,
  `KONTO_ID` int(4) NOT NULL,
  `KOSTENTRAEGER_TYP` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOSTENTRAEGER_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ZUWEISUNG_DAT`),
  KEY `KONTO_ID` (`KONTO_ID`),
  KEY `KOSTENTRAEGER_TYP` (`KOSTENTRAEGER_TYP`),
  KEY `KOSTENTRAEGER_ID` (`KOSTENTRAEGER_ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=2 ;"
            );

            DB::insert(
                "INSERT INTO `GELD_KONTEN_ZUWEISUNG` (`ZUWEISUNG_DAT`, `ZUWEISUNG_ID`, `KONTO_ID`, `KOSTENTRAEGER_TYP`, `KOSTENTRAEGER_ID`, `AKTUELL`) VALUES
(1, 1, 1, 'Partner', 1, '1');"
            );
        }

        if (!Schema::hasTable('GELD_KONTO_BUCHUNGEN')) {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS `GELD_KONTO_BUCHUNGEN` (
  `GELD_KONTO_BUCHUNGEN_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `GELD_KONTO_BUCHUNGEN_ID` int(7) NOT NULL,
  `G_BUCHUNGSNUMMER` int(6) NOT NULL,
  `KONTO_AUSZUGSNUMMER` int(7) NOT NULL,
  `ERFASS_NR` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `BETRAG` decimal(10,2) NOT NULL,
  `MWST_ANTEIL` decimal(10,2) NOT NULL DEFAULT '0.00',
  `VERWENDUNGSZWECK` mediumtext COLLATE utf8mb4_unicode_ci,
  `GELDKONTO_ID` int(7) NOT NULL,
  `KONTENRAHMEN_KONTO` int(6) NOT NULL,
  `DATUM` date NOT NULL,
  `KOSTENTRAEGER_TYP` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOSTENTRAEGER_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`GELD_KONTO_BUCHUNGEN_DAT`),
  KEY `KONTENRAHMEN_KONTO` (`KONTENRAHMEN_KONTO`),
  KEY `KOSTENTRAEGER_TYP` (`KOSTENTRAEGER_TYP`),
  KEY `KOSTENTRAEGER_ID` (`KOSTENTRAEGER_ID`),
  KEY `DATUM` (`DATUM`),
  KEY `GELDKONTO_ID` (`GELDKONTO_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
            );

            DB::statement(
                "ALTER TABLE `GELD_KONTO_BUCHUNGEN`
ADD INDEX `GELDKONTO_BUCHUNGEN_ID` (`GELD_KONTO_BUCHUNGEN_ID` ASC);"
            );
        }

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `GEO_ENTFERNUNG` (
  `DAT` int(8) NOT NULL AUTO_INCREMENT,
  `GEO_DAT_START` int(7) NOT NULL,
  `GEO_DAT_ZIEL` int(7) NOT NULL,
  `KM` varchar(10) NOT NULL,
  `FAHRZEIT` varchar(50) NOT NULL,
  `QUELLE` varchar(20) NOT NULL,
  `AKTUELL` enum('0','1') NOT NULL,
  PRIMARY KEY (`DAT`),
  KEY `GEO_DAT_START` (`GEO_DAT_START`),
  KEY `GEO_DAT_ZIEL` (`GEO_DAT_ZIEL`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `GEO_LON_LAT` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `STR` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NR` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `PLZ` decimal(7,0) NOT NULL,
  `ORT` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `LON` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `LAT` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `QUELLE` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`),
  KEY `STR` (`STR`),
  KEY `NR` (`NR`),
  KEY `PLZ` (`PLZ`),
  KEY `ORT` (`ORT`),
  KEY `AKTUELL` (`AKTUELL`),
  KEY `QUELLE` (`QUELLE`),
  KEY `LAT` (`LAT`),
  KEY `LON` (`LON`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `GEO_TERMINE` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `BENUTZER_ID` int(7) NOT NULL,
  `DATUM` date NOT NULL,
  `VON` time NOT NULL,
  `BIS` time NOT NULL,
  `TEXT` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `GERAETE_ID` int(7) NOT NULL,
  `HINWEIS` varchar(200) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `EINGEGEBEN_AM` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `EINGEGEBEN_VON` int(7) NOT NULL,
  `ABGESAGT_AM` timestamp NULL DEFAULT NULL,
  `ABGESAGT_VON` int(7) DEFAULT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`),
  KEY `BENUTZER_ID` (`BENUTZER_ID`),
  KEY `DATUM` (`DATUM`),
  KEY `GERAETE_ID` (`GERAETE_ID`),
  KEY `VON` (`VON`),
  KEY `BIS` (`BIS`),
  KEY `AKTUELL` (`AKTUELL`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        if (!Schema::hasTable('GEWERKE') && !Schema::hasTable('job_titles')) {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS `GEWERKE` (
  `G_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `G_ID` int(7) NOT NULL,
  `BEZEICHNUNG` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`G_DAT`),
  KEY `G_ID` (`G_ID`),
  KEY `BEZEICHNUNG` (`BEZEICHNUNG`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=9 ;
"
            );

            DB::insert(
                "INSERT INTO `GEWERKE` (`G_DAT`, `G_ID`, `BEZEICHNUNG`, `AKTUELL`) VALUES
(1, 1, 'Elektro', '1'),
(2, 2, 'Sanitär', '1'),
(3, 3, 'Hausmeister', '1'),
(4, 4, 'Maler', '1'),
(5, 5, 'Tischler', '1'),
(6, 6, 'EDV', '1'),
(7, 7, 'Fliesenleger', '1'),
(8, 8, 'Maurer', '1');"
            );
        }

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `HAUS` (
  `HAUS_DAT` int(11) NOT NULL AUTO_INCREMENT,
  `HAUS_ID` int(11) NOT NULL,
  `HAUS_STRASSE` varchar(200) CHARACTER SET utf8 NOT NULL,
  `HAUS_NUMMER` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `HAUS_STADT` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `HAUS_PLZ` int(11) NOT NULL,
  `HAUS_QM` decimal(10,2) NOT NULL,
  `HAUS_AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `OBJEKT_ID` int(11) NOT NULL,
  UNIQUE KEY `HAUS_DAT` (`HAUS_DAT`),
  KEY `HAUS_ID` (`HAUS_ID`),
  KEY `OBJEKT_ID` (`OBJEKT_ID`),
  KEY `HAUS_STRASSE` (`HAUS_STRASSE`),
  KEY `HAUS_NUMMER` (`HAUS_NUMMER`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement("CREATE TABLE IF NOT EXISTS `KAUTION_DATEN` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `MV_ID` int(7) NOT NULL,
  `FELD` varchar(50) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL,
  `WERT` varchar(100) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL,
  `AKTUELL` enum('0','1') CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        DB::statement("CREATE TABLE IF NOT EXISTS `KAUTION_FELD` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `FELD` varchar(50) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL,
  `AKTUELL` enum('0','1') CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `KASSEN` (
  `KASSEN_DAT` int(4) NOT NULL AUTO_INCREMENT,
  `KASSEN_ID` int(4) NOT NULL,
  `KASSEN_NAME` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KASSEN_VERWALTER` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`KASSEN_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `KASSEN_BUCH` (
  `KASSEN_BUCH_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `KASSEN_BUCH_ID` int(7) NOT NULL,
  `KASSEN_ID` int(4) NOT NULL,
  `ZAHLUNGSTYP` enum('Einnahmen','Ausgaben') COLLATE utf8mb4_unicode_ci NOT NULL,
  `BETRAG` decimal(10,2) NOT NULL,
  `DATUM` date NOT NULL,
  `BELEG_TEXT` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOSTENTRAEGER_TYP` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `KOSTENTRAEGER_ID` int(6) DEFAULT NULL,
  PRIMARY KEY (`KASSEN_BUCH_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `KASSEN_PARTNER` (
  `DAT` int(6) NOT NULL AUTO_INCREMENT,
  `KASSEN_ID` int(6) NOT NULL,
  `PARTNER_ID` int(6) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        if (!Schema::hasTable('KONTENRAHMEN')) {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS `KONTENRAHMEN` (
  `KONTENRAHMEN_DAT` int(6) NOT NULL AUTO_INCREMENT,
  `KONTENRAHMEN_ID` int(6) NOT NULL,
  `NAME` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`KONTENRAHMEN_DAT`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=26 ;"
            );

            DB::insert(
                "INSERT INTO `KONTENRAHMEN` (`KONTENRAHMEN_DAT`, `KONTENRAHMEN_ID`, `NAME`, `AKTUELL`) VALUES
(1, 1, 'Kontenrahmen der GmbH', '1'),
(2, 2, 'Standardkontenrahmen Objekte', '1'),
(3, 3, 'Kontenrahmen der Hausverwaltung', '1'),
(4, 4, 'Kautionskonto', '1'),
(7, 5, 'Musterkontenrahmen', '1'),
(8, 6, 'WEG Kontenrahmen', '1'),
(9, 7, 'Instandhaltungsrücklagen', '1');"
            );
        }

        if (!Schema::hasTable('KONTENRAHMEN_GRUPPEN')) {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS `KONTENRAHMEN_GRUPPEN` (
  `KONTENRAHMEN_GRUPPEN_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `KONTENRAHMEN_GRUPPEN_ID` int(7) NOT NULL,
  `BEZEICHNUNG` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`KONTENRAHMEN_GRUPPEN_DAT`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=29 ;"
            );

            DB::insert(
                "INSERT INTO `KONTENRAHMEN_GRUPPEN` (`KONTENRAHMEN_GRUPPEN_DAT`, `KONTENRAHMEN_GRUPPEN_ID`, `BEZEICHNUNG`, `AKTUELL`) VALUES
(1, 1, 'Reparaturen', '1'),
(2, 2, 'Umlagefähige Kosten', '1'),
(3, 3, 'Umlagefähige Heizungskosten', '1'),
(4, 4, 'Nicht umlagefähige Kosten', '1'),
(5, 5, 'Hauswartsbürokosten', '1'),
(6, 6, 'Minderungen', '1'),
(7, 7, 'Rechsbehelfskosten', '1'),
(8, 8, 'Geldliche Mittel', '1'),
(9, 9, 'Kosten Girokonto', '1'),
(10, 10, 'Darlehen', '1'),
(11, 11, 'Festgeld / Tagesgeld', '1'),
(12, 12, 'sonstige Einnahmen', '1'),
(13, 13, 'sonstige Einnahmen + Ausgaben', '1'),
(15, 0, 'Keine Gruppe', '1'),
(17, 14, 'Sivac gruppe', '1'),
(18, 15, 'Betriebskosten', '1'),
(19, 16, 'Sonstige Kosten', '1'),
(20, 17, 'Kosten Waschküche', '1'),
(21, 18, 'Instandhaltungskosten', '1'),
(22, 19, 'Zuführung zur Instandhaltungsrückstellungen', '1'),
(23, 20, 'Verwalterhonorar', '1'),
(24, 21, 'Belastungen / Erstattungen', '1'),
(25, 22, 'Kosten Heizung', '1'),
(26, 23, 'Kosten Aufzug', '1'),
(27, 24, 'Einnahmen Hausgeld', '1'),
(28, 25, 'Mieteinnahmen', '1');"
            );
        }

        if (!Schema::hasTable('KONTENRAHMEN_KONTEN')) {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS `KONTENRAHMEN_KONTEN` (
  `KONTENRAHMEN_KONTEN_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `KONTENRAHMEN_KONTEN_ID` int(7) NOT NULL,
  `KONTO` int(6) NOT NULL,
  `BEZEICHNUNG` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `GRUPPE` int(7) NOT NULL,
  `KONTO_ART` int(7) NOT NULL,
  `KONTENRAHMEN_ID` int(6) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`KONTENRAHMEN_KONTEN_DAT`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1096 ;"
            );

            DB::insert(
                "INSERT INTO `KONTENRAHMEN_KONTEN` (`KONTENRAHMEN_KONTEN_DAT`, `KONTENRAHMEN_KONTEN_ID`, `KONTO`, `BEZEICHNUNG`, `GRUPPE`, `KONTO_ART`, `KONTENRAHMEN_ID`, `AKTUELL`) VALUES
(1, 1, 1020, 'Reparaturkosten Objekt', 1, 1, 2, '1'),
(2, 2, 1023, 'Reparaturkosten Einheit', 1, 1, 2, '1'),
(3, 3, 1030, 'Versicherungsschäden + Versicherungserstattungen', 1, 1, 2, '0'),
(4, 4, 1040, 'Baunebenkosten', 1, 1, 2, '1'),
(5, 5, 1041, 'Grosse Instandhaltung', 1, 1, 2, '0'),
(6, 6, 2000, 'Grundsteuer', 2, 2, 2, '1'),
(7, 7, 2020, 'Versicherungen', 2, 2, 2, '1'),
(8, 8, 2040, 'Be- und Entwässerungskosten', 2, 2, 2, '0'),
(9, 9, 2060, 'Strom Hausbeleuchtung', 2, 2, 2, '1'),
(10, 10, 2080, 'Müllbeseitigung / Strassenreinigung/ Entrümpelung', 2, 2, 2, '0'),
(11, 11, 2110, 'Schornsteinfegerkehrgebühr', 2, 2, 2, '1'),
(12, 12, 2120, 'Reinigungsservice / Schädlingsbekämpfung', 2, 2, 2, '0'),
(13, 13, 2121, 'Reinigungsmittel', 2, 2, 2, '1'),
(14, 14, 2140, 'Hauswart - Nettolohn', 2, 2, 2, '1'),
(15, 15, 2141, 'Hauswart - SV-Abgaben', 2, 2, 2, '1'),
(16, 16, 2143, 'Hauswart - Lohnsteuer', 2, 2, 2, '1'),
(17, 17, 2145, 'Lohnfortzahlung Hauswart', 2, 2, 2, '1'),
(18, 18, 2146, 'Hauswart - Berufsgenossenschaft', 2, 2, 2, '1'),
(19, 19, 2200, 'Eis- und Schneebeseitigung Lohn', 2, 2, 2, '1'),
(20, 20, 2300, 'Gartenpflege Lohn', 2, 2, 2, '1'),
(21, 21, 2400, 'Kabelfernsehen', 2, 2, 2, '1'),
(22, 22, 2900, 'sonstige Betriebskosten Lohn', 2, 2, 2, '1'),
(23, 23, 3000, 'Heizungswartung / Reinigung Lohn', 3, 3, 2, '1'),
(24, 24, 3001, 'TÜV / Heizungsanlage', 3, 3, 2, '1'),
(25, 25, 3002, 'Brennstoffe', 3, 3, 2, '1'),
(26, 26, 3003, 'Heizkostenabrechnungsgebühren', 3, 3, 2, '1'),
(27, 27, 4000, 'Objekthauswart - Netto', 4, 1, 2, '1'),
(28, 28, 4001, 'Objekthauswart - SV-Abgaben', 4, 1, 2, '1'),
(29, 29, 4003, 'Objekthauswart - Lohnsteuer', 4, 1, 2, '1'),
(30, 30, 4005, 'Objekhauswart - Lohnfortzahlung', 4, 1, 2, '1'),
(31, 31, 4006, 'Objekthauswart - Berufsgenossenschaft', 4, 1, 2, '1'),
(32, 32, 4020, 'Hauswartsbüro Mietzins', 5, 1, 2, '1'),
(33, 33, 4021, 'Hauswartsbüro Strom', 5, 1, 2, '1'),
(34, 34, 4022, 'Hauswartsbüro Heizung', 5, 1, 2, '1'),
(35, 35, 4023, 'Hauswartsbüro Telefon', 5, 1, 2, '1'),
(36, 36, 4024, 'Hauswartsbüro Bürobedarf', 5, 1, 2, '1'),
(37, 37, 4025, 'Hauswartsbüro Benzin', 5, 1, 2, '1'),
(38, 38, 4026, 'Hauswartsbüro - sonstige Kosten', 5, 1, 2, '1'),
(39, 39, 4030, 'Schädlingsbekämpfung Haus', 0, 1, 2, '1'),
(40, 40, 4031, 'Schädlingsbekämpfung Mieter', 0, 1, 2, '1'),
(41, 41, 4160, 'Verwaltergeb. / Honorare', 0, 1, 2, '0'),
(42, 42, 4170, 'Mietfreiheit wegen Renovierung', 6, 1, 2, '1'),
(43, 43, 4180, 'Gewährte Minderungen', 6, 1, 2, '0'),
(44, 44, 4190, 'Uneibringliche Forderungen', 6, 1, 2, '0'),
(45, 45, 4191, 'Verr. Ktop Korr Umlagenabrechnung', 0, 7, 2, '0'),
(46, 46, 4240, 'Inserate', 0, 1, 2, '0'),
(47, 47, 4280, 'Gerichtskostenvorschuß', 7, 1, 2, '0'),
(48, 48, 4281, 'Anwaltkosten / EMA', 7, 1, 2, '0'),
(49, 49, 4282, 'Gerichtsvollzieherkosten', 7, 1, 2, '1'),
(50, 50, 5010, 'Eigentümereinlage', 8, 4, 2, '1'),
(51, 51, 5020, 'Eigentümerentnahme', 8, 5, 2, '1'),
(52, 52, 5060, 'Kontoführungsgebühren', 9, 5, 2, '0'),
(53, 53, 5061, 'Habenzinsen Girokonto', 5, 4, 2, '1'),
(54, 54, 5062, 'Sollzinsen Girokonto', 9, 5, 2, '1'),
(55, 55, 5063, 'Kapitalertragsteuer', 9, 5, 2, '1'),
(56, 56, 5064, 'Solidaritätszuschlag', 9, 5, 2, '1'),
(57, 57, 5080, 'Aufnahme von Darlehen', 10, 4, 2, '1'),
(58, 58, 5081, 'Tilgung von Darlehen', 10, 5, 2, '1'),
(59, 59, 5082, 'Zinsaufwendung Darlehen', 10, 5, 2, '1'),
(60, 60, 5083, 'Bearbeitungsgebühren Darlehen', 10, 5, 2, '1'),
(61, 61, 5084, 'Darlehen an Lager', 10, 5, 2, '1'),
(62, 62, 5090, 'Anlage Festgeld', 11, 5, 2, '1'),
(63, 63, 5091, 'Rückruf Festgeld', 11, 4, 2, '1'),
(64, 64, 5092, 'Zinsen für Festgeld', 11, 4, 2, '1'),
(65, 65, 5093, 'Kapitalertragssteuer auf Zinsert.', 11, 1, 2, '1'),
(66, 66, 5094, 'Solidaritätszuschlag auf Zinsert.', 11, 1, 2, '1'),
(67, 67, 5100, 'Einnahme Miete ohne Zuordnung', 12, 6, 2, '1'),
(68, 68, 5101, 'Einnahme Mietsicherheiten', 12, 6, 2, '1'),
(69, 69, 5200, 'Sonstige Einnahmen', 13, 4, 2, '1'),
(70, 70, 5201, 'Vereinnahmte Mahngebühren', 13, 4, 2, '1'),
(71, 71, 52021, 'Sonstige Ausgaben', 13, 5, 2, '0'),
(72, 72, 5203, 'Mahnkosten, LS Geb.', 13, 5, 2, '1'),
(73, 73, 5204, 'Zinseinnahmen', 13, 4, 2, '1'),
(74, 74, 6000, 'Saldovortrag Vorverwaltung Einheiten', 0, 0, 2, '1'),
(75, 75, 7000, 'Verbindlichkeiten', 0, 0, 2, '1'),
(76, 76, 8000, 'Forderungen', 0, 0, 2, '0'),
(79, 79, 1200, 'Bank', 0, 0, 2, '1'),
(80, 80, 1200, 'Bank', 0, 0, 1, '1'),
(81, 81, 80001, 'Mieteinnahmen', 0, 0, 1, '0'),
(82, 82, 5202, 'Sonstige Ausgaben (Steuerbüro)', 13, 5, 2, '1'),
(83, 81, 80001, 'Mieteinnahmen', 0, 0, 2, '0'),
(84, 83, 5210, 'Kasse', 13, 4, 2, '1'),
(85, 84, 5210, 'Kasse', 13, 4, 1, '0'),
(86, 85, 4192, 'Umlagenabr. Altmieter', 13, 4, 1, '0'),
(87, 86, 4192, 'Umlagenabr. Altmieter', 13, 4, 2, '0'),
(88, 87, 1000, 'Umsatzsteuer', 0, 0, 3, '1'),
(89, 88, 2000, 'Kasse', 0, 0, 3, '1'),
(90, 89, 3000, 'sonst. Kosten', 0, 0, 3, '1'),
(91, 90, 3001, 'Bewirtung', 0, 0, 3, '1'),
(92, 91, 3002, 'Präsente', 0, 0, 3, '1'),
(93, 92, 3003, 'Büroeinrichtungen', 0, 0, 3, '1'),
(94, 93, 3004, 'Büromaterial', 0, 0, 3, '1'),
(95, 94, 3005, 'Büro', 0, 0, 3, '1'),
(96, 95, 3006, 'Büro sonst. Kosten', 0, 0, 3, '1'),
(97, 96, 3007, 'Büro Miete', 0, 0, 3, '1'),
(98, 97, 4000, 'Löhne/Gehälter/Aushilfslohn', 0, 0, 3, '1'),
(99, 98, 4001, 'Sozialabgaben', 0, 0, 3, '1'),
(100, 99, 4003, 'Lohnsteuer', 0, 0, 3, '1'),
(101, 100, 4005, 'Lohnfortzahlung', 0, 0, 3, '1'),
(102, 101, 4006, 'Berufsgenossenschaft', 0, 0, 3, '1'),
(103, 102, 4007, 'Direktversicherung', 0, 0, 3, '1'),
(104, 103, 5020, 'Eigentümerentnahme', 0, 0, 3, '1'),
(105, 104, 5060, 'Nebenkosten des Geldverkehrs', 0, 0, 3, '1'),
(106, 105, 5070, 'Buchführungskosten', 0, 0, 3, '1'),
(107, 106, 5100, 'Fremdgeld', 0, 0, 3, '1'),
(108, 107, 8000, 'Verwaltergebühren Wehrheim/Pfeiffer', 0, 0, 3, '1'),
(109, 108, 8001, 'Mieter/Fremde', 0, 0, 3, '1'),
(110, 108, 8002, 'Verwaltungsaufwand Berlus GmbH', 0, 0, 3, '1'),
(111, 0, 1000, 'Umsatzsteuer', 0, 0, 1, '1'),
(112, 0, 2000, 'Kasse', 0, 0, 1, '1'),
(113, 0, 3000, 'sonst. Kosten', 0, 0, 1, '0'),
(114, 0, 3001, 'KFZ Kosten', 0, 0, 1, '0'),
(115, 0, 3002, 'KFZ Kosten', 0, 0, 1, '0'),
(116, 0, 3003, 'Büroeinrichtungen', 0, 0, 1, '1'),
(117, 0, 3004, 'Büromaterial', 0, 0, 1, '1'),
(118, 0, 3005, 'Internet Service', 0, 0, 1, '0'),
(119, 0, 7000, 'Materialrechnungen', 0, 0, 1, '1'),
(120, 0, 7001, 'Sonst. Rechnungen', 0, 0, 1, '0'),
(121, 0, 4000, 'Löhne/Gehälter', 0, 0, 1, '1'),
(122, 0, 4001, 'Sozialabgaben', 0, 0, 1, '1'),
(123, 0, 4003, 'Lohnsteuer', 0, 0, 1, '1'),
(124, 0, 4005, 'Lohnfortzahlung', 0, 0, 1, '1'),
(125, 0, 4006, 'Berufsgenossenschaft', 0, 0, 1, '1'),
(126, 0, 4007, 'Direktversicherung', 0, 0, 1, '0'),
(127, 0, 5071, 'Verwaltungsaufwendungen', 0, 0, 1, '1'),
(128, 0, 5060, 'Nebenkosten des Geldverkehrs', 0, 0, 1, '0'),
(129, 0, 5070, 'Buchführungskosten', 0, 0, 1, '1'),
(130, 0, 5100, 'Fremdgeld', 0, 0, 1, '1'),
(131, 0, 8000, 'Wehrheim/Pfeiffer', 0, 0, 1, '0'),
(132, 0, 8001, 'Mieter/Fremde', 0, 0, 1, '0'),
(133, 0, 8002, 'Berlus HV', 0, 0, 1, '0'),
(134, 110, 5081, 'Tilgung von Darlehen Bank', 0, 0, 1, '1'),
(135, 11, 5082, 'Zinsaufwendungen Darlehen Bank', 0, 0, 1, '1'),
(136, 112, 5083, 'Bearbeitungsgebühr Darlehen Bank', 0, 0, 1, '1'),
(137, 113, 5085, 'Tilgung von Darlehen ET', 0, 0, 1, '1'),
(138, 114, 5086, 'Zinsaufwendungen Darlehen ET', 0, 0, 1, '1'),
(139, 115, 5087, 'Bearbeitungsgebühr Darlehen ET', 0, 0, 1, '1'),
(140, 80, 1200, 'Bank', 0, 0, 3, '1'),
(141, 116, 6000, 'Lager', 0, 0, 1, '1'),
(142, 117, 1001, 'Gewerbesteuer', 0, 0, 1, '1'),
(143, 118, 2201, 'Eis- und Schneebeseitigung Material', 0, 0, 2, '0'),
(144, 119, 2301, 'Gartenpflege Material', 0, 0, 2, '0'),
(145, 120, 2901, 'sonst. Betriebskosten Material', 0, 0, 2, '0'),
(146, 121, 30011, 'Heizungswartung / Reinigung Material', 0, 0, 2, '1'),
(147, 122, 1000, 'Kautionszahler Mieter', 0, 0, 4, '1'),
(148, 123, 1001, 'Kautionszahler Amt', 0, 0, 4, '1'),
(149, 124, 2000, 'Auszahlung Kaution', 0, 0, 4, '1'),
(150, 125, 2001, 'Auszahlung Kautionszinsen', 0, 0, 4, '1'),
(151, 126, 2002, 'Kapitalertragsteuer', 0, 0, 4, '1'),
(152, 127, 2003, 'Solidaritätsbeitrag', 0, 0, 4, '1'),
(153, 128, 2100, 'Kaution zur Verrechnung', 0, 0, 4, '1'),
(154, 129, 5060, 'Nebenkosten des Geldverkehrs', 0, 0, 4, '1'),
(155, 130, 5100, 'Fremdgeld', 0, 0, 4, '1'),
(156, 131, 6000, 'Rundungsdifferenzen', 0, 0, 4, '1'),
(157, 132, 1001, 'Gewerbesteuer', 0, 0, 3, '1'),
(158, 133, 1002, 'Einkommensteuer', 0, 0, 3, '1'),
(159, 134, 1003, 'Kirchensteuer', 0, 0, 3, '1'),
(160, 135, 1004, 'Solidaritätszuschlag', 0, 0, 3, '1'),
(161, 136, 2004, 'Habenzinsen', 0, 0, 4, '1'),
(162, 138, 2500, 'Technischer Hausservice', 2, 2, 2, '1'),
(163, 139, 1050, 'Hauswart-Plus Service', 0, 1, 2, '1'),
(164, 140, 12345, 'Testkonto', 10, 5, 5, '1'),
(165, 141, 2000, 'Mieteinnahmen', 0, 4, 5, '0'),
(166, 142, 20004, 'Mieteinnahmen2', 11, 6, 5, '1'),
(167, 143, 15411, 'Testkonto 22', 9, 3, 5, '0'),
(168, 144, 15411, 'Testkonto 22', 8, 7, 5, '1'),
(253, 181, 6030, 'Einnahmen aus Hausgeld f. IHR', 24, 4, 7, '1'),
(252, 180, 4193, 'Guthaben/Nachzahlung Umlagenabrechnung', 0, 7, 2, '1'),
(251, 179, 5080, 'Aufnahme von Darlehen', 8, 4, 1, '1'),
(250, 178, 2080, 'BSR/brs,Strassenreinigung,Müllabfuhr', 2, 5, 6, '0'),
(249, 177, 9001, 'Materialentnahme Lager ,LSW und LEW', 0, 4, 2, '1'),
(248, 176, 9000, 'Materialrechnungen Lager', 0, 1, 2, '1'),
(247, 175, 3006, 'Bewirtung', 0, 0, 1, '1'),
(246, 174, 3006, 'Bewirtung', 21, 5, 1, '0'),
(245, 173, 6099, 'Hausgeldeinnahmen Vorverwaltung', 24, 4, 6, '0'),
(244, 172, 2041, 'Niederschlagswasser', 4, 5, 6, '0'),
(243, 171, 10001, 'Sonstige Einnahmen', 12, 4, 6, '0'),
(242, 170, 1022, 'Laufende Instandhaltung', 4, 5, 6, '1'),
(241, 169, 5055, 'Zuführung zur Instandhaltungsrücklage', 4, 6, 6, '0'),
(240, 168, 10000, 'Sonstige Ausgaben', 4, 5, 6, '1'),
(239, 167, 1021, 'Hauskosten Lohn', 4, 5, 6, '1'),
(238, 166, 1020, 'Hauskosten Material', 4, 5, 6, '1'),
(237, 165, 8003, 'Einnahmen aus Verwaltergebühren', 12, 4, 1, '1'),
(236, 164, 2400, 'Heizkosten', 2, 5, 6, '1'),
(235, 163, 2300, 'Müllabfuhr', 2, 5, 6, '0'),
(234, 162, 6030, 'Einnahmen aus Hausgeld für IHR', 24, 4, 6, '1'),
(233, 161, 6020, 'Einnahmen aus Hausgeld für Kosten', 24, 4, 6, '1'),
(232, 160, 6000, 'Hausgeldeinnahmen', 24, 6, 6, '1'),
(231, 159, 6010, 'Einnahmen aus Hausgeld für Heizung', 24, 4, 6, '1'),
(230, 158, 6000, 'Hausgeldeinnahmen', 24, 4, 6, '0'),
(229, 157, 3000, 'Verwalterhonorar', 4, 5, 6, '1'),
(228, 156, 2033, 'Aufzug - Hauptprüfung', 2, 5, 6, '1'),
(227, 155, 2032, 'Aufzug - Betriebsstrom', 2, 5, 6, '1'),
(226, 154, 2031, 'Aufzug - Telefon', 2, 5, 6, '1'),
(225, 153, 2030, 'Aufzug - Wartung', 2, 5, 6, '1'),
(224, 152, 2120, 'Hausreinigung', 2, 5, 6, '1'),
(223, 151, 2060, 'Strom Hausbeleuchtung', 2, 5, 6, '1'),
(222, 150, 2020, 'Versicherungen', 2, 5, 6, '1'),
(221, 149, 2080, 'Strassenreinigung', 2, 5, 6, '0'),
(220, 148, 5060, 'Kontoführungsgebühren', 4, 5, 6, '1'),
(219, 147, 2201, 'Eis- und Schneebeseitigung Material', 2, 5, 6, '1'),
(218, 146, 2200, 'Eis- und Schneebeseitigung Lohn', 2, 5, 6, '1'),
(217, 145, 2040, 'Trinkwasser', 2, 5, 6, '1'),
(254, 182, 6050, 'Übernahme aus Vorverwaltung', 24, 4, 7, '1'),
(255, 183, 6040, 'Zuführung zur Instandhaltungsrücklage', 4, 6, 6, '0'),
(256, 184, 2300, 'Müllabfuhr', 2, 5, 6, '0'),
(257, 185, 2080, 'Müllabfuhr', 2, 5, 6, '1'),
(258, 186, 17000, 'Einnahmen (Nachzahlungen) aus HGA', 21, 4, 6, '1'),
(259, 187, 18000, 'Ausgaben (Guthaben) aus der HGA', 21, 5, 6, '1'),
(260, 188, 5063, 'Kapitalertragsteuer', 0, 5, 7, '1'),
(261, 189, 5064, 'Solidaritätszuschlag', 0, 5, 7, '1'),
(262, 190, 5061, 'Habenzinsen', 0, 4, 7, '0'),
(263, 191, 6040, 'Zuführung zur Instandhaltungsrücklage', 19, 6, 6, '1'),
(264, 192, 2300, 'Hausmüll / Biogut / Straßenreinigung / Schließentg', 2, 5, 6, '1'),
(265, 193, 1020, 'Reparaturen / Renovierungen', 4, 5, 7, '1'),
(266, 194, 5061, 'Zinsen aus Rücklagen', 19, 4, 7, '1'),
(267, 195, 1009, 'Einfuhrumsatzsteuer', 0, 5, 1, '0'),
(268, 196, 1009, 'Einfuhrumsatzsteuer', 0, 0, 1, '1'),
(269, 197, 5001, 'Gewinnausschüttung', 0, 0, 1, '1'),
(270, 198, 1003, 'Kapitalertragsteuer', 0, 0, 1, '1'),
(271, 199, 1004, 'ev. Kirchensteuer', 0, 0, 1, '0'),
(272, 200, 1005, 'Solidaritätszuschlag', 0, 0, 1, '1'),
(273, 201, 52022, 'Baufinanzierung', 0, 5, 2, '1'),
(274, 202, 80014, 'vereinnahmte Mahngebühren', 0, 4, 1, '0'),
(275, 203, 5100, 'Einnahmen / Ausgaben ohne Zuordnung', 0, 6, 6, '1'),
(276, 204, 5100, 'Einnahmen / Ausgaben ohne Zuordnung', 0, 6, 7, '1'),
(277, 205, 6050, 'Hausgeldabrechnungen', 21, 7, 6, '1'),
(278, 206, 2041, 'Niederschlagswasser', 15, 1, 2, '0'),
(279, 207, 2041, 'Niederschlagswasser', 2, 2, 2, '1'),
(306, 234, 3004, 'Heiz. und WW-Nebenkosten', 3, 3, 2, '1'),
(307, 235, 2042, 'Kaltwasser-Nebenkosten', 15, 2, 2, '0'),
(308, 236, 2042, 'Kaltwasser-Nebenkosten', 2, 2, 2, '1'),
(309, 237, 30021, 'KFZ-Kosten B US', 0, 1, 1, '0'),
(310, 238, 5088, 'Mitarbeiterdarlehen', 0, 1, 1, '0'),
(311, 239, 5088, 'Mitarbeiterdarlehen', 0, 0, 1, '0'),
(312, 240, 5089, 'Tilgung Mitarbeiterdarlehen', 0, 0, 1, '0'),
(313, 241, 5090, 'Zinsen Mitarbeiterdarlehen', 0, 0, 1, '0'),
(314, 242, 6099, 'Hausgeldeinnahmen Vorverwaltung', 12, 4, 6, '1'),
(315, 243, 30021, 'KFZ-Kosten B US', 0, 0, 1, '0'),
(316, 244, 3001, 'KFZ Kosten (Mercedes)', 0, 0, 1, '0'),
(317, 245, 3999, 'Maschinen und hochwertiges Werkzeug', 0, 0, 1, '0'),
(318, 246, 6999, 'Maschinen und hochwertiges Werkzeug', 0, 0, 1, '1'),
(319, 247, 2081, 'Behältermanagement', 2, 2, 2, '1'),
(320, 248, 30022, 'KFZ-Kosten B MG', 0, 0, 1, '1'),
(321, 249, 80001, 'Mieteinnahmen', 25, 4, 2, '1'),
(322, 250, 2201, 'Eis- und Schneebeseitigung Material', 2, 2, 2, '1'),
(323, 251, 4160, 'Verwaltergeb. / Honorare', 16, 1, 2, '0'),
(324, 252, 2301, 'Gartenpflege Material', 2, 2, 2, '1'),
(325, 253, 52021, 'Sonstige Ausgaben', 16, 1, 2, '1'),
(326, 254, 5060, 'Kontoführungsgebühren', 16, 1, 2, '1'),
(327, 255, 4240, 'Inserate', 16, 1, 2, '1'),
(328, 256, 4192, 'Umlagenabr. Altmieter', 13, 7, 2, '1'),
(329, 257, 4180, 'Gewährte Minderungen', 13, 7, 2, '1'),
(330, 258, 5088, 'Mitarbeiterdarlehen Stremlau Z.', 0, 0, 1, '0'),
(331, 259, 5089, 'Mitarbeiterdarlehen Gül', 0, 0, 1, '0'),
(332, 260, 1300, 'Umsatzsteuer', 0, 1, 2, '1'),
(333, 261, 5300, 'Rg. zur Weiterleitung an Mieter', 0, 1, 2, '1'),
(334, 262, 7999, 'Sicherheitseinbehalt', 0, 0, 1, '1'),
(335, 263, 7000, 'Verrechnungskonto HG Abrechnung', 21, 6, 6, '1'),
(336, 264, 7001, 'Einbehalt aus Hausgeldabrechnungen zur Verrechnung', 21, 6, 6, '1'),
(337, 265, 1002, 'Körperschaftsteuer', 0, 0, 1, '1'),
(338, 266, 5090, 'Mitarbeiterdarlehen Stremlau M.', 0, 0, 1, '0'),
(339, 267, 4191, 'Verr. Kto. Korr. Umlagenabrechnung', 0, 7, 2, '1'),
(340, 268, 4190, 'Uneinbringliche Forderungen', 6, 1, 2, '1'),
(341, 269, 2901, 'sonst. Betriebskosten Material', 2, 2, 2, '1'),
(342, 270, 2999, 'Abrechnung gegenüber Vorverwaltung BK', 2, 2, 2, '1'),
(343, 271, 3999, 'Abrechnung gegenüber Vorverwaltung HK', 3, 3, 2, '1'),
(372, 300, 80014, 'vereinnahmte Mahngebühren, Zinsen aus Mitarbeiterd', 0, 4, 1, '1'),
(373, 301, 5061, 'Portogebühren', 4, 5, 6, '1'),
(375, 303, 2081, 'Recycling', 2, 5, 6, '1'),
(377, 305, 2021, 'Versicherungsschäden', 4, 5, 6, '1'),
(412, 340, 5021, 'Eigentümerentnahme für Hausgeld', 8, 5, 2, '1'),
(413, 341, 30023, 'Bobcat (kleiner Bagger)', 0, 5, 1, '1'),
(415, 343, 5085, 'Bausparvertrag', 10, 5, 2, '1'),
(416, 344, 8004, 'Einnahmen W.Wehrheim', 12, 4, 1, '0'),
(417, 345, 8004, 'Einnahmen A., T., W.Wehrheim', 12, 4, 1, '0'),
(434, 362, 3007, 'Miete Büro', 21, 5, 1, '0'),
(435, 363, 3007, 'Miete Büro', 21, 0, 1, '0'),
(436, 364, 3007, 'Miete Büro', 0, 0, 1, '0'),
(451, 379, 2061, 'Stromkosten Jahresabrechnung', 2, 5, 6, '1'),
(485, 413, 2042, 'Schmutzwasser', 2, 5, 6, '1'),
(486, 414, 2043, 'Wasserkosten Jahresabrechnung', 2, 5, 6, '1'),
(487, 415, 2045, 'Erstattungen/Belastungen BWB', 2, 5, 6, '1'),
(488, 416, 2401, 'Erstattungen/Belastungen Heizung', 2, 5, 6, '1'),
(489, 417, 2041, 'Niederschlagswasser', 2, 5, 6, '1'),
(499, 427, 6060, 'Sondervermögen Einnahmen', 8, 4, 7, '1'),
(500, 428, 30024, 'KFZ B-PZ 2243', 16, 5, 1, '0'),
(501, 429, 30024, 'KFZ-Kosten B-PZ 2243', 0, 0, 1, '0'),
(502, 430, 30024, 'KFZ-Kosten B-PZ ', 0, 0, 1, '0'),
(504, 432, 5299, 'Mietausfallgarantie', 0, 4, 2, '1'),
(594, 522, 8005, 'HW Service', 0, 4, 1, '0'),
(595, 523, 8005, 'Einnahmen aus HW Service', 0, 4, 1, '0'),
(597, 525, 2902, 'Reinigungsmittel Wedding', 15, 5, 2, '0'),
(598, 526, 2902, 'Reinigungsmittel Wedding', 2, 5, 2, '0'),
(599, 527, 2903, 'Kraftstoff Wedding', 2, 5, 2, '0'),
(600, 528, 2902, 'Reinigungsmittel Wedding', 2, 2, 2, '1'),
(601, 529, 2903, 'Kraftstoff Wedding', 2, 2, 2, '1'),
(612, 540, 5022, 'Rücklage für Nebenkostenabrechnung', 4, 4, 2, '1'),
(625, 553, 3005, 'Internet Service / Telefon', 0, 0, 1, '0'),
(626, 554, 3005, 'Internet Service ', 0, 0, 1, '0'),
(627, 555, 3008, 'Telefon', 0, 0, 1, '1'),
(628, 556, 99999, 'Verrechnungskonto', 21, 5, 2, '1'),
(635, 563, 6041, 'Verrechnung mit IHR Konto', 21, 7, 6, '1'),
(639, 567, 2111, 'Wartung RWA Anlage', 2, 2, 2, '1'),
(640, 568, 2130, 'Reinigung Hof', 2, 2, 2, '1'),
(641, 569, 6001, 'Einnahme Vorverwaltung', 8, 6, 2, '0'),
(642, 570, 4007, 'Lohnpfändung', 0, 0, 1, '1'),
(643, 571, 5400, 'durch INS zu erstatten', 21, 7, 2, '1'),
(660, 588, 5500, 'INS-Maklergebühr', 21, 5, 2, '1'),
(665, 593, 10000, 'Übertrag auf Hausgeldkonto', 21, 5, 7, '1'),
(705, 633, 6001, 'Einnahme / Ausgaben Vorverwaltung', 8, 6, 2, '0'),
(706, 634, 1030, 'Versicherungsschäden & Versicherungserstattungen', 1, 1, 2, '1'),
(707, 635, 1041, 'Große Instandhaltung', 1, 1, 2, '1'),
(708, 636, 2080, 'Müllbeseitigung/Strassenreinigung/Entrümpelung', 2, 2, 2, '1'),
(709, 637, 2120, 'Reinigungsservice /Schädlingsbekämpfung', 2, 2, 2, '1'),
(710, 638, 4281, 'Anwaltkosten/EMA', 7, 1, 2, '1'),
(711, 639, 6001, 'Einnahmen/Ausgaben Vorverwaltung', 8, 6, 2, '1'),
(765, 693, 5555, 'WEG - Liquiditätssicherung', 21, 0, 2, '1'),
(766, 694, 5556, 'WEG - von WEG zu erstatten', 21, 0, 2, '1'),
(814, 742, 6666, 'eventuelle Zahlungen an Vorverwaltung', 21, 6, 2, '1'),
(815, 743, 6667, 'nachgewiesene Zahlungen an die Vorverwaltung', 21, 6, 2, '0'),
(816, 744, 6667, 'nachgewiesene Zahlungen an Vorverwaltung', 21, 6, 2, '1'),
(817, 745, 4280, 'Gerichtskostenvorschuss', 7, 1, 2, '1'),
(818, 746, 5505, 'Steuerberatungskosten', 21, 5, 2, '0'),
(823, 751, 5505, 'Steuerberatungskosten', 1, 1, 2, '1'),
(863, 791, 5600, 'Mietaufehebungsvereinbarungen', 21, 7, 2, '1'),
(865, 793, 2112, 'Wartung Aufzug', 2, 2, 2, '1'),
(867, 795, 1029, 'Kosten vor Inbetriebnahme Aufzug', 4, 1, 7, '0'),
(868, 796, 1029, 'Kosten vor Inbetriebnahme Aufzug', 4, 5, 7, '1'),
(870, 798, 3002, 'KFZ Kosten (Renault) B-LS 8808', 0, 0, 1, '1'),
(875, 803, 30025, 'Kredit Kubota BX2350', 16, 5, 1, '0'),
(876, 804, 5060, 'Kontoführungsgebühren', 9, 5, 7, '1'),
(877, 805, 11000, 'Dachreparaturkosten', 4, 5, 7, '1'),
(879, 807, 13000, 'Fremdgeld / zu erstatten', 21, 6, 7, '1'),
(882, 810, 5555, 'Liquiditätssicherung / Hausgeldkonto', 10, 7, 7, '1'),
(883, 811, 9000, 'Einnahmen aus Sonderumlagen', 8, 4, 7, '1'),
(884, 812, 3009, 'Porto', 0, 0, 1, '0'),
(885, 813, 3009, 'Briefporto', 0, 0, 1, '1'),
(886, 814, 8004, 'Einnahmen A., T., W.Wehrheim (DW)', 12, 4, 1, '1'),
(887, 815, 30026, 'B-EZ 9503 (Smart)', 0, 5, 1, '1'),
(975, 903, 30025, 'Kubota BX2350 (B-HV 190)', 0, 5, 1, '1'),
(977, 905, 30021, 'KFZ-Kosten B-LS 8848 VW', 0, 0, 1, '1'),
(978, 906, 3001, 'KFZ Kosten B-W 4888 Mercedes', 0, 0, 1, '1'),
(980, 908, 30024, 'KFZ-Kosten B-PZ 2243', 0, 0, 1, '0'),
(987, 915, 3333, 'Amazon', 0, 0, 1, '1'),
(988, 916, 3010, 'Metro', 0, 0, 1, '1'),
(989, 917, 3011, 'Kabel Deutschland', 0, 0, 1, '0'),
(990, 918, 8001, 'Einnahmen Fremde', 0, 0, 1, '1'),
(991, 919, 8005, 'Einnahmen aus Hauswartservice', 0, 4, 1, '1'),
(992, 920, 8000, 'Einnahmen Eigentümerinnen Frau Wehrheim & Frau Pfe', 0, 0, 1, '1'),
(993, 921, 8002, 'Einnahmen Berlus Hausverwaltung', 0, 0, 1, '1'),
(994, 922, 7001, 'Sonstige Rechnungen', 0, 0, 1, '1'),
(995, 923, 1004, 'Kirchensteuer', 0, 0, 1, '1'),
(996, 924, 3005, 'Internet ', 0, 0, 1, '1'),
(997, 925, 3000, 'Sonstige Kosten', 0, 0, 1, '1'),
(998, 926, 5060, 'Bankgebühren', 0, 0, 1, '1'),
(999, 927, 3007, 'Büromiete', 0, 0, 1, '1'),
(1000, 928, 7001, 'Forderungen', 0, 0, 2, '1'),
(1003, 931, 30001, 'Ingineurleistungen', 16, 5, 7, '1'),
(1004, 932, 30002, 'Außenanlagen / Garten', 16, 5, 7, '1'),
(1005, 933, 30003, 'Schließanlage', 16, 5, 7, '1'),
(1006, 934, 30004, 'Regenwasseranschluß', 16, 5, 7, '1'),
(1007, 935, 10002, 'Kosten Vorjahr', 12, 1, 6, '0'),
(1008, 936, 10001, 'Kosten Folgejahr', 16, 4, 6, '0'),
(1009, 937, 10002, 'Kosten Vorjahr', 16, 1, 6, '0'),
(1010, 938, 2130, 'Gartenpflege', 21, 5, 6, '0'),
(1011, 939, 2130, 'Gartenpflege', 2, 5, 6, '1'),
(1012, 940, 10001, 'Kosten Folgejahr', 4, 6, 6, '0'),
(1013, 941, 10001, 'Kosten Folgejahr', 21, 6, 6, '1'),
(1014, 942, 10002, 'Kosten Vorjahr', 21, 6, 6, '1'),
(1015, 943, 50000, 'periodenfremde Buchungen Folgejahr', 21, 7, 6, '1'),
(1016, 944, 2022, 'Haftpflicht Beirat', 21, 5, 6, '1'),
(1032, 960, 2405, 'Direktkosten ET - Heizung', 4, 8, 6, '1'),
(1033, 961, 9009, 'Kosten Eigentümerversammlung', 4, 8, 6, '1'),
(1034, 962, 6088, 'Hausgeldzahlungen für Folgejahr', 8, 7, 6, '1'),
(1035, 963, 6087, 'Hausgeldzahlungen im Voraus aus dem Vorjahr', 8, 7, 6, '1'),
(1039, 967, 9089, 'Mitarbeiterdarlehen Gül', 0, 0, 1, '1'),
(1040, 968, 9090, 'Mitarbeiterdarlehen Stremlau M.', 0, 0, 1, '1'),
(1041, 969, 9088, 'Mitarbeiterdarlehen Stremlau Z.', 0, 0, 1, '1'),
(1043, 971, 3012, 'Twister Kurierdienst', 0, 0, 1, '1'),
(1052, 980, 2040, 'Kaltwasserkosten', 2, 2, 2, '1'),
(1053, 981, 2039, 'Schmutz-/Abwasserkosten', 2, 2, 2, '1'),
(1058, 986, 4160, 'Verwaltergebühr', 16, 1, 2, '1'),
(1075, 1003, 2048, 'Legionellenuntersuchung', 2, 2, 6, '1'),
(1092, 1020, 8889, 'Einnahmen/Ausgaben ab dem 15.10.2015', 13, 0, 7, '1'),
(1094, 1022, 3011, 'Vodafone Kabel Deutschland', 0, 0, 1, '1'),
(1095, 1023, 30024, 'KFZ-Kosten B-PZ 2243 (Iveco)', 0, 0, 1, '1');"
            );
        }

        if (!Schema::hasTable('KONTENRAHMEN_KONTOARTEN')) {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS `KONTENRAHMEN_KONTOARTEN` (
  `KONTENRAHMEN_KONTOART_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `KONTENRAHMEN_KONTOART_ID` int(7) NOT NULL,
  `KONTOART` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`KONTENRAHMEN_KONTOART_DAT`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=10 ;"
            );

            DB::insert(
                "INSERT INTO `KONTENRAHMEN_KONTOARTEN` (`KONTENRAHMEN_KONTOART_DAT`, `KONTENRAHMEN_KONTOART_ID`, `KONTOART`, `AKTUELL`) VALUES
(1, 1, 'Kosten', '1'),
(2, 2, 'BK', '1'),
(3, 3, 'HK', '1'),
(4, 4, 'Einnahmen', '1'),
(5, 5, 'Ausgaben', '1'),
(6, 6, 'Durchlaufkonto', '1'),
(7, 7, 'Verrechnungskonto', '1'),
(8, 0, 'Keine Kontoart', '1'),
(9, 8, 'Sonstige Ausgaben', '1');"
            );
        }

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `KONTENRAHMEN_ZUWEISUNG` (
  `DAT` int(6) NOT NULL AUTO_INCREMENT,
  `ID` int(6) NOT NULL,
  `TYP` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TYP_ID` int(6) NOT NULL,
  `KONTENRAHMEN_ID` int(6) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `KONTIERUNG_POSITIONEN` (
  `KONTIERUNG_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `KONTIERUNG_ID` int(7) NOT NULL,
  `BELEG_NR` int(7) NOT NULL,
  `POSITION` int(7) NOT NULL,
  `MENGE` decimal(10,2) NOT NULL,
  `EINZEL_PREIS` decimal(10,4) NOT NULL,
  `GESAMT_SUMME` decimal(10,4) NOT NULL,
  `MWST_SATZ` int(2) NOT NULL,
  `SKONTO` decimal(3,2) DEFAULT NULL,
  `RABATT_SATZ` decimal(4,2) NOT NULL,
  `KONTENRAHMEN_KONTO` int(7) NOT NULL,
  `KOSTENTRAEGER_TYP` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOSTENTRAEGER_ID` int(7) NOT NULL,
  `KONTIERUNGS_DATUM` date NOT NULL,
  `VERWENDUNGS_JAHR` decimal(4,0) NOT NULL,
  `WEITER_VERWENDEN` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`KONTIERUNG_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `KUNDEN_LOGIN` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ID` int(7) NOT NULL,
  `USERNAME` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `PASSWORD` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `EMAIL` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `PERSON_ID` int(7) NOT NULL,
  `PDF_PARTNER_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `KUNDEN_LOG_BER` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ID` int(7) NOT NULL,
  `PERSON_ID` int(7) NOT NULL,
  `ZUGRIFF_OBJ` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ZUGRIFF_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `LAGER` (
  `LAGER_DAT` int(4) NOT NULL AUTO_INCREMENT,
  `LAGER_ID` int(4) NOT NULL,
  `LAGER_NAME` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `LAGER_VERWALTER` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`LAGER_DAT`),
  KEY `LAGER_ID` (`LAGER_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `LAGER_PARTNER` (
  `DAT` int(6) NOT NULL AUTO_INCREMENT,
  `LAGER_ID` int(6) NOT NULL,
  `PARTNER_ID` int(6) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`),
  KEY `LAGER_ID` (`LAGER_ID`),
  KEY `PARTNER_ID` (`PARTNER_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `LEISTUNGSKATALOG` (
  `LK_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `LK_ID` int(7) NOT NULL,
  `BEZEICHNUNG` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `GEWERK` int(11) DEFAULT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`LK_DAT`),
  KEY `LK_ID` (`LK_ID`),
  KEY `BEZEICHNUNG` (`BEZEICHNUNG`),
  KEY `GEWERK` (`GEWERK`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `LIEFERSCHEINE` (
  `L_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `L_ID` int(7) NOT NULL,
  `DATUM` date NOT NULL,
  `LI_TYP` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `LI_ID` int(7) NOT NULL,
  `EMPF_TYP` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `EMPF_ID` int(7) NOT NULL,
  `L_NR` int(30) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`L_DAT`),
  KEY `L_ID` (`L_ID`),
  KEY `EMPF_TYP` (`EMPF_TYP`),
  KEY `EMPF_ID` (`EMPF_ID`),
  KEY `L_NR` (`L_NR`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `MIETENTWICKLUNG` (
  `MIETENTWICKLUNG_DAT` int(11) NOT NULL AUTO_INCREMENT,
  `MIETENTWICKLUNG_ID` int(11) NOT NULL,
  `KOSTENTRAEGER_TYP` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOSTENTRAEGER_ID` int(11) NOT NULL,
  `KOSTENKATEGORIE` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ANFANG` date NOT NULL,
  `ENDE` date NOT NULL,
  `MWST_ANTEIL` decimal(10,2) NOT NULL,
  `BETRAG` decimal(10,2) NOT NULL,
  `MIETENTWICKLUNG_AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`MIETENTWICKLUNG_DAT`),
  KEY `KOSTENTRAEGER_TYP` (`KOSTENTRAEGER_TYP`),
  KEY `KOSTENTRAEGER_ID` (`KOSTENTRAEGER_ID`),
  KEY `BETRAG` (`BETRAG`),
  KEY `ANFANG` (`ANFANG`),
  KEY `ENDE` (`ENDE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `MIETER_MAHNLISTEN` (
  `MAHN_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `DATUM` date NOT NULL,
  `MIETVERTRAG_ID` int(11) NOT NULL,
  `SALDO` decimal(10,2) NOT NULL,
  `ZAHLUNGSFRIST_Z` date NOT NULL,
  `ZAHLUNGSFRIST_M` date NOT NULL,
  `MAHN_GEB` decimal(6,2) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`MAHN_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        if (!Schema::hasTable('MIETSPIEGEL')) {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS `MIETSPIEGEL` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `JAHR` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2015',
  `FELD` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `U_WERT` decimal(4,2) NOT NULL,
  `M_WERT` decimal(4,2) NOT NULL,
  `O_WERT` decimal(4,2) NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=377 ;"
            );

            DB::insert(
                "INSERT INTO `MIETSPIEGEL` (`DAT`, `JAHR`, `FELD`, `U_WERT`, `M_WERT`, `O_WERT`) VALUES
(1, '2009', 'A5', '4.30', '4.94', '6.25'),
(2, '2009', 'D5', '4.04', '4.73', '5.77'),
(3, '2009', 'G5', '4.15', '4.55', '5.27'),
(4, '2009', 'G4', '4.19', '4.95', '5.62'),
(5, '2009', 'J4', '3.57', '4.55', '5.82'),
(6, '2009', 'A2', '3.11', '5.07', '6.93'),
(7, '2009', 'A4', '4.80', '5.40', '6.29'),
(8, '2009', 'A6', '3.79', '5.08', '6.34'),
(9, '2009', 'A7', '3.94', '4.51', '5.46'),
(10, '2009', 'A10', '4.43', '5.58', '6.01'),
(11, '2009', 'B2', '3.16', '5.15', '6.70'),
(12, '2009', 'B4', '4.53', '5.70', '6.36'),
(13, '2009', 'B5', '4.12', '4.64', '5.53'),
(14, '2009', 'B6', '3.97', '5.14', '6.50'),
(15, '2009', 'B7', '4.50', '4.96', '6.07'),
(16, '2009', 'B10', '5.06', '6.11', '6.66'),
(17, '2009', 'C2', '3.68', '4.89', '5.36'),
(18, '2009', 'C3', '3.38', '3.56', '4.01'),
(19, '2009', 'C4', '5.35', '6.22', '6.70'),
(20, '2009', 'C5', '4.49', '5.70', '6.87'),
(21, '2009', 'C6', '4.49', '5.88', '6.69'),
(22, '2009', 'C7', '5.70', '6.53', '6.91'),
(23, '2009', 'C10', '4.73', '5.55', '6.37'),
(24, '2009', 'D1', '3.14', '3.51', '4.30'),
(25, '2009', 'D2', '3.12', '4.54', '6.03'),
(26, '2009', 'D3', '2.98', '3.21', '3.44'),
(27, '2009', 'D4', '4.27', '4.89', '5.80'),
(28, '2009', 'D6', '3.94', '4.72', '6.10'),
(29, '2009', 'D7', '3.83', '4.29', '4.90'),
(30, '2009', 'D8', '4.78', '5.52', '6.38'),
(31, '2009', 'D10', '4.23', '4.80', '5.24'),
(32, '2009', 'D11', '5.90', '6.41', '6.80'),
(33, '2009', 'E1', '3.14', '3.21', '3.45'),
(34, '2009', 'E2', '3.77', '4.85', '6.05'),
(35, '2009', 'E3', '3.13', '3.44', '3.59'),
(36, '2009', 'E4', '3.91', '4.81', '5.65'),
(37, '2009', 'E5', '4.27', '4.72', '5.41'),
(38, '2009', 'E6', '4.19', '4.86', '5.60'),
(39, '2009', 'E7', '4.38', '4.96', '5.28'),
(40, '2009', 'E8', '4.84', '5.97', '7.02'),
(41, '2009', 'E10', '4.32', '5.04', '5.60'),
(42, '2009', 'E11', '5.69', '7.03', '7.56'),
(43, '2009', 'F1', '2.61', '2.85', '3.10'),
(44, '2009', 'F2', '4.85', '5.42', '6.06'),
(45, '2009', 'F4', '4.59', '5.45', '6.49'),
(46, '2009', 'F5', '4.15', '4.55', '5.27'),
(47, '2009', 'F6', '4.14', '4.82', '5.49'),
(48, '2009', 'F7', '4.65', '5.36', '6.40'),
(49, '2009', 'F8', '5.81', '6.80', '7.50'),
(50, '2009', 'F10', '4.66', '4.97', '5.95'),
(51, '2009', 'F11', '6.00', '7.17', '8.47'),
(52, '2009', 'G1', '1.82', '3.29', '4.38'),
(53, '2009', 'G2', '3.65', '4.60', '6.00'),
(54, '2009', 'G3', '3.00', '3.17', '3.46'),
(55, '2009', 'G6', '3.61', '4.18', '5.34'),
(56, '2009', 'G7', '3.91', '4.10', '4.43'),
(57, '2009', 'G8', '4.25', '5.38', '6.96'),
(58, '2009', 'G9', '5.00', '6.37', '6.95'),
(59, '2009', 'G10', '3.87', '4.36', '4.69'),
(60, '2009', 'G11', '4.98', '6.13', '7.18'),
(61, '2009', 'H1', '2.99', '3.40', '4.20'),
(62, '2009', 'H2', '3.78', '4.85', '6.00'),
(63, '2009', 'H3', '2.91', '3.21', '3.48'),
(64, '2009', 'H4', '4.07', '4.70', '5.40'),
(65, '2009', 'H5', '4.45', '5.02', '5.74'),
(66, '2009', 'H6', '3.90', '4.51', '5.20'),
(67, '2009', 'H7', '4.03', '4.62', '5.19'),
(68, '2009', 'H8', '3.72', '5.56', '6.82'),
(69, '2009', 'H9', '5.40', '5.80', '5.92'),
(70, '2009', 'H10', '3.94', '4.51', '5.04'),
(71, '2009', 'H11', '5.77', '6.87', '7.50'),
(72, '2009', 'I1', '2.63', '3.08', '4.21'),
(73, '2009', 'I2', '3.77', '5.08', '6.33'),
(74, '2009', 'I3', '3.05', '3.12', '3.25'),
(75, '2009', 'I4', '4.62', '5.38', '6.65'),
(76, '2009', 'I5', '4.34', '4.98', '5.71'),
(77, '2009', 'I6', '4.18', '4.70', '5.18'),
(78, '2009', 'I7', '4.85', '5.49', '6.04'),
(79, '2009', 'I8', '5.90', '7.44', '9.05'),
(80, '2009', 'I9', '5.26', '6.62', '8.13'),
(81, '2009', 'J1', '2.53', '2.77', '3.40'),
(82, '2009', 'J2', '3.45', '4.46', '5.54'),
(83, '2009', 'J4', '3.57', '4.55', '5.82'),
(84, '2009', 'J7', '3.72', '4.04', '4.16'),
(85, '2009', 'J10', '3.57', '4.29', '5.07'),
(86, '2009', 'J11', '4.35', '5.96', '7.15'),
(87, '2009', 'K1', '2.92', '3.15', '3.61'),
(88, '2009', 'K2', '3.71', '4.53', '5.50'),
(89, '2009', 'K4', '4.40', '4.81', '5.34'),
(90, '2009', 'K5', '4.58', '5.25', '5.89'),
(91, '2009', 'K6', '3.29', '5.12', '6.38'),
(92, '2009', 'K7', '3.99', '4.96', '5.62'),
(93, '2009', 'K8', '5.85', '6.57', '7.80'),
(94, '2009', 'K9', '4.65', '7.04', '8.00'),
(95, '2009', 'K10', '3.86', '4.32', '4.95'),
(96, '2009', 'K11', '5.36', '6.54', '7.55'),
(97, '2009', 'L1', '2.85', '3.11', '3.53'),
(98, '2009', 'L2', '4.24', '5.18', '6.57'),
(99, '2009', 'L3', '2.81', '3.04', '3.27'),
(100, '2009', 'L4', '4.40', '5.00', '6.00'),
(101, '2009', 'L5', '3.83', '4.80', '5.94'),
(102, '2009', 'L6', '4.20', '5.96', '10.28'),
(103, '2009', 'L7', '5.23', '6.50', '8.17'),
(104, '2009', 'L8', '6.01', '7.35', '8.51'),
(105, '2009', 'L9', '6.53', '7.46', '8.35'),
(106, '2009', 'L10', '4.27', '4.57', '5.14'),
(107, '2009', 'L11', '5.02', '7.21', '9.00'),
(108, '2011', 'G3', '3.07', '3.36', '3.46'),
(109, '2011', 'A2', '4.43', '5.69', '7.28'),
(110, '2011', 'A4', '5.50', '6.33', '7.20'),
(111, '2011', 'A5', '4.69', '4.88', '5.18'),
(112, '2011', 'A6', '4.01', '5.04', '6.24'),
(113, '2011', 'A7', '3.99', '5.07', '6.59'),
(114, '2011', 'A10', '5.00', '6.00', '7.56'),
(115, '2011', 'B2', '4.01', '6.51', '7.79'),
(116, '2011', 'B4', '5.02', '6.10', '6.70'),
(117, '2011', 'B5', '4.66', '5.56', '6.16'),
(118, '2011', 'B6', '4.31', '5.51', '6.97'),
(119, '2011', 'B7', '4.53', '5.16', '5.59'),
(120, '2011', 'B10', '5.61', '6.17', '6.75'),
(121, '2011', 'C2', '5.09', '6.08', '7.53'),
(122, '2011', 'C3', '3.44', '3.65', '4.50'),
(123, '2011', 'C4', '5.33', '6.43', '6.94'),
(124, '2011', 'C5', '4.68', '6.16', '7.79'),
(125, '2011', 'C6', '5.41', '6.25', '7.50'),
(126, '2011', 'C7', '5.91', '7.12', '7.98'),
(127, '2011', 'C10', '5.59', '6.63', '8.18'),
(128, '2011', 'D1', '3.41', '3.99', '5.20'),
(129, '2011', 'D2', '3.38', '4.69', '5.87'),
(130, '2011', 'D3', '3.30', '3.98', '4.50'),
(131, '2011', 'D4', '4.56', '5.24', '6.00'),
(132, '2011', 'D5', '4.40', '4.76', '5.30'),
(133, '2011', 'D6', '4.36', '4.84', '5.70'),
(134, '2011', 'D7', '4.13', '4.48', '4.91'),
(135, '2011', 'D10', '4.38', '4.95', '5.38'),
(136, '2011', 'D11', '6.02', '6.52', '6.70'),
(137, '2011', 'E1', '3.18', '4.43', '5.57'),
(138, '2011', 'E2', '4.41', '5.46', '6.53'),
(139, '2011', 'E3', '3.28', '3.57', '4.00'),
(140, '2011', 'E4', '4.37', '5.21', '5.93'),
(141, '2011', 'E5', '4.51', '5.03', '5.62'),
(142, '2011', 'E6', '4.74', '5.22', '6.00'),
(143, '2011', 'E7', '4.80', '5.25', '5.50'),
(144, '2011', 'E8', '5.58', '6.27', '6.50'),
(145, '2011', 'E10', '5.03', '5.28', '5.72'),
(146, '2011', 'E11', '5.10', '7.04', '7.97'),
(147, '2011', 'F1', '3.92', '4.37', '5.00'),
(148, '2011', 'F2', '5.14', '5.65', '6.40'),
(149, '2011', 'F4', '5.22', '6.02', '7.00'),
(150, '2011', 'F5', '4.79', '5.70', '6.99'),
(151, '2011', 'F6', '4.65', '5.26', '6.09'),
(152, '2011', 'F7', '5.35', '5.79', '6.79'),
(153, '2011', 'F8', '5.10', '6.68', '7.47'),
(154, '2011', 'F10', '4.73', '5.52', '6.18'),
(155, '2011', 'F11', '5.89', '7.95', '9.97'),
(156, '2011', 'G1', '3.29', '3.83', '4.39'),
(157, '2011', 'G2', '4.00', '5.02', '6.10'),
(158, '2011', 'G4', '4.39', '5.18', '5.86'),
(159, '2011', 'G5', '4.39', '4.55', '5.04'),
(160, '2011', 'G6', '4.08', '4.56', '5.10'),
(161, '2011', 'G7', '3.99', '4.35', '4.60'),
(162, '2011', 'G8', '5.53', '6.11', '6.61'),
(163, '2011', 'G9', '4.63', '5.72', '6.41'),
(164, '2011', 'G10', '4.10', '4.49', '4.95'),
(165, '2011', 'G11', '5.40', '6.48', '8.49'),
(166, '2011', 'H1', '3.24', '3.89', '4.27'),
(167, '2011', 'H2', '4.44', '5.47', '6.52'),
(168, '2011', 'H3', '3.42', '4.09', '4.70'),
(169, '2011', 'H4', '4.48', '5.05', '5.68'),
(170, '2011', 'H5', '4.56', '5.06', '5.71'),
(171, '2011', 'H6', '4.39', '4.95', '5.57'),
(172, '2011', 'H7', '4.50', '4.96', '5.36'),
(173, '2011', 'H8', '4.85', '6.13', '6.94'),
(174, '2011', 'H9', '5.85', '6.17', '6.54'),
(175, '2011', 'H10', '4.36', '4.75', '5.09'),
(176, '2011', 'H11', '5.75', '6.80', '7.36'),
(177, '2011', 'I1', '2.81', '4.13', '5.25'),
(178, '2011', 'I2', '4.99', '5.80', '6.97'),
(179, '2011', 'I4', '4.88', '5.89', '7.10'),
(180, '2011', 'I5', '4.68', '5.54', '7.10'),
(181, '2011', 'I6', '4.65', '5.51', '6.48'),
(182, '2011', 'I7', '5.48', '6.00', '6.85'),
(183, '2011', 'I8', '5.83', '6.89', '8.04'),
(184, '2011', 'I9', '6.21', '7.27', '8.88'),
(185, '2011', 'I10', '4.58', '5.02', '5.37'),
(186, '2011', 'I11', '6.04', '7.23', '8.95'),
(187, '2011', 'J1', '2.58', '2.82', '3.34'),
(188, '2011', 'J2', '3.95', '4.77', '5.80'),
(189, '2011', 'J4', '3.04', '4.66', '5.82'),
(190, '2011', 'J7', '3.91', '4.10', '4.34'),
(191, '2011', 'J10', '3.77', '4.33', '4.65'),
(192, '2011', 'J11', '5.51', '7.05', '8.37'),
(193, '2011', 'K1', '2.92', '3.17', '3.43'),
(194, '2011', 'K2', '4.19', '5.08', '6.44'),
(195, '2011', 'K4', '4.48', '5.07', '5.82'),
(196, '2011', 'K5', '5.04', '5.63', '5.99'),
(197, '2011', 'K6', '4.86', '5.74', '6.30'),
(198, '2011', 'K7', '4.20', '5.20', '6.25'),
(199, '2011', 'K8', '6.00', '6.66', '7.78'),
(200, '2011', 'K10', '4.32', '4.58', '5.00'),
(201, '2011', 'K11', '5.50', '7.15', '8.65'),
(202, '2011', 'L1', '2.90', '3.76', '6.00'),
(203, '2011', 'L2', '4.81', '5.82', '7.34'),
(204, '2011', 'L3', '3.35', '4.02', '4.52'),
(205, '2011', 'L4', '4.66', '5.47', '7.08'),
(206, '2011', 'L5', '4.80', '6.16', '8.04'),
(207, '2011', 'L6', '5.12', '6.99', '8.20'),
(208, '2011', 'L7', '4.47', '6.87', '7.74'),
(209, '2011', 'L8', '6.16', '8.11', '9.28'),
(210, '2011', 'L9', '6.32', '7.36', '8.37'),
(211, '2011', 'L10', '4.45', '4.94', '5.14'),
(212, '2011', 'L11', '6.01', '8.19', '10.23'),
(213, '2013', 'A1', '5.06', '6.66', '8.15'),
(214, '2013', 'A2', '6.01', '6.35', '6.72'),
(215, '2013', 'A3', '4.74', '5.52', '6.70'),
(216, '2013', 'A4', '4.85', '5.86', '7.30'),
(217, '2013', 'A6', '5.08', '6.13', '7.25'),
(218, '2013', 'B1', '4.86', '6.81', '8.16'),
(219, '2013', 'B2', '6.00', '6.55', '7.00'),
(220, '2013', 'B3', '5.01', '5.72', '6.50'),
(221, '2013', 'B4', '5.40', '6.09', '7.02'),
(222, '2013', 'B6', '5.80', '6.39', '7.00'),
(223, '2013', 'C1', '5.07', '6.87', '8.42'),
(224, '2013', 'C2', '5.43', '6.19', '7.60'),
(225, '2013', 'C3', '5.00', '6.45', '7.80'),
(226, '2013', 'C4', '5.56', '7.70', '8.95'),
(227, '2013', 'C6', '5.84', '7.87', '8.46'),
(228, '2013', 'D1', '4.39', '5.57', '7.06'),
(229, '2013', 'D2', '4.82', '5.31', '5.86'),
(230, '2013', 'D3', '4.65', '5.19', '5.85'),
(231, '2013', 'D4', '4.30', '4.92', '5.94'),
(232, '2013', 'D5', '5.25', '6.33', '7.32'),
(233, '2013', 'D6', '4.86', '5.27', '5.87'),
(234, '2013', 'D7', '5.08', '6.97', '8.96'),
(235, '2013', 'E1', '4.30', '5.82', '7.45'),
(236, '2013', 'E2', '4.87', '5.51', '6.20'),
(237, '2013', 'E3', '4.73', '5.28', '5.85'),
(238, '2013', 'E4', '4.70', '5.28', '6.00'),
(239, '2013', 'E5', '5.53', '6.46', '7.00'),
(240, '2013', 'E6', '5.00', '5.35', '5.78'),
(241, '2013', 'E7', '6.30', '7.00', '7.86'),
(242, '2013', 'F1', '5.46', '6.17', '7.64'),
(243, '2013', 'F2', '5.29', '6.07', '7.45'),
(244, '2013', 'F3', '4.69', '5.53', '7.10'),
(245, '2013', 'F4', '4.98', '6.03', '7.41'),
(246, '2013', 'F5', '6.52', '7.41', '8.18'),
(247, '2013', 'F6', '5.38', '6.64', '7.03'),
(248, '2013', 'F7', '6.83', '7.93', '9.00'),
(249, '2013', 'G1', '4.11', '5.19', '6.75'),
(250, '2013', 'G2', '4.70', '5.22', '5.86'),
(251, '2013', 'G3', '4.22', '4.73', '5.33'),
(252, '2013', 'G4', '4.19', '4.50', '5.06'),
(253, '2013', 'G5', '5.10', '5.88', '6.00'),
(254, '2013', 'G6', '4.16', '4.62', '5.22'),
(255, '2013', 'G7', '5.43', '6.58', '7.72'),
(256, '2013', 'H1', '4.56', '5.67', '7.00'),
(257, '2013', 'H2', '4.57', '5.28', '6.10'),
(258, '2013', 'H3', '4.75', '5.23', '5.88'),
(259, '2013', 'H4', '4.30', '5.01', '5.50'),
(260, '2013', 'H5', '5.54', '6.10', '6.60'),
(261, '2013', 'H6', '4.28', '4.75', '5.14'),
(262, '2013', 'H7', '6.04', '6.87', '7.59'),
(263, '2013', 'I1', '4.97', '6.03', '7.51'),
(264, '2013', 'I2', '5.25', '6.08', '7.10'),
(265, '2013', 'I3', '4.75', '5.58', '6.71'),
(266, '2013', 'I4', '4.64', '6.07', '7.50'),
(267, '2013', 'I5', '6.91', '7.74', '8.44'),
(268, '2013', 'I6', '4.76', '5.34', '6.66'),
(269, '2013', 'I7', '6.40', '7.77', '9.01'),
(270, '2013', 'J1', '4.00', '5.17', '6.62'),
(271, '2013', 'J2', '4.29', '5.48', '6.93'),
(272, '2013', 'J4', '3.70', '4.28', '5.04'),
(273, '2013', 'J5', '5.30', '5.63', '6.20'),
(274, '2013', 'J6', '3.92', '4.56', '5.09'),
(275, '2013', 'J7', '6.01', '6.70', '7.42'),
(276, '2013', 'K1', '4.25', '5.30', '7.00'),
(277, '2013', 'K2', '4.79', '5.33', '6.30'),
(278, '2013', 'K3', '4.64', '5.45', '5.96'),
(279, '2013', 'K4', '4.25', '4.88', '5.26'),
(280, '2013', 'K5', '5.64', '6.64', '7.50'),
(281, '2013', 'K6', '3.95', '4.64', '5.09'),
(282, '2013', 'K7', '5.75', '6.93', '8.28'),
(283, '2013', 'L1', '4.36', '5.77', '7.67'),
(284, '2013', 'L2', '4.73', '5.56', '6.75'),
(285, '2013', 'L3', '4.68', '6.55', '7.94'),
(286, '2013', 'L4', '5.30', '7.18', '8.50'),
(287, '2013', 'L5', '6.88', '7.92', '9.45'),
(288, '2013', 'L6', '4.78', '5.38', '6.14'),
(289, '2013', 'L7', '6.97', '8.57', '10.55'),
(290, '2015', 'A1', '4.81', '6.48', '8.55'),
(291, '2015', 'A2', '5.84', '6.60', '8.03'),
(292, '2015', 'A3', '5.04', '5.53', '6.09'),
(293, '2015', 'A4', '5.13', '6.12', '7.27'),
(294, '2015', 'A6', '6.02', '6.63', '7.39'),
(295, '2015', 'B1', '5.18', '7.18', '9.27'),
(296, '2015', 'B2', '5.93', '6.86', '7.50'),
(297, '2015', 'A3', '5.53', '6.20', '7.00'),
(298, '2015', 'A4', '5.41', '6.27', '7.01'),
(299, '2015', 'B6', '6.29', '6.61', '6.86'),
(300, '2015', 'C1', '4.62', '5.76', '6.90'),
(301, '2015', 'C2', '5.52', '6.15', '6.51'),
(302, '2015', 'C3', '5.00', '6.63', '8.07'),
(303, '2015', 'C4', '6.32', '7.78', '8.98'),
(304, '2015', 'C6', '7.09', '7.88', '8.66'),
(305, '2015', 'C7', '7.35', '7.83', '8.52'),
(306, '2015', 'D1', '4.51', '5.91', '7.99'),
(307, '2015', 'D2', '4.92', '5.68', '6.93'),
(308, '2015', 'D3', '4.95', '5.43', '5.91'),
(309, '2015', 'D4', '4.74', '5.13', '5.75'),
(310, '2015', 'D5', '5.79', '6.37', '7.08'),
(311, '2015', 'D6', '5.11', '5.47', '5.81'),
(312, '2015', 'D7', '6.62', '7.45', '8.34'),
(313, '2015', 'E1', '4.34', '5.81', '7.81'),
(314, '2015', 'E2', '5.27', '5.83', '6.55'),
(315, '2015', 'E3', '5.06', '5.60', '6.50'),
(316, '2015', 'E4', '5.09', '5.43', '5.86'),
(317, '2015', 'E5', '6.46', '6.97', '7.84'),
(318, '2015', 'E6', '5.37', '5.64', '6.11'),
(319, '2015', 'E7', '6.32', '7.34', '8.36'),
(320, '2015', 'E8', '7.69', '10.01', '12.31'),
(321, '2015', 'F1', '5.62', '6.51', '8.28'),
(322, '2015', 'F2', '5.66', '6.63', '7.79'),
(323, '2015', 'F3', '5.00', '5.96', '7.70'),
(324, '2015', 'F4', '4.83', '6.57', '8.01'),
(325, '2015', 'F5', '6.77', '7.59', '8.21'),
(326, '2015', 'F6', '5.54', '6.41', '6.99'),
(327, '2015', 'F7', '7.25', '8.52', '9.55'),
(328, '2015', 'F8', '8.03', '8.62', '9.37'),
(329, '2015', 'G1', '4.40', '5.62', '7.52'),
(330, '2015', 'G2', '4.83', '5.34', '5.86'),
(331, '2015', 'G3', '4.52', '5.09', '6.07'),
(332, '2015', 'G4', '4.40', '4.70', '5.24'),
(333, '2015', 'G5', '5.47', '5.94', '7.02'),
(334, '2015', 'G6', '4.46', '4.84', '5.42'),
(335, '2015', 'G7', '5.76', '6.99', '8.27'),
(336, '2015', 'G8', '7.92', '9.36', '10.66'),
(337, '2015', 'H1', '4.55', '5.84', '8.10'),
(338, '2015', 'H2', '4.88', '5.66', '6.51'),
(339, '2015', 'H3', '4.96', '5.54', '6.53'),
(340, '2015', 'H4', '4.71', '5.21', '5.67'),
(341, '2015', 'H5', '6.24', '7.14', '8.27'),
(342, '2015', 'H6', '4.75', '4.99', '5.28'),
(343, '2015', 'H7', '5.19', '6.92', '7.97'),
(344, '2015', 'H8', '7.50', '8.79', '10.69'),
(345, '2015', 'I1', '4.84', '5.98', '7.49'),
(346, '2015', 'I2', '5.49', '6.72', '8.10'),
(347, '2015', 'I3', '5.12', '6.02', '7.32'),
(348, '2015', 'I4', '5.54', '6.39', '7.67'),
(349, '2015', 'I5', '6.80', '7.88', '8.55'),
(350, '2015', 'I6', '5.04', '5.66', '6.16'),
(351, '2015', 'I7', '7.12', '8.50', '9.64'),
(352, '2015', 'I8', '8.00', '8.63', '9.68'),
(353, '2015', 'J1', '4.13', '5.43', '7.42'),
(354, '2015', 'J2', '4.86', '5.50', '6.10'),
(355, '2015', 'J3', '4.35', '4.73', '5.11'),
(356, '2015', 'J4', '4.28', '4.77', '5.38'),
(357, '2015', 'J5', '5.39', '5.57', '6.44'),
(358, '2015', 'J6', '4.24', '4.54', '5.02'),
(359, '2015', 'J7', '6.12', '7.28', '8.01'),
(360, '2015', 'J8', '9.50', '10.88', '13.00'),
(361, '2015', 'K1', '4.51', '5.49', '6.99'),
(362, '2015', 'K2', '5.44', '4.91', '6.30'),
(363, '2015', 'K3', '5.60', '5.84', '6.69'),
(364, '2015', 'K4', '4.56', '5.10', '5.88'),
(365, '2015', 'K6', '4.66', '4.94', '5.22'),
(366, '2015', 'K5', '5.71', '6.69', '8.18'),
(367, '2015', 'K7', '6.50', '7.45', '8.98'),
(368, '2015', 'K8', '8.00', '9.79', '12.35'),
(369, '2015', 'L1', '4.72', '6.18', '8.50'),
(370, '2015', 'L2', '5.25', '6.09', '7.50'),
(371, '2015', 'L3', '5.22', '7.06', '10.00'),
(372, '2015', 'L4', '7.18', '7.82', '8.93'),
(373, '2015', 'L5', '7.33', '8.21', '9.00'),
(374, '2015', 'L6', '5.01', '5.59', '6.03'),
(375, '2015', 'L7', '7.19', '9.04', '10.71'),
(376, '2015', 'L8', '8.11', '9.14', '11.24');"
            );
        }

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `MIETVERTRAG` (
  `MIETVERTRAG_DAT` int(11) NOT NULL AUTO_INCREMENT,
  `MIETVERTRAG_ID` int(11) NOT NULL,
  `MIETVERTRAG_VON` date NOT NULL,
  `MIETVERTRAG_BIS` date NOT NULL,
  `EINHEIT_ID` int(11) NOT NULL,
  `MIETVERTRAG_AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`MIETVERTRAG_DAT`),
  KEY `EINHEIT_ID` (`EINHEIT_ID`),
  KEY `MIETVERTRAG_ID` (`MIETVERTRAG_ID`),
  KEY `MIETVERTRAG_BIS` (`MIETVERTRAG_BIS`),
  KEY `MIETVERTRAG_VON` (`MIETVERTRAG_VON`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `MS_SONDERMERKMALE` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `JAHR` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MERKMAL` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `WERT` decimal(4,2) NOT NULL,
  `A_KLASSE` int(2) DEFAULT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `OBJEKT` (
  `OBJEKT_DAT` int(11) NOT NULL AUTO_INCREMENT,
  `OBJEKT_ID` int(11) NOT NULL,
  `OBJEKT_AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `OBJEKT_KURZNAME` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `EIGENTUEMER_PARTNER` int(7) NOT NULL,
  UNIQUE KEY `OBJEKT_DAT` (`OBJEKT_DAT`),
  KEY `OBJEKT_KURZNAME` (`OBJEKT_KURZNAME`),
  KEY `OBJEKT_ID` (`OBJEKT_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `OBJEKT_PARTNER` (
  `DAT` int(6) NOT NULL AUTO_INCREMENT,
  `OBJEKT_ID` int(6) NOT NULL,
  `PARTNER_ID` int(6) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `VON` date NOT NULL,
  `BIS` date NOT NULL,
  PRIMARY KEY (`DAT`),
  KEY `OBJEKT_ID` (`OBJEKT_ID`),
  KEY `PARTNER_ID` (`PARTNER_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        if (!Schema::hasTable('PARTNER_LIEFERANT')) {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS `PARTNER_LIEFERANT` (
  `PARTNER_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `PARTNER_ID` int(7) NOT NULL,
  `PARTNER_NAME` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `STRASSE` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NUMMER` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `PLZ` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ORT` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `LAND` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`PARTNER_DAT`),
  KEY `PARTNER_ID` (`PARTNER_ID`),
  KEY `STRASSE` (`STRASSE`),
  KEY `NUMMER` (`NUMMER`),
  KEY `PLZ` (`PLZ`),
  KEY `ORT` (`ORT`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=4 ;"
            );

            DB::insert(
                "INSERT INTO `PARTNER_LIEFERANT` (`PARTNER_DAT`, `PARTNER_ID`, `PARTNER_NAME`, `STRASSE`, `NUMMER`, `PLZ`, `ORT`, `LAND`, `AKTUELL`) VALUES
(1, 1, 'Mustermann GmbH', 'Eichkampstraße', '161', '14055', 'Berlin', 'Deutschland', '1');"
            );
        }

        if (!Schema::hasTable('PARTNER_STICHWORT')) {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS `PARTNER_STICHWORT` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ID` int(7) NOT NULL,
  `PARTNER_ID` int(7) NOT NULL,
  `STICHWORT` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=2 ;"
            );

            DB::insert(
                "INSERT INTO `PARTNER_STICHWORT` (`DAT`, `ID`, `PARTNER_ID`, `STICHWORT`, `AKTUELL`) VALUES
(1, 1, 1, 'Hausverwaltung', '1');"
            );
        }

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `PDF_VORLAGEN` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `KURZTEXT` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TEXT` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `EMPF_TYP` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KAT` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        if (!Schema::hasTable('PERSON') && !Schema::hasTable('persons')) {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS `PERSON` (
  `PERSON_DAT` int(11) NOT NULL AUTO_INCREMENT,
  `PERSON_ID` int(11) NOT NULL,
  `PERSON_NACHNAME` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `PERSON_VORNAME` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `PERSON_GEBURTSTAG` date NOT NULL,
  `PERSON_AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`PERSON_DAT`),
  KEY `PERSON_ID` (`PERSON_ID`),
  KEY `PERSON_NACHNAME` (`PERSON_NACHNAME`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=2 ;"
            );

            DB::insert(
                "INSERT INTO `PERSON` (`PERSON_DAT`, `PERSON_ID`, `PERSON_NACHNAME`, `PERSON_VORNAME`, `PERSON_GEBURTSTAG`, `PERSON_AKTUELL`) VALUES
(1, 1, 'Mustermann', 'Max', '1900-01-01', '1');"
            );
        }

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `PERSON_MIETVERTRAG` (
  `PERSON_MIETVERTRAG_DAT` int(11) NOT NULL AUTO_INCREMENT,
  `PERSON_MIETVERTRAG_ID` int(11) NOT NULL,
  `PERSON_MIETVERTRAG_PERSON_ID` int(11) NOT NULL,
  `PERSON_MIETVERTRAG_MIETVERTRAG_ID` int(11) NOT NULL,
  `PERSON_MIETVERTRAG_AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`PERSON_MIETVERTRAG_DAT`),
  KEY `PERSON_MIETVERTRAG_MIETVERTRAG_ID` (`PERSON_MIETVERTRAG_MIETVERTRAG_ID`),
  KEY `PERSON_MIETVERTRAG_PERSON_ID` (`PERSON_MIETVERTRAG_PERSON_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `POSITIONEN_KATALOG` (
  `KATALOG_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `KATALOG_ID` int(7) NOT NULL,
  `ART_LIEFERANT` int(7) NOT NULL,
  `ARTIKEL_NR` varchar(20) CHARACTER SET utf8 NOT NULL,
  `BEZEICHNUNG` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `LISTENPREIS` decimal(10,4) NOT NULL,
  `RABATT_SATZ` decimal(4,2) NOT NULL,
  `EINHEIT` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MWST_SATZ` int(2) NOT NULL,
  `SKONTO` decimal(3,2) DEFAULT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`KATALOG_DAT`),
  KEY `ARTIKEL_NR` (`ARTIKEL_NR`),
  KEY `ART_LIEFERANT` (`ART_LIEFERANT`),
  KEY `LISTENPREIS` (`LISTENPREIS`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `POS_GRUPPE` (
  `B_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `B_ID` int(7) NOT NULL,
  `BELEG_NR` int(7) NOT NULL,
  `POS` int(4) NOT NULL,
  `UEBERSCHRIFT` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`B_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `POS_POOL` (
  `PP_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `U_BELEG_NR` int(7) NOT NULL,
  `U_POS` int(7) NOT NULL,
  `POOL_ID` int(7) NOT NULL,
  `POS` int(7) NOT NULL,
  `MENGE` decimal(10,2) NOT NULL,
  `EINZEL_PREIS` decimal(10,3) NOT NULL,
  `V_PREIS` decimal(10,2) NOT NULL,
  `G_SUMME` decimal(10,2) NOT NULL,
  `MWST_SATZ` decimal(10,2) NOT NULL,
  `SKONTO` decimal(3,2) NOT NULL,
  `RABATT_SATZ` decimal(10,2) NOT NULL,
  `KOSTENKONTO` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOS_TYP` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOS_ID` int(7) NOT NULL,
  `AUSSTELLER_TYP` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AUSSTELLER_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`PP_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `POS_POOLS` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ID` int(7) NOT NULL,
  `POOL_NAME` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOS_TYP` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOS_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `PROTOKOLL` (
  `PROTOKOLL_DAT` int(11) NOT NULL AUTO_INCREMENT,
  `PROTOKOLL_WER` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `PROTOKOLL_COMPUTER` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `PROTOKOLL_WANN` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `PROTOKOLL_TABELE` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `PROTOKOLL_DAT_NEU` int(11) NOT NULL,
  `PROTOKOLL_DAT_ALT` int(11) NOT NULL,
  PRIMARY KEY (`PROTOKOLL_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `RECHNUNGEN` (
  `RECHNUNG_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `BELEG_NR` int(7) NOT NULL,
  `RECHNUNGSNUMMER` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `AUSTELLER_AUSGANGS_RNR` int(7) NOT NULL,
  `EMPFAENGER_EINGANGS_RNR` int(7) NOT NULL,
  `RECHNUNGSTYP` enum('Rechnung','Stornorechnung','Gutschrift','Kassenbeleg','Buchungsbeleg','Angebot','Schlussrechnung','Teilrechnung') COLLATE utf8mb4_unicode_ci NOT NULL,
  `RECHNUNGSDATUM` date NOT NULL,
  `EINGANGSDATUM` date NOT NULL,
  `NETTO` decimal(10,2) NOT NULL,
  `BRUTTO` decimal(10,2) NOT NULL,
  `SKONTOBETRAG` decimal(10,2) NOT NULL,
  `AUSSTELLER_TYP` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AUSSTELLER_ID` int(7) NOT NULL,
  `EMPFAENGER_TYP` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `EMPFAENGER_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `STATUS_ERFASST` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `STATUS_VOLLSTAENDIG` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `STATUS_ZUGEWIESEN` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `STATUS_ZAHLUNG_FREIGEGEBEN` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `STATUS_BEZAHLT` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `STATUS_BESTAETIGT` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `FAELLIG_AM` date NOT NULL,
  `BEZAHLT_AM` date NOT NULL,
  `KURZBESCHREIBUNG` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `EMPFANGS_GELD_KONTO` int(4) NOT NULL,
  PRIMARY KEY (`RECHNUNG_DAT`),
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `RECHNUNGEN_POSITIONEN` (
  `RECHNUNGEN_POS_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `RECHNUNGEN_POS_ID` int(7) NOT NULL,
  `POSITION` int(7) NOT NULL,
  `BELEG_NR` int(7) NOT NULL,
  `U_BELEG_NR` int(7) DEFAULT NULL,
  `ART_LIEFERANT` int(6) NOT NULL,
  `ARTIKEL_NR` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MENGE` decimal(10,2) NOT NULL,
  `PREIS` decimal(10,4) NOT NULL,
  `MWST_SATZ` int(2) NOT NULL,
  `RABATT_SATZ` decimal(5,2) NOT NULL,
  `SKONTO` decimal(3,2) DEFAULT NULL,
  `GESAMT_NETTO` decimal(10,4) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`RECHNUNGEN_POS_DAT`),
  KEY `BELEG_NR` (`BELEG_NR`),
  KEY `U_BELEG_NR` (`U_BELEG_NR`),
  KEY `AKTUELL` (`AKTUELL`),
  KEY `POSITION` (`POSITION`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `RECHNUNGEN_SCHLUSS` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ID` int(7) NOT NULL,
  `SCHLUSS_R_ID` int(7) NOT NULL,
  `TEIL_R_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `RECHNUNG_KUERZEL` (
  `RK_DAT` int(4) NOT NULL AUTO_INCREMENT,
  `AUSSTELLER_TYP` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AUSSTELLER_ID` int(6) NOT NULL,
  `KUERZEL` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `VON` date NOT NULL,
  `BIS` date NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`RK_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `SEPA_MANDATE` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `M_ID` int(7) NOT NULL,
  `M_REFERENZ` varchar(35) COLLATE utf8mb4_unicode_ci NOT NULL,
  `GLAEUBIGER_ID` varchar(35) COLLATE utf8mb4_unicode_ci NOT NULL,
  `GLAEUBIGER_GK_ID` int(7) NOT NULL,
  `BEGUENSTIGTER` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NAME` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ANSCHRIFT` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KONTONR` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `BLZ` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `IBAN` varchar(35) COLLATE utf8mb4_unicode_ci NOT NULL,
  `BIC` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `BANKNAME` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `M_UDATUM` date NOT NULL,
  `M_ADATUM` date NOT NULL,
  `M_EDATUM` date NOT NULL DEFAULT '9999-12-31',
  `M_ART` enum('EINMALIG','WIEDERKEHREND') COLLATE utf8mb4_unicode_ci NOT NULL,
  `NUTZUNGSART` enum('MIETZAHLUNG','RECHNUNGEN','HAUSGELD') COLLATE utf8mb4_unicode_ci NOT NULL,
  `EINZUGSART` enum('Aktuelles Saldo komplett','Nur die Summe aus Vertrag','Ratenzahlung') COLLATE utf8mb4_unicode_ci NOT NULL,
  `M_KOS_TYP` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `M_KOS_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `SEPA_MANDATE_SEQ` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `M_REFERENZ` varchar(35) COLLATE utf8mb4_unicode_ci NOT NULL,
  `IBAN` varchar(35) COLLATE utf8mb4_unicode_ci NOT NULL,
  `SEQ` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `BETRAG` decimal(10,2) NOT NULL,
  `DATEI` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DATUM` date NOT NULL,
  `VZWECK` varchar(140) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `SEPA_UEBERWEISUNG` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ID` int(7) NOT NULL,
  `FILE` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `KAT` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `VZWECK` varchar(144) COLLATE utf8mb4_unicode_ci NOT NULL,
  `BETRAG` decimal(10,2) NOT NULL,
  `GK_ID_AUFTRAG` int(7) NOT NULL,
  `IBAN` varchar(35) COLLATE utf8mb4_unicode_ci NOT NULL,
  `BIC` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `BANKNAME` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `BEGUENSTIGTER` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOS_TYP` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOS_ID` int(7) NOT NULL,
  `KONTO` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `SICH_EINBEHALT` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `BELEG_NR` int(7) NOT NULL,
  `DATUM` date NOT NULL,
  `PROZENT` decimal(2,0) NOT NULL,
  `BETRAG` decimal(7,2) NOT NULL,
  `EINBEHALT_BIS` date NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `START_STOP` (
  `S_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `TAB` varchar(20) NOT NULL,
  `TAB_DAT` int(7) NOT NULL,
  `START_TIME` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `END_TIME` timestamp NULL DEFAULT NULL,
  `UNTERBROCHEN` enum('1') DEFAULT NULL,
  `BENUTZER_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') NOT NULL,
  PRIMARY KEY (`S_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `STUNDENZETTEL` (
  `ZETTEL_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ZETTEL_ID` int(7) NOT NULL,
  `BENUTZER_ID` int(7) NOT NULL,
  `BESCHREIBUNG` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ERFASSUNGSDATUM` date NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ZETTEL_DAT`),
  KEY `BENUTZER_ID` (`BENUTZER_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `STUNDENZETTEL_POS` (
  `ST_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ST_ID` int(7) NOT NULL,
  `ZETTEL_ID` int(7) NOT NULL,
  `DATUM` date NOT NULL,
  `BEGINN` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ENDE` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `LEISTUNG_ID` int(11) NOT NULL,
  `DAUER_MIN` decimal(4,0) NOT NULL,
  `KOSTENTRAEGER_TYP` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOSTENTRAEGER_ID` int(7) NOT NULL,
  `HINWEIS` mediumtext COLLATE utf8mb4_unicode_ci,
  `IN_BELEG` int(7) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ST_DAT`),
  KEY `DATUM` (`DATUM`),
  KEY `ZETTEL_ID` (`ZETTEL_ID`),
  KEY `LEISTUNG_ID` (`LEISTUNG_ID`),
  KEY `KOSTENTRAEGER_TYP` (`KOSTENTRAEGER_TYP`),
  KEY `KOSTENTRAEGER_ID` (`KOSTENTRAEGER_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `TODO_LISTE` (
  `T_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `T_ID` int(7) NOT NULL,
  `UE_ID` int(7) DEFAULT NULL,
  `TEXT` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ERSTELLT` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ANZEIGEN_AB` date NOT NULL,
  `BENUTZER_TYP` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `BENUTZER_ID` int(7) NOT NULL,
  `VERFASSER_ID` int(7) NOT NULL,
  `ERLEDIGT` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKUT` enum('JA','NEIN') COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOS_TYP` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOS_ID` int(7) NOT NULL,
  `WERT_EUR` decimal(10,2) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`T_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `TRANSFER_TAB` (
  `MIETVERTRAG_ID` int(6) DEFAULT NULL,
  `EINHEIT_ID` int(6) NOT NULL,
  `EINHEIT_KURZNAME` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `FM_Kurzname` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `FM_Einheitenname` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `UEBERWEISUNG` (
  `U_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `DTAUS_ID` int(7) DEFAULT NULL,
  `DATUM` date NOT NULL,
  `A_KONTO_ID` int(7) NOT NULL,
  `E_KONTO_ID` int(7) NOT NULL,
  `BETRAG` decimal(10,2) NOT NULL,
  `BETRAGS_ART` enum('Bruttobetrag','Nettobetrag','Skontobetrag') COLLATE utf8mb4_unicode_ci NOT NULL,
  `VZWECK1` varchar(27) COLLATE utf8mb4_unicode_ci NOT NULL,
  `VZWECK2` varchar(27) COLLATE utf8mb4_unicode_ci NOT NULL,
  `VZWECK3` varchar(27) COLLATE utf8mb4_unicode_ci NOT NULL,
  `BUCHUNGSTEXT` mediumtext COLLATE utf8mb4_unicode_ci,
  `ZAHLUNGSART` enum('VOLL','TEIL') COLLATE utf8mb4_unicode_ci NOT NULL,
  `BEZUGSTAB` enum('MIETVERTRAG','RECHNUNG') COLLATE utf8mb4_unicode_ci NOT NULL,
  `BEZUGS_ID` int(7) NOT NULL,
  `AUSZUGSNR` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `AUSZUGS_DATUM` date DEFAULT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`U_DAT`),
  KEY `DATUM` (`DATUM`),
  KEY `DTAUS_ID` (`DTAUS_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `URLAUB` (
  `U_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `BENUTZER_ID` int(7) NOT NULL,
  `ANTRAG_D` date DEFAULT NULL,
  `DATUM` date NOT NULL,
  `ANTEIL` decimal(2,1) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ART` enum('Urlaub','Krank','oK','Auszahlung','Unbezahlt') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`U_DAT`),
  KEY `BENUTZER_ID` (`BENUTZER_ID`),
  KEY `ANTEIL` (`ANTEIL`),
  KEY `DATUM` (`DATUM`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `URLAUB_EINST` (
  `UE_DAT` int(2) NOT NULL AUTO_INCREMENT,
  `DATUM` date NOT NULL,
  `ANTEIL` decimal(2,1) NOT NULL,
  PRIMARY KEY (`UE_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        if (!Schema::hasTable('VERPACKUNGS_E')) {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS `VERPACKUNGS_E` (
  `V_ID` int(7) NOT NULL AUTO_INCREMENT,
  `V_EINHEIT` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `BEZEICHNUNG` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`V_ID`),
  KEY `V_EINHEIT` (`V_EINHEIT`),
  KEY `BEZEICHNUNG` (`BEZEICHNUNG`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=27 ;"
            );

            DB::insert(
                "INSERT INTO `VERPACKUNGS_E` (`V_ID`, `V_EINHEIT`, `BEZEICHNUNG`, `AKTUELL`) VALUES
(1, 'Stk', 'Stück', '1'),
(2, 'Std', 'Stunden', '1'),
(3, 'lfm', 'Meter', '1'),
(4, 'm²', 'm²', '1'),
(5, 'm³', 'm³', '1'),
(6, 'KAN', 'Kanister', '1'),
(7, 'SCK', 'Sack', '1'),
(8, 'kg', 'kg', '1'),
(9, 'VE', 'VE', '1'),
(10, 'l', 'Liter', '1'),
(11, 'ml', 'ml', '1'),
(12, 'Tonne', 'Tonne', '1'),
(13, 'Rolle', 'Rolle', '1'),
(14, 'Dose', 'Dose', '1'),
(15, 'Pak', 'Paket', '1'),
(16, '%', 'Prozent', '1'),
(17, 'Tube', 'Tube', '1'),
(18, 'Kartusche', 'Kartusche', '1'),
(19, 'Pauschale', 'Pauschale', '1'),
(20, 'Paar', 'Paar', '1'),
(21, 'Set', 'Set', ''),
(22, 'Eim.', 'Eimer', '1'),
(23, 'Beu.', 'Beutel', '1'),
(24, 'Tag(e)', 'Tag', '1'),
(26, 'Aufg.', 'Hausaufgang', '1');"
            );
        }

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `WARTUNGEN` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `GERAETE_ID` int(7) NOT NULL,
  `PLAN_ID` int(7) NOT NULL,
  `WARTUNGSDATUM` date DEFAULT NULL,
  `BENUTZER_ID` int(11) NOT NULL,
  `BEMERKUNG` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`),
  KEY `PLAN_ID` (`PLAN_ID`),
  KEY `GERAETE_ID` (`GERAETE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `WARTUNGSPLAN` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `PLAN_ID` int(7) NOT NULL,
  `PLAN_BEZEICHNUNG` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `INTERVALL` int(3) NOT NULL,
  `INTERVALL_PERIOD` enum('DAY','MONTH','YEAR') COLLATE utf8mb4_unicode_ci NOT NULL,
  `GEWERK_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`),
  KEY `PLAN_ID` (`PLAN_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `WEG_EIGENTUEMER_PERSON` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ID` int(7) NOT NULL,
  `WEG_EIG_ID` int(7) NOT NULL,
  `PERSON_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `WEG_HGA_HK` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ID` int(7) NOT NULL,
  `BETRAG` decimal(10,2) NOT NULL,
  `KOS_TYP` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOS_ID` int(7) NOT NULL,
  `WEG_HGA_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `WEG_HGA_PROFIL` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ID` int(7) NOT NULL,
  `BEZEICHNUNG` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `JAHR` int(4) NOT NULL,
  `VON` date DEFAULT NULL,
  `BIS` date DEFAULT NULL,
  `OBJEKT_ID` int(7) NOT NULL,
  `GELDKONTO_ID` int(7) NOT NULL,
  `IHR_GK_ID` int(7) NOT NULL,
  `WPLAN_ID` int(7) NOT NULL,
  `HG_KONTO` int(10) NOT NULL,
  `HK_KONTO` int(10) NOT NULL,
  `IHR_KONTO` int(10) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `WEG_HGA_ZEILEN` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ID` int(7) NOT NULL,
  `WEG_HG_P_ID` int(1) NOT NULL,
  `KONTO` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ART` enum('Ausgaben/Einnahmen','Mittelverwendung','Verrechnung') COLLATE utf8mb4_unicode_ci NOT NULL,
  `TEXT` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `GEN_KEY_ID` int(7) NOT NULL,
  `BETRAG` decimal(10,2) NOT NULL,
  `HNDL_BETRAG` decimal(10,2) NOT NULL,
  `KOS_TYP` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOS_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `WEG_HG_ZAHLUNGEN` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ID` int(7) NOT NULL,
  `BUCHUNGS_DAT` int(7) DEFAULT NULL,
  `BUCHUNGS_SUMME` decimal(10,2) NOT NULL,
  `KOSTENKONTO` int(10) NOT NULL,
  `KOS_TYP` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOS_ID` int(7) NOT NULL,
  `WEG_HGA_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `WEG_IHR_III` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ID` int(7) NOT NULL,
  `KONTENRAHMEN_KONTO` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `IHR_GK_ID` int(7) NOT NULL,
  `DATUM` date NOT NULL,
  `BETRAG` decimal(10,2) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `WEG_KONTOSTAND` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ID` int(7) NOT NULL,
  `GK_ID` int(7) NOT NULL,
  `DATUM` date NOT NULL,
  `BETRAG` decimal(10,2) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `WEG_MITEIGENTUEMER` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ID` int(7) NOT NULL,
  `EINHEIT_ID` int(7) NOT NULL,
  `VON` date NOT NULL,
  `BIS` date NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `WEG_WG_DEF` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ID` int(7) NOT NULL,
  `ANFANG` date NOT NULL,
  `ENDE` date NOT NULL,
  `BETRAG` decimal(10,2) NOT NULL,
  `KOSTENKAT` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `E_KONTO` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `GRUPPE` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `G_KONTO` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOS_TYP` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOS_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `WEG_WPLAN` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `PLAN_ID` int(7) NOT NULL,
  `JAHR` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `OBJEKT_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `WEG_WPLAN_ZEILEN` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ID` int(7) NOT NULL,
  `WPLAN_ID` int(7) NOT NULL,
  `KOSTENKONTO` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `BETRAG` decimal(10,2) NOT NULL,
  `BETRAG_VJ` decimal(10,2) NOT NULL,
  `FORMEL` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `WIRT_ID` int(7) DEFAULT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `WERKZEUGE` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ID` int(7) NOT NULL,
  `BELEG_ID` int(7) NOT NULL,
  `POS` int(11) NOT NULL,
  `ARTIKEL_NR` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MENGE` decimal(10,2) NOT NULL,
  `KURZINFO` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `BENUTZER_ID` int(7) DEFAULT NULL,
  `DATUM` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `WIRT_EINHEITEN` (
  `W_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `W_ID` int(7) NOT NULL,
  `W_NAME` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`W_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `WIRT_EIN_TAB` (
  `WZ_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `WZ_ID` int(7) NOT NULL,
  `W_ID` int(7) NOT NULL,
  `EINHEIT_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`WZ_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `W_GERAETE` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `GERAETE_ID` int(7) NOT NULL,
  `GRUPPE_ID` int(7) NOT NULL,
  `BEZEICHNUNG` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `HERSTELLER` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `BAUJAHR` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `LAGE_RAUM` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `KOSTENTRAEGER_TYP` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KOSTENTRAEGER_ID` int(7) NOT NULL,
  `RECHNUNG_AN` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `INTERVAL_M` int(3) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`),
  KEY `GERAETE_ID` (`GERAETE_ID`),
  KEY `GRUPPE_ID` (`GRUPPE_ID`),
  KEY `KOSTENTRAEGER_TYP` (`KOSTENTRAEGER_TYP`),
  KEY `KOSTENTRAEGER_ID` (`KOSTENTRAEGER_ID`),
  KEY `AKTUELL` (`AKTUELL`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `W_GRUPPE` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `GRUPPE_ID` int(7) NOT NULL,
  `GRUPPE` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TEAM_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`),
  KEY `GRUPPE_ID` (`GRUPPE_ID`),
  KEY `GRUPPE` (`GRUPPE`),
  KEY `TEAM_ID` (`TEAM_ID`),
  KEY `AKTUELL` (`AKTUELL`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `W_TEAMS` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `TEAM_ID` int(7) NOT NULL,
  `TEAM_BEZ` varchar(50) NOT NULL,
  `AKTUELL` enum('0','1') NOT NULL,
  PRIMARY KEY (`DAT`),
  KEY `TEAM_ID` (`TEAM_ID`),
  KEY `AKTUELL` (`AKTUELL`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `W_TEAMS_BENUTZER` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ID` int(7) NOT NULL,
  `TEAM_ID` int(7) NOT NULL,
  `BENUTZER_ID` int(7) NOT NULL,
  `AKTUELL` enum('0','1') NOT NULL,
  PRIMARY KEY (`DAT`),
  KEY `TEAM_ID` (`TEAM_ID`),
  KEY `BENUTZER_ID` (`BENUTZER_ID`),
  KEY `AKTUELL` (`AKTUELL`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `W_TEAM_PROFILE` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `ID` int(7) NOT NULL,
  `BENUTZER_ID` int(11) NOT NULL,
  `1` enum('0','1') NOT NULL,
  `2` enum('0','1') NOT NULL,
  `3` enum('0','1') NOT NULL,
  `4` enum('0','1') NOT NULL,
  `5` enum('0','1') NOT NULL,
  `6` enum('0','1') NOT NULL,
  `7` enum('0','1') NOT NULL,
  `VON` time NOT NULL,
  `BIS` time NOT NULL,
  `TERMINE_TAG` int(2) NOT NULL,
  `START_ADRESSE` varchar(100) DEFAULT NULL,
  `AKTIV` enum('0','1') NOT NULL,
  `AKTUELL` enum('0','1') NOT NULL,
  PRIMARY KEY (`DAT`),
  KEY `1` (`1`),
  KEY `2` (`2`),
  KEY `4` (`4`),
  KEY `5` (`5`),
  KEY `3` (`3`),
  KEY `6` (`6`),
  KEY `7` (`7`),
  KEY `AKTUELL` (`AKTUELL`),
  KEY `AKTIV` (`AKTIV`),
  KEY `BENUTZER_ID` (`BENUTZER_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `W_TERMINE` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `PLAN_ID` int(7) NOT NULL,
  `DATUM_FAELLIG` date NOT NULL,
  `TERMIN` datetime NOT NULL,
  `DAUER_MIN` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `GERAETE_ID` int(7) NOT NULL,
  `BENUTZER_ID` int(7) NOT NULL,
  `ABGESAGT` enum('0','1') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ABGESAGT_AM` date DEFAULT NULL,
  `ABGESAGT_VON` enum('Kunde','Wartungsfirma') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `GRUND` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `ABSAGE_AUFGENOMMEN` int(7) DEFAULT NULL,
  `ABSAGE_RECHTZEITIG` enum('0','1') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS `ZUGRIFF_ERROR` (
  `Z_DAT` int(7) NOT NULL AUTO_INCREMENT,
  `BENUTZER_ID` int(7) NOT NULL,
  `NAME` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ZEIT` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `MODUL_NAME` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `IP` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `HOST` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Z_DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;"
        );

        DB::statement(
            "CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `Thermen in 6 Monaten` AS select `W_GERAETE`.`GERAETE_ID` AS `GERAETE_ID`,`W_GERAETE`.`LAGE_RAUM` AS `EINBAUORT`,`W_GERAETE`.`HERSTELLER` AS `HERSTELLER`,`W_GERAETE`.`BEZEICHNUNG` AS `BEZEICHNUNG`,`W_GERAETE`.`KOSTENTRAEGER_TYP` AS `KOSTENTRAEGER_TYP`,`W_GERAETE`.`KOSTENTRAEGER_ID` AS `KOSTENTRAEGER_ID`,`W_GERAETE`.`INTERVAL_M` AS `INTERVAL_M`,date_format(now(),'%Y-%m-%d') AS `HEUTE`,date_format((date_format(now(),'%Y-%m-%d') + interval -(`W_GERAETE`.`INTERVAL_M`) month),'%Y-%m-%d') AS `L_WART_FAELLIG`,(select `GEO_TERMINE`.`DATUM` from `GEO_TERMINE` where ((`GEO_TERMINE`.`GERAETE_ID` = `W_GERAETE`.`GERAETE_ID`) and (`GEO_TERMINE`.`AKTUELL` = '1')) order by `GEO_TERMINE`.`DATUM` desc limit 0,1) AS `L_WART` from `W_GERAETE` where ((`W_GERAETE`.`AKTUELL` = '1') and (`W_GERAETE`.`GRUPPE_ID` = '1') and (not(`W_GERAETE`.`GERAETE_ID` in (select `GEO_TERMINE`.`GERAETE_ID` from `GEO_TERMINE` where ((`GEO_TERMINE`.`AKTUELL` = '1') and (`GEO_TERMINE`.`DATUM` >= date_format((date_format(now(),'%Y-%m-%d') + interval -((`W_GERAETE`.`INTERVAL_M` - 6)) month),'%Y-%m-%d')) and (`GEO_TERMINE`.`DATUM` <= date_format(now(),'%Y-%m-%d')))))) and (not(`W_GERAETE`.`GERAETE_ID` in (select `GEO_TERMINE`.`GERAETE_ID` from `GEO_TERMINE` where ((`GEO_TERMINE`.`AKTUELL` = '1') and (`GEO_TERMINE`.`DATUM` > date_format(now(),'%Y-%m-%d'))) group by `GEO_TERMINE`.`GERAETE_ID`)))) order by `W_GERAETE`.`KOSTENTRAEGER_TYP`,`W_GERAETE`.`KOSTENTRAEGER_ID`,`W_GERAETE`.`LAGE_RAUM`;"
        );

        DB::statement(
            "CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `Thermen in 9 Monaten` AS select `W_GERAETE`.`GERAETE_ID` AS `GERAETE_ID`,`W_GERAETE`.`LAGE_RAUM` AS `EINBAUORT`,`W_GERAETE`.`HERSTELLER` AS `HERSTELLER`,`W_GERAETE`.`BEZEICHNUNG` AS `BEZEICHNUNG`,`W_GERAETE`.`KOSTENTRAEGER_TYP` AS `KOSTENTRAEGER_TYP`,`W_GERAETE`.`KOSTENTRAEGER_ID` AS `KOSTENTRAEGER_ID`,`W_GERAETE`.`INTERVAL_M` AS `INTERVAL_M`,date_format(now(),'%Y-%m-%d') AS `HEUTE`,date_format((date_format(now(),'%Y-%m-%d') + interval -(`W_GERAETE`.`INTERVAL_M`) month),'%Y-%m-%d') AS `L_WART_FAELLIG`,(select `GEO_TERMINE`.`DATUM` from `GEO_TERMINE` where ((`GEO_TERMINE`.`GERAETE_ID` = `W_GERAETE`.`GERAETE_ID`) and (`GEO_TERMINE`.`AKTUELL` = '1')) order by `GEO_TERMINE`.`DATUM` desc limit 0,1) AS `L_WART` from `W_GERAETE` where ((`W_GERAETE`.`AKTUELL` = '1') and (`W_GERAETE`.`GRUPPE_ID` = '1') and (not(`W_GERAETE`.`GERAETE_ID` in (select `GEO_TERMINE`.`GERAETE_ID` from `GEO_TERMINE` where ((`GEO_TERMINE`.`AKTUELL` = '1') and (`GEO_TERMINE`.`DATUM` >= date_format((date_format(now(),'%Y-%m-%d') + interval -((`W_GERAETE`.`INTERVAL_M` - 3)) month),'%Y-%m-%d')) and (`GEO_TERMINE`.`DATUM` <= date_format(now(),'%Y-%m-%d')))))) and (not(`W_GERAETE`.`GERAETE_ID` in (select `GEO_TERMINE`.`GERAETE_ID` from `GEO_TERMINE` where ((`GEO_TERMINE`.`AKTUELL` = '1') and (`GEO_TERMINE`.`DATUM` > date_format(now(),'%Y-%m-%d'))) group by `GEO_TERMINE`.`GERAETE_ID`)))) order by `W_GERAETE`.`KOSTENTRAEGER_TYP`,`W_GERAETE`.`KOSTENTRAEGER_ID`,`W_GERAETE`.`LAGE_RAUM`;"
        );

        DB::statement(
            "CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `UNI_ARTIKELL_PREIS` AS select `RECHNUNGEN_POSITIONEN`.`ARTIKEL_NR` AS `ARTIKEL_NR`,`RECHNUNGEN_POSITIONEN`.`MENGE` AS `MENGE`,`RECHNUNGEN_POSITIONEN`.`PREIS` AS `PREIS`,`RECHNUNGEN_POSITIONEN`.`RABATT_SATZ` AS `RABATT_SATZ` from `RECHNUNGEN_POSITIONEN` where `RECHNUNGEN_POSITIONEN`.`BELEG_NR` in (select `RECHNUNGEN`.`BELEG_NR` AS `BELEG_NR` from `RECHNUNGEN` where ((`RECHNUNGEN`.`AUSSTELLER_TYP` like 'Partner') and (`RECHNUNGEN`.`AUSSTELLER_ID` = '205') and (`RECHNUNGEN`.`AKTUELL` = '1'))) group by `RECHNUNGEN_POSITIONEN`.`PREIS` order by `RECHNUNGEN_POSITIONEN`.`ARTIKEL_NR`,`RECHNUNGEN_POSITIONEN`.`PREIS`;"
        );

        DB::statement(
            "CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `W_GERAETE doppelt` AS select count(`W_GERAETE`.`LAGE_RAUM`) AS `COUNT(LAGE_RAUM)`,`W_GERAETE`.`LAGE_RAUM` AS `LAGE_RAUM`,`PARTNER_LIEFERANT`.`PARTNER_NAME` AS `PARTNER_NAME` from (`W_GERAETE` join `PARTNER_LIEFERANT`) where ((`PARTNER_LIEFERANT`.`AKTUELL` = '1') and (`W_GERAETE`.`AKTUELL` = '1') and (`PARTNER_LIEFERANT`.`PARTNER_ID` = `W_GERAETE`.`KOSTENTRAEGER_ID`)) group by `W_GERAETE`.`KOSTENTRAEGER_TYP`,`W_GERAETE`.`KOSTENTRAEGER_ID`,`W_GERAETE`.`LAGE_RAUM` order by count(`W_GERAETE`.`LAGE_RAUM`) desc;"
        );

        if (!Schema::hasTable('KAUTION_FELD')) {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS `KAUTION_FELD` (
  `DAT` int(7) NOT NULL AUTO_INCREMENT,
  `FELD` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `AKTUELL` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`DAT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
            );

            DB::insert(
                "INSERT INTO KAUTION_FELD (FELD, AKTUELL) VALUES 
('SOLL', '1'),
('IST', '1'),
('Kautionsart', '1'),
('Kontonummer', '1'),
('Bank', '1'),
('Bemerkungen', '1') ;"
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
