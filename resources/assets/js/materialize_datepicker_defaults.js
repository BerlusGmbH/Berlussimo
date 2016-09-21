/**
 * The date picker defaults.
 */
$.extend($.fn.pickadate.defaults, {
    // The title label to use for the month nav buttons
    labelMonthNext: 'Nächster Monat',
    labelMonthPrev: 'Vorheriger Monat',

    // The title label to use for the dropdown selectors
    labelMonthSelect: 'Monat wählen',
    labelYearSelect: 'Jahr wählen',

    // Months and weekdays
    monthsFull: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
    monthsShort: ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
    weekdaysFull: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
    weekdaysShort: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],

    // Materialize modified
    weekdaysLetter: ['S', 'M', 'D', 'M', 'D', 'F', 'S'],

    // Today and clear
    today: 'Heute',
    clear: 'Entf',
    close: 'Fertig',

    // The format to show on the `input` element
    format: 'dd.mm.yyyy'
});