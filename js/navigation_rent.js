tree.push({
    text: "Miete",
    href: "?daten=mietkonten_blatt",
    selectable: false,
    nodes: [
        {
            text: "Miethöhe definieren",
            href: "?daten=miete_definieren"
        },
        {
            text: "Mieterliste MOD",
            href: "?daten=miete_definieren&option=mieterlisten_kostenkat&kostenkat=MOD"
        },
        {
            text: "Untermieterliste",
            href: "?daten=miete_definieren&option=mieterlisten_kostenkat&kostenkat=Untermieter Zuschlag"
        },
        {
            text: "SEPA Autobuchen",
            href: "?daten=sepa&option=ls_auto_buchen"
        }
    ]
});