(function app_deck_charts(deck_charts, $) {

deck_charts.chart_cost = function chart_cost() {

		var data = [];

		var draw_deck = app.deck.get_draw_deck();
		draw_deck.forEach(function (card) {
			if(typeof card.cost === 'number') {
				data[card.cost] = data[card.cost] || 0;
				data[card.cost] += card.indeck.cards;
			}
		})
		data = _.flatten(data).map(function (value) { return value || 0; });

		$("#deck-chart-cost").highcharts({
			chart: {
				type: 'line'
			},
			title: {
	            text: Translator.trans("decks.charts.cost.title")
	        },
			subtitle: {
	            text: Translator.trans("decks.charts.cost.subtitle")
	        },
			xAxis: {
				allowDecimals: false,
				tickInterval: 1,
				title: {
					text: null
				}
			},
			yAxis: {
				min: 0,
				allowDecimals: false,
				tickInterval: 1,
				title: null,
				labels: {
					overflow: 'justify'
				}
			},
			tooltip: {
				headerFormat: '<span style="font-size: 10px">'+Translator.trans('decks.charts.cost.tooltip.header', {cost: '{point.key}'})+'</span><br/>'
			},
			series: [{
				animation: false,
				name: Translator.trans('decks.charts.cost.tooltip.label'),
				showInLegend: false,
				data: data
			}]
		});
}

deck_charts.setup = function setup(options) {
	deck_charts.chart_cost();
}

$(document).on('shown.bs.tab', 'a[data-toggle=tab]', function (e) {
	deck_charts.setup();
});

})(app.deck_charts = {}, jQuery);
