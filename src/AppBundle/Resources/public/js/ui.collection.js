(function ui_collection(ui, $) {

var DisplayColumnsTpl = Handlebars.compile(
		'<tr data-code="{{card.code}}"> ' +
		'    <td> ' +
		'        <span class="icon icon-{{card.type_code}} fg-{{ card.faction_code }}"></span> ' +
		'        <a class="card card-tip" data-code="{{ card.code }}" href="{{ url }}" data-target="#cardModal" data-remote="false" data-toggle="modal">{{ card.label }}</a> ' +
		'    </td> ' +
		'    {{#if card.has_die}} ' +
		'        {{#each card.sides}} ' +
		'        <td class="text-center"> ' +
		'            {{dieside this}} ' +
		'        </td> ' +
		'        {{/each}} ' +
		'    {{else}} ' +
		'        <td colspan="6"></td> ' +
		'    {{/if}} ' +
		'    <td>{{ card.set_name }} {{ card.position }}</td> ' +
		'   <td> ' +
		'         <input type="text" class="touchspin" value="{{card.owned}}"/>' +
		'   </td> ' +
		'</tr> '
	),
	SortKey = 'code',
	SortOrder = 1,
	CardDivs = [[],[],[]],
	Config = null;

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
		'	<strong>{{example.rarity_name}}</strong>' +
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
		available: true
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
		//if (!event.shiftKey && Config['buttons-behavior'] === 'exclusive' || event.shiftKey && Config['buttons-behavior'] === 'cumulative') {
			if (!event.altKey) {
				uncheck_all_active.call(this);
			} else {
				check_all_inactive.call(this);
			}
		//}
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

	$('#collection').on('change', 'input.touchspin', function() {
		var $tr=$(this).closest('tr[data-code]'),
		    code = $tr.data('code');
		    op = $(this).text();
		    card = app.data.cards.findById(code);

		app.data.cards.updateById(code, {owned: $(this).val()});
	});

	$('#btn-save').on('click', function(e) {
		e.stopPropagation();
		var toSave = _.chain(app.data.cards.find({$or: [{owned: {$gt: 0}}, {actualOwned: {$gt: 0}}]}))
			.filter(function(c) { return c.owned != c.actualOwned; })
			.map(_.partial(_.pick, _, ['code','owned', 'actualOwned']))
			.value();
		var $form = $(this).closest('form');
		$form.find('#changes').val(JSON.stringify(toSave));			
		$form.submit();
	});

	//$('#filter-text').on('input', ui.on_input_smartfilter);

	//$('#save_form').on('submit', ui.on_submit_form);

	/*
	$('#btn-save-as-copy').on('click', function(event) {
		$('#deck-save-as-copy').val(1);
	});
	*/

	/*
	$('#btn-cancel-edits').on('click', function(event) {
		var unsaved_edits = app.deck_history.get_unsaved_edits();
		if(unsaved_edits.length) {
			var confirmation = confirm("This operation will revert the changes made to the deck since "+unsaved_edits[0].date_creation.calendar()+". The last "+(unsaved_edits.length > 1 ? unsaved_edits.length+" edits" : "edit")+" will be lost. Do you confirm?");
			if(!confirmation) return false;
		}
		else {
			if(app.deck_history.is_changed_since_last_autosave()) {
				var confirmation = confirm("This operation will revert the changes made to the deck. Do you confirm?");
				if(!confirmation) return false;
			}
		}
		$('#deck-cancel-edits').val(1);
	});
	*/

	//$('#config-options').on('change', 'input', ui.on_config_change);
	$('#collection').on('change', 'input[type=radio]', ui.on_list_quantity_change);

	$('#cardModal').on('keypress', function(event) {
		var num = parseInt(event.which, 10) - 48;
		$('#cardModal input[type=radio][value=' + num + ']').trigger('change');
	});
	$('#cardModal').on('change', 'input[type=radio]', ui.on_modal_quantity_change);

	$('thead').on('click', 'a[data-sort]', ui.on_table_sort_click);

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

/**
 * builds a row for the list of available cards
 * @memberOf ui
 */
ui.build_row = function build_row(card) {
	var html = DisplayColumnsTpl({
		url: Routing.generate('cards_zoom', {card_code:card.code}),
		card: card
	});
	return $(html);
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

		row.find('.touchspin').TouchSpin({
			min: 0,
			step: 1,
			decimals: 0,
			buttondown_class: 'btn btn-danger',
			buttonup_class: 'btn btn-success'
		});
		/*
		row.find('input[name="qty-' + card.code + '"]').each(
			function(i, element) {
				if($(element).val() == card.indeck) {
					$(element).prop('checked', true).closest('label').addClass('active');
				} else {
					$(element).prop('checked', false).closest('label').removeClass('active');
				}
			}
		);
		*/

		/*
		if (unusable) {
			row.find('label').addClass("disabled").find('input[type=radio]').attr("disabled", true);
		}
		*/

		/*
		if (Config['display-column'] > 1 && (counter % Config['display-column'] === 0)) {
			container = $('<div class="row"></div>').appendTo($('#collection-grid'));
		}
		*/

		container.append(row);
		counter++;
	});
}, 250);

/**
 * called when the DOM is loaded
 * @memberOf ui
 */
ui.on_dom_loaded = function on_dom_loaded() {
	ui.setup_event_handlers();
};

/*
* * called when the app data is loaded
 * @memberOf ui
 */
ui.on_data_loaded = function on_data_loaded() {
	
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


})(app.ui, jQuery);
