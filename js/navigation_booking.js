tree.push({
    text: "Buchen",
    href: "?daten=buchen",
    selectable: false,
    nodes: [
        {
            text: "Miete buchen",
            href: "?daten=miete_buchen"
        },
        {
            text: "Kosten buchen",
            href: "?daten=buchen&option=zahlbetrag_buchen"
        },
        {
            text: "RA buchen",
            href: "?daten=buchen&option=ausgangsbuch_kurz"
        },
        {
            text: "RE buchen",
            href: "?daten=buchen&option=eingangsbuch_kurz&anzeige=empfaenger_eingangs_rnr"
        },
        {
            text: "Profil zurücksetzen",
            href: "?daten=bk&option=profil_reset"
        },
        {
            text: "Buchungsjournal",
            href: "?daten=buchen&option=buchungs_journal"
        },
        {
            text: "Buchungsjournal " + (new Date()).getFullYear() + " PDF",
            href: "?daten=buchen&option=buchungs_journal_jahr_pdf&jahr=" + new Date().getFullYear()
        },
        {
            text: "Buchingsjournal " + (new Date().getFullYear() - 1) + " XLS",
            href: "?daten=buchen&option=buchungs_journal_jahr_pdf&jahr=" + (new Date().getFullYear() - 1) + "&xls"
        },
        {
            text: "Kontenübersicht",
            href: "?daten=buchen&option=konten_uebersicht"
        },
        {
            text: "Kontoübersicht",
            href: "?daten=buchen&option=konto_uebersicht"
        },
        {
            text: "Buchungen zu Kostenkonto",
            href: "?daten=buchen&option=buchungen_zu_kostenkonto"
        },
        {
            text: "Monatsbericht ohne Auszug",
            href: "?daten=buchen&option=monatsbericht_o_a"
        },
        {
            text: "Monatsbericht mit Auszug",
            href: "?daten=buchen&option=monatsbericht_m_a"
        },
        {
            text: "Kosten & Einnahmen",
            href: "?daten=buchen&option=kosten_einnahmen"
        },
        {
            text: "Buchung suchen",
            href: "?daten=buchen&option=buchung_suchen"
        },
        {
            text: "Kostenkonto PDF",
            href: "?daten=buchen&option=kostenkonto_pdf"
        },
        {
            text: "EXCEL Upload",
            href: "?daten=buchen&option=excel_buchen&upload"
        },
        {
            text: "Exceldaten verbuchen",
            href: "?daten=buchen&option=excel_buchen_session"
        },
        {
            text: "Excelkonten",
            href: "?daten=buchen&option=uebersicht_excel_konten"
        },
        {
            text: "Buchungskonten summiert XLS",
            href: "?daten=buchen&option=buchungskonto_summiert_xls&jahr=" + (new Date().getFullYear() - 1)
        }
    ]
});