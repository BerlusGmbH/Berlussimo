var tree = [];

$(document).ready(function () {
    $('[data-toggle=offcanvas]').click(function () {
        $('.row-offcanvas').toggleClass('active');
    });
    var treeview = $('#tree');
    treeview.treeview({
        data: tree, enableLinks: true, collapseIcon: 'glyphicon glyphicon-chevron-down',
        expandIcon: 'glyphicon glyphicon-chevron-right', levels: 0, selectedBackColor: '#4f2514'
    });
    var dataParameter = getURLParameter('daten');
    dataParameter = dataParameter === undefined ? getURLParameter('formular') : dataParameter;
    var optionParameter = getURLParameter('option');
    optionParameter = optionParameter === undefined ? getURLParameter('objekte_raus') : optionParameter;
    optionParameter = optionParameter === undefined ? getURLParameter('haus_raus') : optionParameter;
    optionParameter = optionParameter === undefined ? getURLParameter('einheit_raus') : optionParameter;
    optionParameter = optionParameter === undefined ? getURLParameter('mietvertrag_raus') : optionParameter;
    optionParameter = optionParameter === undefined ? getURLParameter('anzeigen') : optionParameter;
    optionParameter = optionParameter === undefined ? getURLParameter('daten_rein') : optionParameter;
    var node;
    if (dataParameter !== undefined) {
        treeview.treeview('getSiblings', '0').some(function (vo, io, ao) {
            if (getURLParameter('daten', vo.href) === dataParameter ||
                getURLParameter('formular', vo.href) === dataParameter
            ) {
                vo.nodes.some(function (vi, ii, ai) {
                    if (getURLParameter('option', vi.href) === optionParameter ||
                        getURLParameter('objekte_raus', vi.href) === optionParameter ||
                        getURLParameter('haus_raus', vi.href) === optionParameter ||
                        getURLParameter('einheit_raus', vi.href) === optionParameter ||
                        getURLParameter('mietvertrag_raus', vi.href) === optionParameter ||
                        getURLParameter('anzeigen', vi.href) === optionParameter ||
                        getURLParameter('daten_rein', vi.href) === optionParameter
                    ) {
                        node = vi;
                        treeview.treeview('selectNode', node);
                        treeview.treeview('revealNode', node);
                        return true;
                    }
                });
            }
        });
    }
    treeview.on('nodeSelected', function (event, data) {
        window.location.href = data.href;
    });
    treeview.on('nodeUnselected', function (event, data) {
        //treeview.treeview('selectNode', [data.id, {silent: true}]);
    });
});

function getMonthFromDate(d) {
    var mm = d.getMonth() + 1;
    if (mm < 10)
        mm = '0' + mm;
    return mm;
}

function getURLParameter(sParam, sPageURL) {
    if (sPageURL === null || sPageURL === undefined) {
        sPageURL = window.location.search.substring(1);
    } else {
        sPageURL = sPageURL.substring(1);
    }
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++) {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam) {
            return sParameterName[1];
        }
    }
}
