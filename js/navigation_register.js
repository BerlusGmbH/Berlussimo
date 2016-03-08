tree.push({
    text: "Kassen",
    href: "?daten=kasse",
    selectable: false,
    nodes: [
        {
            text: "Kassenbuch",
            href: "?daten=kasse&option=kassenbuch"
        },
        {
            text: "Ausgaben erfassen",
            href: "?daten=kasse&option=rechnung_an_kasse_erfassen"
        },
        {
            text: "E/A buchen",
            href: "?daten=kasse&option=buchungsmaske_kasse"
        }
    ]
});