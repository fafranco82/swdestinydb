(function app_deck_charts(deck_charts, $) {

	var faction_colors = {
        red: '#b22222',
        yellow: '#dab032',
        blue: '#0b609e',
        gray: '#979d9f'
    };

	deck_charts.chart_type = function chart_type() {

        var categories = {
            'Upgrade': '<span class="icon icon-upgrade"></span>',
            'Support': '<span class="icon icon-support"></span>',
            'Event': '<span class="icon icon-event"></span>'
        };

        var iData = {
            'upgrade': { i: 0, name: Translator.trans('icon.upgrade') },
            'support': { i: 1, name: Translator.trans('icon.support') },
            'event': { i: 2, name: Translator.trans('icon.event') }
        };

        var validTypes = {};
        var validIndexes = {};

        var series = [];
        var iSeries = {};

        var draw_deck = app.deck.get_draw_deck();
        draw_deck.forEach(function(card) {
            var serie;

            if (!iSeries[card.faction_code]) {
                serie = {
                    name: card.faction_name,
                    color: faction_colors[card.faction_code],
                    data: [0, 0, 0, 0, 0],
                    type: "column",
                    animation: false,
                    showInLegend: false
                };
                iSeries[card.faction_code] = serie;
                series.push(serie);
            } else {
                serie = iSeries[card.faction_code];
            }

            var d = iData[card.type_code];
            if (d !== undefined) {
                validTypes[d.name] = true;
                validIndexes[d.i] = true;
                serie.data[d.i] += card.indeck.cards;
            }
        });

        categories = _.omit(categories, function(value, key) {
            return !validTypes[key];
        });

        _.each(series, function(serie) {
            serie.data = _.filter(serie.data, function(value, index) {
                return validIndexes[index];
            });
        });

        $("#deck-chart-type").highcharts({
            chart: {
                type: 'column'
            },
            title: {
                text: "Card Types"
            },
            subtitle: {
                text: ""
            },
            xAxis: {
                type: 'category',
                categories: _.keys(categories),
                labels: {
                    useHTML: true,
                    formatter: function() {
                        return categories[this.value];
                    }
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
                headerFormat: '<b>{point.x}</b><br/>',
                pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
            },
            //tooltip: {
            //    headerFormat: '<span style="font-size: 10px">{point.key}</span><br/>'
            //},
            series: series,
            plotOptions: {
                column: {
                    stacking: 'normal',
                    borderWidth: 0,
                    groupPadding: 0,
                    shadow: false
                }
            }
        });
    };

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
	};


	deck_charts.setup = function setup(options) {
		deck_charts.chart_type();
		deck_charts.chart_cost();
	}

	$(document).on('shown.bs.tab', 'a[data-toggle=tab]', function (e) {
		deck_charts.setup();
	});

})(app.deck_charts = {}, jQuery);
