tree.push({
    text: "Mietverträge",
    href: "?daten=mietvertrag_raus",
    selectable: false,
    nodes: [
        {
            text: "Alle",
            href: "?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_kurz"
        },
        {
            text: "Aktuelle",
            href: "?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_aktuelle"
        },
        {
            text: "Abgelaufene",
            href: "?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_abgelaufen"
        },
        {
            text: "Neu",
            href: "?daten=mietvertrag_raus&mietvertrag_raus=mietvertrag_neu"
        },
        {
            text: "Letzte Auszüge Objekt",
            href: "?daten=mietvertrag_raus&mietvertrag_raus=letzte_auszuege"
        },
        {
            text: "Letzte Einzüge Objekt",
            href: "?daten=mietvertrag_raus&mietvertrag_raus=letzte_einzuege"
        },
        {
            text: "Alle Auszüge",
            href: "?daten=mietvertrag_raus&mietvertrag_raus=alle_letzten_auszuege&monat=" + getMonthFromDate(new Date()) + "&jahr=" + new Date().getFullYear()
        },
        {
            text: "Alle Einzüge",
            href: "?daten=mietvertrag_raus&mietvertrag_raus=alle_letzten_auszuege&monat=" + getMonthFromDate(new Date()) + "&jahr=" + new Date().getFullYear()
        },
        {
            text: "Mahnliste Alle",
            href: "?daten=mietvertrag_raus&mietvertrag_raus=mahnliste_alle"
        },
        {
            text: "Mahnliste Aktuell",
            href: "?daten=mietvertrag_raus&mietvertrag_raus=mahnliste"
        },
        {
            text: "Mahnliste ehemalige Mieter",
            href: "?daten=mietvertrag_raus&mietvertrag_raus=mahnliste_ausgezogene"
        },
        {
            text: "Guthaben",
            href: "?daten=mietvertrag_raus&mietvertrag_raus=guthaben_liste"
        },
        {
            text: "Saldenlisten",
            href: "?daten=mietvertrag_raus&mietvertrag_raus=saldenliste"
        },
        {
            text: "Nebenkosten/Jahr",
            href: "?daten=mietvertrag_raus&mietvertrag_raus=nebenkosten"
        },
        {
            text: "NK/KM/Jahr mit ZS",
            href: "?daten=mietvertrag_raus&mietvertrag_raus=nebenkosten_pdf_zs&jahr=" + (new Date().getFullYear() - 1)
        },
        {
            text: "NK/KM/Jahr mit ZS als XLS",
            href: "?daten=mietvertrag_raus&mietvertrag_raus=nebenkosten_pdf_zs&jahr=" + (new Date().getFullYear() - 1) + "&xls"
        }
    ]
});