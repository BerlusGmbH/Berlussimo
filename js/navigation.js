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
    var node;
    var matches = 0;
    treeview.treeview('getSiblings', '0').some(function (vo) {
        try {
            var currentMatches = compareByParameters(vo.href);
            if ( matches < currentMatches ) {
                node = vo;
                matches = currentMatches;
            }
            if (vo.nodes !== undefined) {
                vo.nodes.some(function (vi) {
                    var currentMatches = compareByParameters(vi.href);
                    if (matches < currentMatches) {
                        node = vi;
                        matches = currentMatches;
                    }
                });
            }
        } catch (e) {
            console.error(e);
        }
    });
    if (node !== undefined) {
        treeview.treeview('selectNode', node);
        treeview.treeview('revealNode', node);
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

function compareByParameters(url) {
    var currentUrl = window.location.search.substring(1);
    var currentParametersString = currentUrl.split('&');
    var parameters = {};
    for (var i = 0; i < currentParametersString.length; i++) {
        var parameter = currentParametersString[i].split('=');
        parameters[parameter[0]] = parameter[1];
    }
    var matches = 0;
    var parametersString = url.substring(1).split('&');
    for (i = 0; i < parametersString.length; i++) {
        parameter = parametersString[i].split('=');
        if (parameter[0] in parameters && parameters[parameter[0]] === parameter[1]) {
            matches++;
        }
    }
    return matches;
}
