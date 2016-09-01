(function app_deck(deck, $) {

var date_creation,
	date_update,
	description_md,
	id,
	name,
	tags,
	affiliation_code,
	affiliation_name,
	unsaved,
	user_id,
	layouts = {},
	layout_data = {};

Handlebars.registerHelper('cards', function(key, value, opt) {
    var query=[]; query[key] = value;
    return app.deck.get_cards({name: 1}, query);
});

Handlebars.registerHelper('nb_cards', function(cards) {
    return app.deck.get_nb_cards(cards);
});

var Template = Handlebars.compile(
'<div class="deck-content">' +
'	<div class="row">' +
'		<div class="col-sm-6">' +
'			<h4 style="font-weight:bold">{{deck.get_affiliation_name}}</h4>' +
'			<div>' +
'				{{trans "decks.edit.meta.characters"}}: ' +
'				{{transChoice "decks.edit.meta.points" (deck.get_character_points) points=(deck.get_character_points)}},' +
'				{{transChoice "decks.edit.meta.dice" (deck.get_character_dice) count=(deck.get_character_dice)}}' +
'			</div>' +
'			<div>' +
'				{{trans "decks.edit.meta.drawdeck"}}: ' +
'				{{transChoice "decks.edit.meta.cards" (deck.get_draw_deck_size) count=(deck.get_draw_deck_size)}},' +
'				{{transChoice "decks.edit.meta.dice" (deck.get_draw_deck_dice) count=(deck.get_draw_deck_dice)}}' +
'			</div>' +
'			<div>{{trans "decks.edit.meta.sets" sets=sets}}</div>' +
'			{{#if deck.get_problem}}' +
'			<div class="text-danger small">' +
'				<span class="fa fa-exclamation-triangle"></span>{{trans (concat "decks.problems." (deck.get_problem))}}' +
'			</div>' +
'			{{/if}}' +
'		</div>' +
'		<div class="col-sm-6">		' +
'			{{#with deck.get_battlefield}}' +
'			<h5><span class="icon icon-battlefield"></span> {{this.type_name}}</h5>' +
'				<div class="row">' +
'					<div class="col-lg-12 text-center">' +
'						<div class="battlefield-thumbnail card-thumbnail-2x card-thumbnail-battlefield border-{{faction_code}}" style="background-image:url(\'{{imagesrc}}\')"></div>' +
'						<div>' +
'							<a href="#" class="card card-tip fg-{{faction_code}}" data-toggle="modal" data-remote="false" data-target="#cardModal" data-code="{{code}}">' +
'								{{name}}' +
'							</a>' +
'						</div>' +
'					</div>' +
'				</div>' +
'			{{/with}}' +
'		</div>' +
'	</div>' +
'	<div class="row">' +
'		<div class="col-sm-12">' +
'			<div>' +
'				{{#with (deck.get_character_row_data)}}' +
'				<h5><span class="icon icon-character"></span> {{this.0.type_name}} ({{nb_cards this}})</h5>' +
'				<div class="row">' +
'				{{#each this}}' +
'					<div class="col-lg-3 col-sm-6 text-center">' +
'						<div class="character-thumbnail card-thumbnail-2x card-thumbnail-character border-{{faction_code}}" style="background-image:url(\'{{imagesrc}}\')"></div>' +
'						<div>' +
'							<a href="#" class="card card-tip fg-{{faction_code}}" data-toggle="modal" data-remote="false" data-target="#cardModal" data-code="{{code}}">' +
'								{{name}}' +
'							</a>' +
'						</div>' +
'						{{dice}} <span class="icon-die"></span>' +
'					</div>' +
'				{{/each}}' +
'				</div>' +
'				{{/with}}' +
'			</div>' +
'		</div>' +
'	</div>' +
'	<div class="row">' +
'		{{#*inline "section"}}' +
'			<div>' +
'				{{#with (cards key value)}}' +
'				<h5><span class="icon icon-{{this.0.type_code}}"></span> {{this.0.type_name}} ({{nb_cards this}})</h5>' +
'				{{#each this}}' +
'				<div>' +
'					x{{indeck}}' +
'					<span class="icon icon-{{type_code}} fg-{{faction_code}}"></span>' +
'					<a href="#" class="card card-tip" data-toggle="modal" data-remote="false" data-target="#cardModal" data-code="{{code}}">' +
'						{{name}}' +
'					</a>' +
'				</div>' +
'				{{/each}}' +
'				{{/with}}' +
'			</div>' +
'		{{/inline}}' +
'		<div class="col-sm-6 col-print-6">' +
'			{{> section key="type_code" value="upgrade"}}' +
'		</div>' +
'		<div class="col-sm-6 col-print-6">' +
'			{{> section key="type_code" value="support"}}' +
'			{{> section key="type_code" value="event"}}' +
'		</div>' +
'	</div>' +
'</div>'
);

/*
 * Templates for the different deck layouts, see deck.get_layout_data
 */
layouts[1] = _.template('<div class="deck-content"><%= meta %><%= plots %><%= characters %><%= attachments %><%= locations %><%= events %></div>');
//layouts[2] = _.template('<div class="deck-content"><div class="row"><div class="col-sm-6 col-print-6"><%= meta %></div><div class="col-sm-6 col-print-6"><%= plots %></div></div><div class="row"><div class="col-sm-6 col-print-6"><%= characters %></div><div class="col-sm-6 col-print-6"><%= attachments %><%= locations %><%= events %></div></div></div>');
layouts[2] = _.template('<div class="deck-content"><div class="row"><div class="col-sm-6 col-print-6"><%= meta %></div><div class="col-sm-6 col-print-6"></div></div><div class="row"><div class="col-sm-6 col-print-6"></div><div class="col-sm-6 col-print-6"></div></div></div>');
//layouts[3] = _.template('<div class="deck-content"><div class="row"><div class="col-sm-4"><%= meta %><%= plots %></div><div class="col-sm-4"><%= characters %></div><div class="col-sm-4"><%= attachments %><%= locations %><%= events %></div></div></div>');

/**
 * @memberOf deck
 */
deck.init = function init(data) {
	date_creation = data.date_creation;
	date_update = data.date_update;
	description_md = data.description_md;
	id = data.id;
	name = data.name;
	tags = data.tags;
	affiliation_code = data.affiliation_code;
	affiliation_name = data.affiliation_name;
	unsaved = data.unsaved;
	user_id = data.user_id;
	
	if(app.data.isLoaded) {
		deck.set_slots(data.slots);
	} else {
		console.log("deck.set_slots put on hold until data.app");
		$(document).on('data.app', function () { deck.set_slots(data.slots); });
	}
}

/**
 * Sets the slots of the deck
 * @memberOf deck
 */
deck.set_slots = function set_slots(slots) {
	app.data.cards.update({}, {
		indeck: 0,
		dice: 0
	});
	for(code in slots) {
		if(slots.hasOwnProperty(code)) {
			app.data.cards.updateById(code, {
				indeck: slots[code].quantity,
				dice: slots[code].dice
			});
		}
	}
}

/**
 * @memberOf deck
 * @returns string
 */
deck.get_id = function get_id() {
	return id;
}

/**
 * @memberOf deck
 * @returns string
 */
deck.get_name = function get_name() {
	return name;
}

/**
 * @memberOf deck
 * @returns string
 */
deck.get_affiliation_code = function get_affiliation_code() {
	return affiliation_code;
}
/**
 * @memberOf deck
 * @returns string
 */
deck.get_affiliation_name = function get_affiliation_name() {
	return affiliation_name;
}

/**
 * @memberOf deck
 * @returns string
 */
deck.get_description_md = function get_description_md() {
	return description_md;
}


/**
 * @memberOf deck
 */
deck.get_battlefields = function get_battlefields() {
	return deck.get_cards(null, {
		type_code: 'battlefield'
	});
}

/**
 * @memberOf deck
 */
deck.get_battlefield = function get_battlefield() {
	var battlefields = deck.get_battlefields();
	return battlefields.length ? battlefields[0] : null;
}

/**
 * @memberOf deck
 */
deck.get_cards = function get_cards(sort, query) {
	sort = sort || {};
	sort['code'] = 1;

	query = query || {};
	query.indeck = {
		'$gt': 0
	};

	return app.data.cards.find(query, {
		'$orderBy': sort
	});
}

/**
 * @memberOf deck
 */
deck.get_draw_deck = function get_draw_deck(sort) {
	return deck.get_cards(sort, {
		type_code: {
			'$nin' : ['character', 'battlefield']
		}
	});
}

/**
 * @memberOf deck
 */
deck.get_character_deck = function get_draw_deck(sort) {
	return deck.get_cards(sort, {
		type_code: 'character'
	});
}

/**
 * @memberOf deck
 */
deck.get_character_points = function get_character_points() {
	var points = _.reduce(deck.get_character_deck(), function(points, character) {
		if(character.is_unique) {
			return points + parseInt(character.points.split('/')[character.dice-1], 10);
		} else {
			return points + parseInt(character.points, 10) * character.indeck;
		}
	}, 0);
	return points;
}


/**
 * @memberOf deck
 */
deck.get_character_factions = function get_character_points() {
	var points = _.reduce(deck.get_character_deck(), function(points, character) {
		if(character.is_unique) {
			return points + parseInt(character.points.split('/')[character.dice-1], 10);
		} else {
			return points + parseInt(character.points, 10) * character.indeck;
		}
	}, 0);
	return points;
}

/**
 * @memberOf deck
 */
deck.get_character_dice = function get_character_dice() {
	return deck.get_nb_dice(deck.get_character_deck());
}

/**
 * @memberOf deck
 */
deck.get_character_row_data = function get_character_row_data() {
	return _.flatten(_.map(deck.get_character_deck(), function(card) {
		if(card.is_unique) {
			return card;
		} else {
			var spread = [];
			for(var i=0;i<card.indeck;i++) {
				var clone = _.clone(card);
				clone.index = clone.dice = 1;
				spread.push(clone);
			}
			return spread;
		}
	}));
}

 /**
 * @memberOf deck
 */
deck.get_draw_deck_size = function get_draw_deck_size(sort) {
	var draw_deck = deck.get_draw_deck();
	return deck.get_nb_cards(draw_deck);
}
/**
 * @memberOf deck
 */
deck.get_draw_deck_dice = function get_draw_deck_dice(sort) {
	var draw_deck = deck.get_draw_deck();
	return deck.get_nb_dice(draw_deck);
}

deck.get_nb_cards = function get_nb_cards(cards) {
	if(!cards) cards = deck.get_cards();
	var quantities = _.map(cards, 'indeck');
	return _.reduce(quantities, function(memo, num) { return memo + num; }, 0);
}

deck.get_nb_dice = function get_nb_dice(cards) {
	if(!cards) cards = deck.get_cards();
	var dice = _.map(cards, 'dice');
	return _.reduce(dice, function(memo, num) { return memo + num; }, 0);
}

deck.get_nongray_factions = function get_nongray_factions(cards) {
	if(!cards) cards = deck.get_cards();
	return _(cards).map('faction_code').uniq().reject(_.partial(_.isEqual, 'gray')).value()
}

/**
 * @memberOf deck
 */
deck.get_included_sets = function get_included_sets() {
	var cards = deck.get_cards();
	var nb_sets = {};
	cards.forEach(function (card) {
		nb_sets[card.set_code] = Math.max(nb_sets[card.set_code] || 0, card.indeck);
	});
	var set_codes = _.uniq(_.map(cards, 'set_code'));
	var sets = app.data.sets.find({
		'code': {
			'$in': set_codes
		}
	}, {
		'$orderBy': {
			'available': 1
		}
	});
	sets.forEach(function (set) {
		set.quantity = nb_sets[set.code] || 0;
	})
	return sets;
}

/**
 * @memberOf deck
 */
deck.display = function display(container, options) {
	
	options = _.extend({sort: 'type', cols: 2}, options);

	//var layout_data = deck.get_layout_data(options);
	//var deck_content = layouts[options.cols](layout_data);
	var deck_content = Template({
		deck: this,
		sets: _.map(deck.get_included_sets(), function (set) { return set.name+(set.quantity > 1 ? ' ('+set.quantity+')' : ''); }).join(', ')
	});

	$(container)
		.removeClass('deck-loading')
		.empty();

	$(container).append(deck_content);
}

deck.get_layout_data = function get_layout_data(options) {
	
	var data = {
			images: '',
			meta: '',
			characters: '',
			upgrades: '',
			supports: '',
			events: ''
	};
	
	var problem = deck.get_problem();

	//deck.update_layout_section(data, 'images', $('<div style="margin-bottom:10px"><img src="/bundles/app/images/factions/'+deck.get_faction_code()+'.png" class="img-responsive">'));

	deck.update_layout_section(data, 'meta', $('<h4 style="font-weight:bold">'+affiliation_name+'</h4>'));
	deck.update_layout_section(data, 'meta', $(Handlebars.compile('<div>{{drawdeck}}: {{cards}}, {{dice}}</div>')({
		drawdeck: Translator.trans('decks.edit.meta.drawdeck'),
		cards: Translator.transChoice('decks.edit.meta.cards', deck.get_draw_deck_size(), {count: deck.get_draw_deck_size()}),
		dice: Translator.transChoice('decks.edit.meta.dice', deck.get_draw_deck_dice(), {count: deck.get_draw_deck_dice()})
	})).addClass(deck.get_draw_deck_size() < 30 ? 'text-danger': ''));
	var sets = _.map(deck.get_included_sets(), function (set) { return set.name+(set.quantity > 1 ? ' ('+set.quantity+')' : ''); }).join(', ');
	deck.update_layout_section(data, 'meta', $('<div>'+Translator.trans('decks.edit.meta.sets', {"sets": sets})+'</div>'));
	if(problem) {
		deck.update_layout_section(data, 'meta', $('<div class="text-danger small"><span class="fa fa-exclamation-triangle"></span> '+problem_labels[problem]+'</div>'));
	}

	/*
	deck.update_layout_section(data, 'plots', deck.get_layout_data_one_section('type_code', 'plot', 'type_name'));
	deck.update_layout_section(data, 'characters', deck.get_layout_data_one_section('type_code', 'character', 'type_name'));
	deck.update_layout_section(data, 'attachments', deck.get_layout_data_one_section('type_code', 'attachment', 'type_name'));
	deck.update_layout_section(data, 'locations', deck.get_layout_data_one_section('type_code', 'location', 'type_name'));
	deck.update_layout_section(data, 'events', deck.get_layout_data_one_section('type_code', 'event', 'type_name'));
	*/
	
	return data;
}

deck.update_layout_section = function update_layout_section(data, section, element) {
	data[section] = data[section] + element[0].outerHTML;
}

deck.get_layout_data_one_section = function get_layout_data_one_section(sortKey, sortValue, displayLabel) {
	var section = $('<div>');
	var query = {};
	query[sortKey] = sortValue;
	var cards = deck.get_cards({ name: 1 }, query);
	if(cards.length) {
		$(header_tpl({code: sortValue, name:cards[0][displayLabel], quantity: deck.get_nb_cards(cards)})).appendTo(section);
		cards.forEach(function (card) {
			var $div = $('<div>').addClass(deck.can_include_card(card) ? '' : 'invalid-card');
			$div.append($(card_line_tpl({card:card})));
			$div.prepend(card.indeck+'x ');
			$div.appendTo(section);
		});
	}
	return section;
}

/**
 * @memberOf deck
 * @return boolean true if at least one other card quantity was updated
 */
deck.set_card_copies = function set_card_copies(card_code, nb_copies) {
	var card = app.data.cards.findById(card_code);
	if(!card) return false;

	var updated_other_card = false;

	// card-specific rules
	switch(card.type_code) {
		case 'battlefield':
			app.data.cards.update({
				type_code: 'battlefield'
			}, {
				indeck: 0
			});
			updated_other_card = true;
			break;
	}

	app.data.cards.updateById(card_code, {
		indeck: nb_copies,
		dice: card.has_die ? nb_copies : 0
	});
	app.deck_history && app.deck_history.notify_change();

	return updated_other_card;
}
/**
 * @memberOf deck
 * @return boolean true if at least one other card quantity was updated
 */
deck.set_card_2nd_die = function set_card_2nd_die(card_code, die_active) {
	var card = app.data.cards.findById(card_code);
	if(!card) return;

	app.data.cards.updateById(card_code, {
		dice: card.indeck==0 ? 0 : (die_active) ? card.indeck+1 : card.indeck
	});
	app.deck_history && app.deck_history.notify_change();

	return card.indeck;
}

/**
 * @memberOf deck
 */
deck.get_content = function get_content() {
	var cards = deck.get_cards();
	var content = {};
	cards.forEach(function (card) {
		content[card.code] = {
			quantity: card.indeck,
			dice: card.dice
		};
	});
	return content;
}

/**
 * @memberOf deck
 */
deck.get_json = function get_json() {
	return JSON.stringify(deck.get_content());
}

/**
 * @memberOf deck
 */
deck.get_export = function get_export(format) {

}

/**
 * @memberOf deck
 */
deck.get_copies_and_deck_limit = function get_copies_and_deck_limit() {
	var copies_and_deck_limit = {};
	deck.get_draw_deck().forEach(function (card) {
		var value = copies_and_deck_limit[card.name];
		if(!value) {
			copies_and_deck_limit[card.name] = {
					nb_copies: card.indeck,
					deck_limit: card.deck_limit
			};
		} else {
			value.nb_copies += card.indeck;
			value.deck_limit = Math.min(card.deck_limit, value.deck_limit);
		}
	})
	return copies_and_deck_limit;
}

/**
 * @memberOf deck
 */
deck.get_problem = function get_problem() {
	// at least 30 others cards
	if(deck.get_draw_deck_size() < 30) {
		return 'too_few_cards';
	}

	if(deck.get_character_points() > 30) {
		return 'too_many_character_points';
	}

	// too many copies of one card
	if(_.findKey(deck.get_copies_and_deck_limit(), function(value) {
	    return value.nb_copies > value.deck_limit;
	}) != null) return 'too_many_copies';

	//faction_not_included
	if(_.difference(deck.get_nongray_factions(deck.get_draw_deck()), deck.get_nongray_factions(deck.get_character_deck())).length > 0) return 'faction_not_included';

	// no invalid card
	if(deck.get_invalid_cards().length > 0) {
		return 'invalid_cards';
	}
}

deck.get_invalid_cards = function get_invalid_cards() {
	return _.filter(deck.get_cards(), function (card) {
		return ! deck.can_include_card(card);
	});
}

/**
 * returns true if the deck can include the card as parameter
 * @memberOf deck
 */
deck.can_include_card = function can_include_card(card) {
	// neutral card => yes
	if(card.affiliation_code === 'neutral') return true;

	// affiliation card => yes
	if(card.affiliation_code === affiliation_code) return true;

	// if none above => no
	return false;
}

})(app.deck = {}, jQuery);
