tree.push({
    text: "WEG",
    href: "?daten=weg",
    selectable: false,
    nodes: [
        {
            text: "Objekte",
            href: "?daten=weg&option=objekt_auswahl"
        },
        {
            text: "Stammdaten als PDF",
            href: "?daten=weg&option=stammdaten_weg&lang=en"
        },
        {
            text: "Hausgeld buchen",
            href: "?daten=weg&option=wohngeld_buchen_auswahl_e"
        },
        {
            text: "Kosten buchen",
            href: "?daten=buchen&option=zahlbetrag_buchen"
        },
        {
            text: "Einheiten",
            href: "?daten=weg&option=einheiten"
        },
        {
            text: "Eigentümerwechsel",
            href: "?daten=weg&option=eigentuemer_wechsel"
        },
        {
            text: "Erledigte Pojekte",
            href: "?daten=todo&option=erledigte_projekte"
        },
        {
            text: "Mahnliste",
            href: "daten=weg&option=mahnliste"
        },
        {
            text: "Wirtschaftspläne",
            href: "?daten=weg&option=wpliste"
        },
        {
            text: "Neuer Wirtschaftsplan",
            href: "?daten=weg&option=wp_neu"
        },
        {
            text: "HGA-Assistent",
            href: "?daten=weg&option=assistent"
        },
        {
            text: "HGA-Profile",
            href: "?daten=weg&option=hga_profile"
        },
        {
            text: "HK-Verbrauch",
            href: "?daten=weg&option=hk_verbrauch_tab"
        },
        {
            text: "Kontostand erfassen",
            href: "?daten=weg&option=kontostand_erfassen"
        },
        {
            text: "HGA-Gesamtabrechnung als PDF",
            href: "?daten=weg&option=hga_gesamt_pdf"
        },
        {
            text: "IHR",
            href: "?daten=weg&option=ihr"
        },
        {
            text: "IHR als PDF",
            href: "?daten=weg&option=pdf_ihr"
        },
        {
            text: "HGA-Einzelabrechnung als PDF",
            href: "?daten=weg&option=hga_einzeln"
        },
        {
            text: "Serienbrief",
            href: "?daten=weg&option=serienbrief"
        },
        {
            text: "Kontenübersicht " + new Date().getFullYear(),
            href: "?daten=weg&option=hausgeld_zahlungen&jahr=" + new Date().getFullYear()
        },
        {
            text: "Kontenübersicht" + (new Date().getFullYear() - 1) + " als XLS",
            href: "?daten=weg&option=hausgeld_zahlungen_xls&jahr=" + (new Date().getFullYear() - 1)
        },
        {
            text: "Hausgelder",
            href: "?daten=weg&option=pdf_hausgelder"
        }
    ]
});