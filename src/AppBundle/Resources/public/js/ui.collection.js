(function ui_collection(ui, $) {

var SortKey = 'code',
	SortOrder = 1,
	CardDivs = [[],[],[]],
	Config = null;

/**
 * reads ui configuration from localStorage
 * @memberOf ui
 */
ui.read_config_from_storage = function read_config_from_storage() {
	if (localStorage) {
		var stored = localStorage.getItem('ui.collection.config');
		if(stored) {
			Config = JSON.parse(stored);
		}
	}
	Config = _.extend({
		'link-cards-dice': 0,
		'buttons-behavior': 'cumulative'
	}, Config || {});
}

/**
 * write ui configuration to localStorage
 * @memberOf ui
 */
ui.write_config_to_storage = function write_config_to_storage() {
	if (localStorage) {
		localStorage.setItem('ui.collection.config', JSON.stringify(Config));
	}
}

/**
 * inits the state of config buttons
 * @memberOf ui
 */
ui.init_config_buttons = function init_config_buttons() {
	// radio
	['buttons-behavior'].forEach(function (radio) {
		$('input[name='+radio+'][value='+Config[radio]+']').prop('checked', true);
	});
	// checkbox
	['link-cards-dice'].forEach(function (checkbox) {
		if(Config[checkbox]) $('input[name='+checkbox+']').prop('checked', true);
	});
}

ui.init_filter_help = function init_filter_help() {
	$('#filter-text-button').popover({
		container: 'body',
		content: app.smart_filter.get_help(),
		html: true,
		placement: 'bottom',
		title: Translator.trans('decks.smartfilter.title')
	});
}

/**
 * sets the current quantity of cards and dice in collection
 * @memberOf ui
 */
ui.set_current_owned = function set_current_owned() {
	app.data.cards.find().forEach(function(record) {
		app.data.cards.updateById(record.code, {
			current: record.owned
		});
	});
}

function get_examples(codes, key) {
	return _.map(codes, function(code) {
		var query={}; query[key] = code;
		return {code: code, example: app.data.cards.find(query)[0] };
	});	
}

/**
 * builds the affiliation selector
 * @memberOf ui
 */
ui.build_affiliation_selector = function build_affiliation_selector() {
	$('[data-filter=affiliation_code]').empty();
	var tpl = Handlebars.compile(
		'{{#each codes}}' +
		'<label class="btn btn-default btn-sm" data-code="{{code}}" title="{{example.affiliation_name}}">' +
		'	<input type="checkbox" name="{{code}}">' +
		'	<strong>{{example.affiliation_name}}</strong>' +
		'</label>' +
		'{{/each}}'
	);
	var affiliation_codes = app.data.cards.distinct('affiliation_code').sort();
	var neutral_index = affiliation_codes.indexOf('neutral');
	affiliation_codes.splice(neutral_index, 1);
	affiliation_codes.unshift('neutral');
	$('[data-filter=affiliation_code]').html(
		tpl({codes: get_examples(affiliation_codes, 'affiliation_code')})
	).button().find('label').tooltip({container: 'body'});
}

/**
 * builds the faction selector
 * @memberOf ui
 */
ui.build_faction_selector = function build_faction_selector() {
	$('[data-filter=faction_code]').empty();
	var tpl = Handlebars.compile(
		'{{#each codes}}' +
		'<label class="btn btn-default btn-sm fg-{{code}}" data-code="{{code}}" title="{{example.faction_name}}">' +
		'	<input type="checkbox" name="{{code}}">' +
		'	<strong>{{example.faction_name}}</strong>' +
		'</label>' +
		'{{/each}}'
	);
	var faction_codes = app.data.cards.distinct('faction_code').sort();
	var gray_index = faction_codes.indexOf('gray');
	faction_codes.splice(gray_index, 1);
	faction_codes.unshift('gray');

	$('[data-filter=faction_code]').html(
		tpl({codes: get_examples(faction_codes, 'faction_code')})
	).button().find('label').tooltip({container: 'body'});
}

/**
 * builds the type selector
 * @memberOf ui
 */
ui.build_type_selector = function build_type_selector() {
	$('[data-filter=type_code]').empty();
	var tpl = Handlebars.compile(
		'{{#each codes}}' +
		'<label class="btn btn-default btn-sm" data-code="{{code}}" title="{{example.type_name}}">' +
		'	<input type="checkbox" name="{{code}}">' +
		'	<span class="icon-{{code}}"></span>' +
		'</label>' +
		'{{/each}}'
	);

	$('[data-filter=type_code]').html(
		tpl({codes: get_examples(['battlefield','character','upgrade','support', 'event'], 'type_code')})
	).button().find('label').tooltip({container: 'body'});
}

/**
 * builds the rarity selector
 * @memberOf ui
 */
ui.build_rarity_selector = function build_rarity_selector() {
	$('[data-filter=rarity_code]').empty();
	var tpl = Handlebars.compile(
		'{{#each codes}}' +
		'<label class="btn btn-default btn-sm fg-rarity-{{code}}" data-code="{{code}}" title="{{example.rarity_name}}">' +
		'	<input type="checkbox" name="{{code}}">' +
		'	<span class="icon-collectors"></span>' +
		'</label>' +
		'{{/each}}'
	);
	$('[data-filter=rarity_code]').html(
		tpl({codes: get_examples(['S','C', 'U', 'R', 'L'], 'rarity_code')})
	).button().find('label').tooltip({container: 'body'});
}

/**
 * builds the set selector
 * @memberOf ui
 */
ui.build_set_selector = function build_set_selector() {
	$('[data-filter=set_code]').empty();
	app.data.sets.find({
		name: {
			'$exists': true
		}, 
		available: {
			'$exists': true
		}
	}, {
	    $orderBy: {
	        position: 1
	    }
	}).forEach(function(record) {
		// checked or unchecked ? checked by default
		var checked = true;
		$('<li><a href="#"><label><input type="checkbox" name="' + record.code + '"' + (checked ? ' checked="checked"' : '') + '>' + record.name + '</label></a></li>').appendTo('[data-filter=set_code]');
	});
}

function uncheck_all_others() {
	$(this).closest('[data-filter]').find("input[type=checkbox]").prop("checked",false);
	$(this).children('input[type=checkbox]').prop("checked", true).trigger('change');
}

function check_all_others() {
	$(this).closest('[data-filter]').find("input[type=checkbox]").prop("checked",true);
	$(this).children('input[type=checkbox]').prop("checked", false);
}

function uncheck_all_active() {
	$(this).closest('[data-filter]').find("label.active").button('toggle');
}

function check_all_inactive() {
	$(this).closest('[data-filter]').find("label:not(.active)").button('toggle');
}

/**
 * @memberOf ui
 * @param event
 */
ui.on_click_filter = function on_click_filter(event) {
	var dropdown = $(this).closest('ul').hasClass('dropdown-menu');
	if (dropdown) {
		if (event.shiftKey) {
			if (!event.altKey) {
				uncheck_all_others.call(this);
			} else {
				check_all_others.call(this);
			}
		}
		event.stopPropagation();
	} else {
		if (!event.shiftKey && Config['buttons-behavior'] === 'exclusive' || event.shiftKey && Config['buttons-behavior'] === 'cumulative') {
			if (!event.altKey) {
				uncheck_all_active.call(this);
			} else {
				check_all_inactive.call(this);
			}
		}
	}
}

/**
 * @memberOf ui
 * @param event
 */
ui.on_input_smartfilter = function on_input_smartfilter(event) {
	var q = $(this).val();
	if(q.match(/^\w[:<>!]/)) app.smart_filter.update(q);
	else app.smart_filter.update('');
	ui.refresh_list();
}

/**
 * @memberOf ui
 * @param event
 */
ui.on_submit_form = function on_submit_form(event) {
	event.stopPropagation();
	var toSave = ui.get_collection_changes();
	var $form = $('#form');
	$form.find('#changes').val(JSON.stringify(toSave));			
	$form.submit();
}

/**
 * sets up event handlers ; dataloaded not fired yet
 * @memberOf ui
 */
 ui.on_button_spin = function on_button_spin(event) {
	event.stopPropagation();

	var row = $(this).closest('.card-container');
	var code = row.data('code');
	var coll = $(this).closest('[data-spin]').data('spin');
	var inc = $(this).text()=='+' ? 1 : -1;

	var quantity = parseInt($(this).closest('.btn-spinner').find('span.value').text(), 10) + inc;
	if(quantity >= 0)
		ui.on_quantity_change(code, coll, quantity);

	//if cards and dice linked, update the other coll
	if(Config['link-cards-dice']) {
		var otherColl = coll=='cards' ? 'dice' : 'cards';
		var otherQty = parseInt(row.find('[data-spin='+otherColl+']').find('span.value').text(), 10) + inc;
		if(otherQty >= 0)
			ui.on_quantity_change(code, otherColl, otherQty);
	}
 }

/**
 * sets up event handlers ; dataloaded not fired yet
 * @memberOf ui
 */
 ui.on_quantity_change = function on_quantity_change(card_code, coll, quantity) {
	var update_all = app.collection.set_card_owns(card_code, coll, quantity);
	
	if(update_all) {
		ui.refresh_list();
	}
	else {
		ui.refresh_row(card_code);
	}
 }


/**
 * @memberOf ui
 * @param event
 */
ui.on_config_change = function on_config_change(event) {
	var name = $(this).attr('name');
	var type = $(this).prop('type');
	switch(type) {
		case 'radio':
			var value = $(this).val();
			if(!isNaN(parseInt(value, 10))) value = parseInt(value, 10);
			Config[name] = value;
			break;
		case 'checkbox':
			Config[name] = $(this).prop('checked');
			break;
	}
	switch(name) {
		case 'link-cards-dice':
			ui.update_spinners();
			break;
	}
	ui.write_config_to_storage();
	switch(name) {
		case 'buttons-behavior':
			break;
		default:
			ui.refresh_list();
	}
}

/**
 * @memberOf ui
 * @param event
 */
ui.on_before_unload = function on_before_unload(event) {
	var changes = ui.get_collection_changes();
	if(changes.length > 0) {
		event.returnValue = 'Are you sure you want to quit?';
		return event.returnValue;
	}
}

/**
 * sets up event handlers ; dataloaded not fired yet
 * @memberOf ui
 */
ui.setup_event_handlers = function setup_event_handlers() {

	$('[data-filter]').on({
		change : ui.refresh_list,
		click : ui.on_click_filter
	}, 'label');

	$('#btn-save').on('click', ui.on_submit_form);

	$('#collection').on('click', 'button.btn-spin', ui.on_button_spin);

	$('#filter-text').on('input', ui.on_input_smartfilter);
	$('#config-options').on('change', 'input', ui.on_config_change);

	//window.onbeforeunload = ui.on_before_unload;
	$('#form').dirtyForms({
	    helpers:
	        [
	            {
	                isDirty: function ($node, index) {
	                    if ($node.is('form')) {
	                        return ui.get_collection_changes().length > 0;
	                    }
	                }
	            }
	        ]
	});
}

/**
 * returns the current card filters as an array
 * @memberOf ui
 */
ui.get_filters = function get_filters() {
	var filters = {};
	$('[data-filter]').each(
		function(index, div) {
			var column_name = $(div).data('filter');
			var arr = [];
			$(div).find("input[type=checkbox]").each(
				function(index, elt) {
					if($(elt).prop('checked')) arr.push($(elt).attr('name'));
				}
			);
			if(arr.length) {
				filters[column_name] = {
					'$in': arr
				};
			}
		}
	);
	return filters;
}

ui.get_collection_changes = function get_collection_changes() {
	return _.chain(app.data.cards.find())
		.filter(function(c) { return c.owned.cards != c.current.cards || c.owned.dice != c.current.dice; })
		.map(_.partial(_.pick, _, ['code','owned']))
		.value();
}

/**
 * builds a row for the list of available cards
 * @memberOf ui
 */
var DisplayColumnsTpl = Handlebars.compile(
'<tr>' +
'	<td data-th="Name">' +
'	    <span class="icon icon-{{card.type_code}} fg-{{card.faction_code}}"></span>' +
'		<a href="{{card.url}}" class="card-tip" data-code="{{card.code}}">' +
'			{{card.name}}' +
'			{{#if card.subtitle}}<span class="card-subtitle hidden-xs">- {{card.subtitle}}</span>{{/if}}' +
'		</a>' +
'	</td>' +
'	<td class="text-center">' +
'		<div class="btn-group btn-group-xs btn-spinner" data-spin="cards">' +
'			<button class="btn btn-danger btn-spin">-</button>' +
'			<button class="btn btn-default btn-value">' +
'				<span class="value">{{card.owned.cards}}</span>' +
'				<span class="icon-cards"></span>' +
'			</button>' +
'			<button class="btn btn-success btn-spin">+</button>' +
'		</div>' +
'	</td>' +
'	<td class="text-center">' +
'       {{#if card.has_die}} ' +
'		<div class="btn-group btn-group-xs btn-spinner" data-spin="dice">' +
'			<button class="btn btn-danger btn-spin">-</button>' +
'			<button class="btn btn-default btn-value">' +
'				<span class="value">{{card.owned.dice}}</span>' +
'				<span class="icon-die"></span>' +
'			</button>' +
'			<button class="btn btn-success btn-spin">+</button>' +
'		</div>' +
'       {{else}} &nbsp; ' +
'       {{/if}} ' +
'	</td>' +
'	<td class="hidden-sm hidden-xs" data-th="Affiliation">{{card.affiliation_name}}</td>' +
'	<td class="hidden-sm hidden-xs" data-th="Points/Cost">{{ternary (compare card.type_code "character") card.points card.cost}}</td>' +
'	<td class="hidden-sm hidden-xs" data-th="Health">{{card.health}}</td>' +
'	<td class="hidden-sm hidden-xs" data-th="Type">{{card.type_name}}</td>' +
'	<td class="hidden-xs" data-th="Rarity">{{card.rarity_name}}</td>' +
'	{{#if card.has_die}}' +
'	{{#each card.sides}}' +
'	{{#with (dieside this)}}' +
'	<td class="text-center die-face visible-lg" data-th="Die">' +
'		{{#if modifier}}+{{/if}}{{#in code "-" "Sp"}}{{else}}{{value}}{{/in}}<span class="icon icon-{{icon}}"></span>{{#if cost}}/{{cost}}{{/if}}' +
'	</td>' +
'	{{/with}}' +
'	{{/each}}' +
'	{{else}}' +
'	<td colspan="6" data-th="Die" class="hidden-md">&nbsp;</td>' +
'	{{/if}}' +
'	<td data-th="Set">' +
'		<span class="hidden-xs">{{card.set_name}}</span>' +
'		<span class="visible-xs-inline" title="{{card.set_name}}" data-toggle="tooltip">{{card.set_code}}</span>' +
'		{{card.position}}' +
'	</td>' +
'</tr>'
);
ui.build_row = function build_row(card) {
	var html = $(DisplayColumnsTpl({
		url: Routing.generate('cards_zoom', {card_code:card.code}),
		card: card
	}));
	html.find('[data-toggle="tooltip"]').tooltip();
	ui.set_card_collection_status(card, html);
	return html;
}

ui.reset_list = function reset_list() {
	CardDivs = [[],[],[]];
	ui.refresh_list();
}

/**
 * destroys and rebuilds the list of available cards
 * don't fire unless 250ms has passed since last invocation
 * @memberOf ui
 */
ui.refresh_list = _.debounce(function refresh_list() {
	$('#collection-table').empty();
	$('#collection-grid').empty();

	var counter = 0,
		container = $('#collection-table'),
		filters = ui.get_filters(),
		query = app.smart_filter.get_query(filters),
		orderBy = {};

	SortKey.split('|').forEach(function (key ) {
		orderBy[key] = SortOrder;
	});
	if(SortKey !== 'name') orderBy['name'] = 1;
	var cards = app.data.cards.find(query, {'$orderBy': orderBy});
	var divs = CardDivs[ 0 ];

	cards.forEach(function (card) {
		var row = divs[card.code];
		if(!row) row = divs[card.code] = ui.build_row(card);

		row.data("code", card.code).addClass('card-container');

		container.append(row);
		counter++;
	});

	ui.update_spinners();
}, 250);

ui.refresh_row = function refresh_row(card_code) {
	// for each set of divs (1, 2, 3 columns)
	CardDivs.forEach(function(rows) {
		var row = rows[card_code];
		if(!row) return;

		var card = app.data.cards.findById(card_code);
		
		// rows[card_code] is the card row of our card
		// for each "quantity switch" on that row
		row.find('[data-spin]').each(function(idx, spinner) {
			$(spinner).find('span.value').text(card.owned[$(spinner).data('spin')]);
		});

		ui.set_card_collection_status(card, row);
	});
};

ui.update_spinners = function update_spinners() {
	$('[data-spin=dice] button').prop('disabled', Config['link-cards-dice']);
};

ui.set_card_collection_status = function set_card_collection_status(card, row) {
	row.removeClass('collection-card-not-owned collection-card-not-playset collection-card-playset collection-card-excess collection-die-not-owned collection-die-not-playset collection-die-playset collection-die-excess');
	if(card.owned.cards==0) {
		row.addClass('collection-card-not-owned');
	} else if(card.owned.cards==1) {
		if(card.type_code=='character' && card.is_unique)
			row.addClass('collection-card-playset');
		else
			row.addClass('collection-card-not-playset');
	} else if(card.owned.cards==2) {
		if(card.type_code=='character' && card.is_unique)
			row.addClass('collection-card-excess');
		else
			row.addClass('collection-card-playset');
	} else {
		row.addClass('collection-card-excess');
	}
	if(card.owned.dice==0) {
		row.addClass('collection-die-not-owned');
	} else if(card.owned.dice==1) {
		row.addClass('collection-die-not-playset');
	} else if(card.owned.dice==2) {
		row.addClass('collection-die-playset');
	} else {
		row.addClass('collection-die-excess');
	}
}

/**
 * called when the DOM is loaded
 * @memberOf ui
 */
ui.on_dom_loaded = function on_dom_loaded() {
	ui.init_config_buttons();
	ui.init_filter_help();
	ui.setup_event_handlers();
};

/*
* * called when the app data is loaded
 * @memberOf ui
 */
ui.on_data_loaded = function on_data_loaded() {
	if(app.collection.isLoaded) {
		ui.set_current_owned();
	} else {
		$(document).on('collection.app', function(e) {
			ui.set_current_owned();
		});
	}
};

/**
 * called when both the DOM and the data app have finished loading
 * @memberOf ui
 */
ui.on_all_loaded = function on_all_loaded() {
	ui.build_affiliation_selector();
	ui.build_faction_selector();
	ui.build_type_selector();
	ui.build_rarity_selector();
	ui.build_set_selector();

	ui.refresh_list();
};

ui.read_config_from_storage();

})(app.ui, jQuery);
