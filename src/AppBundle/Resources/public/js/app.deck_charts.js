(function app_deck_charts(deck_charts, $) {

var charts = [],
	faction_colors = {
		targaryen :
			'#1c1c1c',

		baratheon :
			'#e3d852',

		stark :
			'#cfcfcf',

		greyjoy :
			'#1d7a99',

		lannister :
			'#c00106',

		tyrell :
			'#509f16',

		thenightswatch :
			'#6e6e6e',

		martell :
			'#e89521',

		neutral :
			'#a99560',
	};

deck_charts.chart_faction = function chart_faction() {
	var factions = {};
	var draw_deck = app.deck.get_draw_deck();
	draw_deck.forEach(function (card) {
		if(!factions[card.faction_code]) factions[card.faction_code] = { code: card.faction_code, name: card.faction_name, count: 0};
		factions[card.faction_code].count += card.indeck;
	})

	var data = [];
	_.each(_.values(factions), function (faction) {
		data.push({
			name: faction.name,
			label: '<span class="icon icon-'+faction.code+'"></span>',
			color: faction_colors[faction.code],
			y: faction.count
		});
	})

	$("#deck-chart-faction").highcharts({
		chart: {
            type: 'column'
        },
		title: {
            text: Translator.trans("decks.charts.faction.title")
        },
		subtitle: {
            text: Translator.trans("decks.charts.faction.subtitle")
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
			tickInterval: 3,
            title: null,
            labels: {
                overflow: 'justify'
            }
        },
        series: [{
			type: "column",
			animation: false,
            name: Translator.trans("decks.charts.faction.label"),
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

deck_charts.chart_icon = function chart_icon() {

	var data = [{
		name: Translator.trans('challenges.military'),
		label: '<span class="icon icon-military"></span>',
		color: '#c8232a',
		y: 0
	}, {
		name: Translator.trans('challenges.intrigue'),
		label: '<span class="icon icon-intrigue"></span>',
		color: '#13522f',
		y: 0
	}, {
		name: Translator.trans('challenges.power'),
		label: '<span class="icon icon-power"></span>',
		color: '#292e5f',
		y: 0
	}];

	var draw_deck = app.deck.get_draw_deck();
	draw_deck.forEach(function (card) {
		if(card.is_military) data[0].y += (card.is_unique ? 1 : card.indeck);
		if(card.is_intrigue) data[1].y += (card.is_unique ? 1 : card.indeck);
		if(card.is_power) data[2].y += (card.is_unique ? 1 : card.indeck);
	})

	$("#deck-chart-icon").highcharts({
		chart: {
			type: 'column'
		},
		title: {
            text: Translator.trans("decks.charts.icon.title")
        },
		subtitle: {
            text: Translator.trans("decks.charts.icon.subtitle")
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
			headerFormat: '<span style="font-size: 10px">'+Translator.trans('decks.charts.icon.tooltip.header', {type: '{point.key}'})+'</span><br/>'
		},
		series: [{
			type: "column",
			animation: false,
			name: Translator.trans('decks.charts.icon.tooltip.label'),
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

deck_charts.chart_strength = function chart_strength() {

		var data = [];

		var draw_deck = app.deck.get_draw_deck();
		draw_deck.forEach(function (card) {
			if(typeof card.strength === 'number') {
				data[card.strength] = data[card.strength] || 0;
				data[card.strength] += (card.is_unique ? 1 : card.indeck);
			}
		})
		data = _.flatten(data).map(function (value) { return value || 0; });

		$("#deck-chart-strength").highcharts({
			chart: {
				type: 'line'
			},
			title: {
	            text: Translator.trans("decks.charts.strength.title")
	        },
			subtitle: {
	            text: Translator.trans("decks.charts.strength.subtitle")
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
				headerFormat: '<span style="font-size: 10px">'+Translator.trans('decks.charts.strength.tooltip.header', {str: '{point.key}'})+'</span><br/>'
			},
			series: [{
				animation: false,
				name: Translator.trans('decks.charts.strength.tooltip.label'),
				showInLegend: false,
				data: data
			}]
		});
}

deck_charts.chart_cost = function chart_cost() {

		var data = [];

		var draw_deck = app.deck.get_draw_deck();
		draw_deck.forEach(function (card) {
			if(typeof card.cost === 'number') {
				data[card.cost] = data[card.cost] || 0;
				data[card.cost] += card.indeck;
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
	deck_charts.chart_faction();
	deck_charts.chart_icon();
	deck_charts.chart_strength();
	deck_charts.chart_cost();
}

$(document).on('shown.bs.tab', 'a[data-toggle=tab]', function (e) {
	deck_charts.setup();
});

})(app.deck_charts = {}, jQuery);
