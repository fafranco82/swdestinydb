(function app_deck_charts(deck_charts, $) {

deck_charts.chart_types = function chart_types() {

	var data = _.map(['upgrade', 'support', 'event'], function(type_code) {
		var cards = app.deck.get_cards(null, {type_code: type_code});
		return {
			name: cards[0].type_name,
			label: '<span class="icon icon-'+type_code+'"></span>',
			y: app.deck.get_nb_cards(cards)
		}
	});

	$("#deck-chart-types").highcharts({
		chart: {
			type: 'column'
		},
		title: {
            text: "Cards by Type"
        },
		subtitle: {
            text: "nanoniaaaaaaaa"
        },
		xAxis: {
			categories: _.map(data, 'label'),
			labels: {
				useHTML: true
			},
			title: {
				text: null
			}
		},
		yAxis: {
			min: 0,
			allowDecimals: false,
			tickInterval: 2,
			title: null,
			labels: {
				overflow: 'justify'
			}
		},
		tooltip: {
			//headerFormat: '<span style="font-size: 10px">{point.key} Icon</span><br/>'
			headerFormat: '<span style="font-size: 10px">{point.key}</span><br/>'
		},
		series: [{
			type: "column",
			animation: false,
			name: 'Hola',
			showInLegend: false,
			data: data
		}],
		plotOptions: {
			column: {
				borderWidth: 0,
				groupPadding: 0,
				shadow: false
			}
		}
	});
}

deck_charts.setup = function setup(options) {
	deck_charts.chart_types();
}

$(document).on('shown.bs.tab', 'a[data-toggle=tab]', function (e) {
	deck_charts.setup();
});

})(app.deck_charts = {}, jQuery);
