(function app_deck(deck, $) {

var date_creation,
	date_update,
	description_md,
	id,
	format_code,
	format_name,
	name,
	tags,
	affiliation_code,
	affiliation_name,
	unsaved,
	user_id;

Handlebars.registerHelper('cards', function(key, value, opt) {
    var query=[]; query[key] = value;
    var cards = app.deck.get_cards({name: 1}, query);

    if(opt.hash && opt.hash.subtype) {
    	var predicate = card => _.map(card.subtypes, 'code').includes(opt.hash.subtype);
    	if(!!opt.hash.negate)
    		predicate = _.negate(predicate);
    	cards = cards.filter(predicate);
    }

    return cards;
});

Handlebars.registerHelper('nb_cards', function(cards) {
    return app.deck.get_nb_cards(cards);
});
Handlebars.registerHelper('nb_dice', function(cards) {
    return app.deck.get_nb_dice(cards);
});
Handlebars.registerHelper('can_include', function(card, options) {
    return app.deck.can_include_card(card);
});
Handlebars.registerHelper('own_enough_cards', function(card, options) {
    return app.deck.own_enough_cards(card);
});
Handlebars.registerHelper('own_enough_dice', function(card, options) {
    return app.deck.own_enough_dice(card);
});

Handlebars.registerHelper('subtype', function(card, subtype_code, options) {
	var subtype = _.find(card.subtypes, {code: subtype_code});
	return subtype && subtype.name; 
});

Handlebars.registerHelper('restricted', function(code) {
	return _.includes(app.deck.get_format_data().data.restricted, code);
});	

Handlebars.registerHelper('erratad', function(code) {
	return _.includes(app.deck.get_format_data().data.errata, code);
});	

/*
 * Templates for the deck layout
 */
var templates = {
	standard: Handlebars.templates['deck-layout-standard'],
	"standard-09114": Handlebars.templates['deck-layout-standard-09114']
}

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
		deck.on_data_loaded(data);
	} else {
		console.log("deck.set_slots put on hold until data.app");
		$(document).on('data.app', function () {
			deck.on_data_loaded(data);
		});
	}
}

deck.on_data_loaded = function on_data_loaded(data) {
	//back up points and has_errata from characters
	app.data.cards.find({type_code: 'character'}).forEach(function(card) {
		app.data.cards.updateById(card.code, {
			backedUp: _.pick(card, ['points', 'cp'])
		});
	});

	deck.set_format_code(data.format_code);
	deck.set_slots(data.slots);	
}

/**
 * Sets the slots of the deck
 * @memberOf deck
 */
deck.set_slots = function set_slots(slots) {
	app.data.cards.update({}, {
		indeck: {
			cards: 0,
			dice: 0
		}
	});
	for(code in slots) {
		if(slots.hasOwnProperty(code)) {
			app.data.cards.updateById(code, {
				indeck: {
					cards: slots[code].quantity,
					dice: slots[code].dice,
					dices: (slots[code].dices ? slots[code].dices.split(',').map(Number) : null)
				}
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
 deck.get_format_code = function get_format_code() {
 	return format_code;
 }

 /**
 * @memberOf deck
 * @returns string
 */
 deck.get_format_name = function get_format_name() {
 	return format_name;
 }

/**
 * @memberOf deck
 */
 deck.set_format_code = function set_format_code(code) {
 	format_code = code;
 	var data = deck.get_format_data();
 	format_name = data.name;

 	//restore cards to its state
 	app.data.cards.find({
 		backedUp: {$exists: true}
 	}).forEach(function(card) {
 		app.data.cards.updateById(card.code, card.backedUp);
 		app.data.cards.updateById(card.code, {$unset: {balance: 1}});
 	});

 	//balance of the force
 	_.forIn(data.data.balance, function(points, code) {
 		var pointsData = /^(\d+)(\/(\d+))?$/.exec(points);
 		app.data.cards.updateById(code, {
 			points: points,
 			cp: parseInt(pointsData[1], 10)*100+parseInt(pointsData[3]||0, 10),
 			balance: format_code
 		});
 	});
 }

 /**
 * @memberOf deck
 * @returns object
 */
 deck.get_format_data = function get_format_data() {
 	return app.data.formats.findById(format_code);
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
		cards: {'$gt': 0 }
	};

	return app.data.cards.find(query, {
		'$orderBy': sort
	});
}

/**
* @memberOf deck
*/
deck.is_included = function is_included(code) {
	return deck.get_cards(null, {code: code}).length > 0;
}

/**
 * @memberOf deck
 */
deck.get_draw_deck = function get_draw_deck(sort) {
	return deck.get_cards(sort, {
		type_code: {
			'$in' : ['upgrade', 'downgrade', 'support', 'event']
		}
	});
}

/**
 * @memberOf deck
 */
deck.get_character_deck = function get_character_deck(sort) {
	return deck.get_cards(sort, {
		type_code: 'character'
	});
}

/**
 * @memberOf deck
 */
deck.get_character_points = function get_character_points() {
	var points = _.reduce(deck.get_character_deck(), function(points, character) {
		if(character.indeck.dices) {
			for(var i=0;i<character.indeck.dices.length;i++) {
				points += parseInt(character.points.split('/')[character.indeck.dices[i]-1], 10);
			}
			return points;
		} else if(character.is_unique) {
			return points + parseInt(character.points.split('/')[character.indeck.dice-1], 10);
		} else {
			return points + parseInt(character.points, 10) * character.indeck.cards;
		}
	}, 0);

	//if Clone Commander Cody (AtG #73)
	if(deck.is_included('08073')) {
		//every Clone Trooper (LEG #38) cost 1 point less
		var cloneTroopers = app.data.cards.findById('05038').indeck.cards;
		points -= cloneTroopers;
	}

	//if General Grievous - Droid Armies Commander (CONV #21)
	if(deck.is_included('09021')) {
		//every droid cost 1 point less
		var droids = deck.get_character_row_data().filter(card => _.map(card.subtypes, 'code').includes('droid')).length;
		points -= droids;
	}

	//if Kanan Jarrus - Jedi Exile (CONV #55)
	if(deck.is_included('12055')) {
		if(deck.get_character_row_data().filter(card => card.code !== '12055' && _.map(card.subtypes, 'code').includes('spectre')).length > 0) {
			points -= 1;
		}
	}

	//if Luke Skywalker - Seeking The Path (TR #2A)
	if(deck.is_included('13002A')) {
		var teamup = ['Obi-Wan Kenobi','Yoda'];
		var characters = deck.get_character_row_data().filter(card => teamup.includes(card.name)).length;
		points -= characters;
	}

	//if Closing In (TR #6A)
	if(deck.is_included('13006A')) {
		//every bounty-hunter cost 1 point less
		var bh = deck.get_character_row_data().filter(card => _.map(card.subtypes, 'code').includes('bounty-hunter')).length;
		points -= bh;
	}

	//if Rescue Han Solo (TR #7A)
	if(deck.is_included('13007A')) {
		var teamup = ['Chewbacca','Lando Calrissian','Leia Organa','Luke Skywalker'];
		var characters = deck.get_character_row_data().filter(card => teamup.includes(card.name)).length;
		points -= characters;
	}

	//if Rescue The Princess (EC #44B)
	if(deck.is_included('701044B')) {
		var teamup = ['Chewbacca','Han Solo','Luke Skywalker','Obi-Wan Kenobi'];
		var characters = deck.get_character_row_data().filter(card => teamup.includes(card.name)).length;
		points -= characters;
	}

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
		} else if(card.indeck.dices) {
			var spread = [];
			for(var i=0;i<card.indeck.dices.length;i++) {
				var clone = _.clone(card);
				clone.indeck = {
					cards: 1,
					dice: card.indeck.dices[i]
				};
				clone.original = card;
				spread.push(clone);
			}
			return spread;
		} else {
			var spread = [];
			for(var i=0;i<card.indeck.cards;i++) {
				var clone = _.clone(card);
				clone.indeck = {
					cards: 1,
					dice: 1
				};
				clone.original = card;
				spread.push(clone);
			}
			return spread;
		}
	}));
}

/**
 * @memberOf deck
 */
deck.get_plot_deck = function get_plot_deck(sort) {
	return deck.get_cards(sort, {
		type_code: 'plot'
	});
}

/**
 * @memberOf deck
 */
deck.get_plot_points = function get_plot_points() {
	var points = _.reduce(deck.get_plot_deck(), function(points, plot) {
		return points + parseInt(plot.points, 10) * plot.indeck.cards;
	}, 0);

	//if Director Krennic - Death Star Mastermind (CM #21)
	if(deck.is_included('12021')) {
		//every death star plot cost 1 point less
		var plots = deck.get_plot_deck().filter(card => _.map(card.subtypes, 'code').includes('death-star')).length;
		points -= plots;
	}

	//if Luke Skywalker - Red Five (CM #56)
	if(deck.is_included('12021')) {
		//every death star plot cost 1 point less
		var plots = deck.get_plot_deck().filter(card => _.map(card.subtypes, 'code').includes('death-star')).length;
		points -= plots;
	}

	return points;
}

 /**
 * @memberOf deck
 */
deck.get_draw_deck_size = function get_draw_deck_size(sort) {
	var draw_deck = deck.get_draw_deck();
	var size = deck.get_nb_cards(draw_deck);

	//if Lightsaber Mastery is included
	if(deck.is_included("09114")) {
		//up to 2 move events don't count towards deck size
		var moves = deck.get_nb_cards(deck.get_cards().filter(card => _.map(card.subtypes, 'code').includes('move') && card.type_code==='event'));
		if(moves > 0)
			size -= _.min([moves, 2]);
	}
	return size;
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
	var quantities = _.map(cards, 'indeck.cards');
	return _.reduce(quantities, function(memo, num) { return memo + num; }, 0);
}

deck.get_nb_dice = function get_nb_dice(cards) {
	if(!cards) cards = deck.get_cards();
	var dice = _.map(cards, 'indeck.dice');
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
	var set_codes = _.uniq(_.map(cards, 'set_code'));
	var sets = app.data.sets.find({
		'code': {
			'$in': set_codes
		}
	}, {
		'$orderBy': {
			'position': 1,
			'available': 1
		}
	});
	return sets;
}

/**
 * @memberOf deck
 */
deck.display = function display(container, options) {
	
	options = _.extend({sort: 'type', cols: 2}, options);

	var template = templates.standard;
	if(deck.is_included('09114')) {
		template = templates['standard-09114'];
	}

	var deck_content = template({
		deck: this,
		sets: _.map(deck.get_included_sets(), 'name').join(', ')
	});

	$(container)
		.removeClass('deck-loading')
		.empty();

	$(container).append(deck_content).find('[data-toggle="tooltip"]').tooltip();
}

/**
 * Change the number of copies and dice together. One Copy = One die.
 *
 * @memberOf deck
 * @return boolean true if at least one other card quantity was updated
 */
deck.set_card_copies = function set_card_copies(card_code, nb_copies, dices) {
	var card = app.data.cards.findById(card_code);
	if(!card) return false;

	var updated_other_card = false;

	// card-specific rules
	switch(card.type_code) {
		case 'battlefield':
			if(deck.get_cards(null, {code: '07127'}).length > 0) {
				//with 'Home Turf Advantage' plot
				break;
			}
		case 'plot':
			if(card_code == '15101')
				break;
			app.data.cards.update({
				type_code: card.type_code,
				code: { $ne: '15101' }
			}, {
				indeck: {
					cards: 0,
					dice: 0
				}
			});
			updated_other_card = true;
			break;
	}

	// by default, a card with a die has an equal number of copies and dice,
	// except for unique characters when you can only hava a copy, but one
	// or more dice. Still then, the UI only allow you to select one copy,
	// so if 1 copy is selected for a unique character, 1 die is selected also.
	// Dices : Manage elite non-unique characters by allowing multiple copies of them
	app.data.cards.updateById(card_code, {
		indeck: {
			cards: dices ? dices.length : nb_copies,
			dice: dices ? dices.length : (card.has_die ? nb_copies : 0),
			dices: dices
		}
	});
	
	app.deck_history && app.deck_history.notify_change();

	//list of cards which, by rules, deny or allow some cards, or modify cards' max quantity
	if(_.includes(['01045', '07089', '08090', '08135', '08143', '09141', '09142', '12003'], card_code))
		updated_other_card = true; //force list refresh

	return updated_other_card;
}

/**
 * Change only the number of dice
 *
 * @memberOf deck
 * @return boolean true if at least one other card quantity was updated
 */
deck.set_card_dice = function set_card_dice(card_code, nb_dice) {
	var card = app.data.cards.findById(card_code);
	if(!card) return false;

	var updated_other_card = false;

	// card-specific rules
	switch(card.type_code) {
		
	}

	// control if card has no die, no dice can be set in deck
	app.data.cards.updateById(card_code, {
		indeck: {
			dice: card.has_die ? nb_dice : 0
		}
	});
	app.deck_history && app.deck_history.notify_change();

	return updated_other_card;
}

/**
 * @memberOf deck
 */
deck.get_content = function get_content() {
	var cards = deck.get_cards();
	var content = {};
	cards.forEach(function (card) {
		content[card.code] = {
			quantity: card.indeck.cards,
			dice: card.indeck.dice,
			dices: (card.indeck.dices ? card.indeck.dices.join(',') : null)
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
					nb_copies: card.indeck.cards,
					deck_limit: card.deck_limit
			};
		} else {
			value.nb_copies += card.indeck.cards;
			value.deck_limit = Math.min(card.deck_limit, value.deck_limit);
		}
	})
	return copies_and_deck_limit;
}

/**
 * @memberOf deck
 */
deck.get_problem = function get_problem() {
	
	// at least 30 others cards (40 if RM #101  plot is present)
	var deckSize = deck.is_included('15101') ? 40 : 30;
	if(deck.get_draw_deck_size() != deckSize) {
		return 'incorrect_size';
	}

	if(deck.get_character_points()+deck.get_plot_points() > 30) {
		return 'too_many_points';
	}

	if(!deck.get_battlefields() || deck.get_battlefields().length == 0) {
		return 'no_battlefield';
	}

	if(deck.get_battlefields().length > (deck.is_included('07127') == 1 ? 2 : 1)) {
		return 'too_many_battlefields';
	}

	if(!deck.check_plots()) {
		return 'plot';
	}

	// too many copies of one card
	var deckLimits = _(deck.get_copies_and_deck_limit())
		.values()
		.filter(v => v.nb_copies > v.deck_limit)
		.map(v => v.nb_copies - v.deck_limit)
		.value();
	if(deckLimits.length > (deck.is_included('08143') || deck.is_included('09114') ? 2 : 0))
		return 'too_many_copies';
	if((deck.is_included('08143') || deck.is_included('09114')) && _.some(deckLimits, v => v > 1))
		return 'too_many_copies';

	/* Leia and Enfys Nest limits unimplemented until official aclarations about using them with Finn, Qi'Ra an Bo-Katan
	if(deck.is_included('08090') && deck.get_nb_cards(deck.get_cards(null, {affiliation_code: 'villain'})) > 5)
		return 'too_many_copies';

	if(deck.is_included('09141') || deck.is_included('09142'))
	{
		var limit = deck.is_included('09141') ? 2 : 1;
		var otherAffiliation = affiliation_code === 'villain' ? 'hero' : 'villain';
		if(deck.get_nb_cards(deck.get_cards(null, {affiliation_code: otherAffiliation})) > limit)
			return 'too_many_copies';
	}
	*/

	if(deck.is_included('12003')) {
		var heroCards = deck.get_cards(null, {affiliation_code: 'hero'});
		if(heroCards.length > 4 || deck.get_nb_cards(heroCards) > heroCards.length) {
			return 'too_many_copies';
		}
	}

	// no invalid card
	if(deck.get_invalid_cards().length > 0) {
		return 'invalid_cards';
	}

	// cards included from different faction of characters
	if(deck.get_notmatching_cards().length > 0) {
		return 'faction_not_included';
	}

	// restricted list, no more than 1 of the restricted list can be included in the deck
	if(deck.get_restricted_count() > 1) {
		return 'restricted_list';
	}

	return null;
}

var plotChecks = {
	//Retribution (AtG 54)
	'08054': function() {
		return _.some(deck.get_character_deck(), function(card) {
			var pointValue = card.is_unique ? parseInt(card.points.split('/')[card.indeck.dice-1], 10) : parseInt(card.points);
			return pointValue >= 20;
		});
	},
	//No Allegiance (AtG 155)
	'08155': function() {
		return _.every(deck.get_character_deck(), function(card) {
			return !_.includes(['villain', 'hero'], card.affiliation_code);
		});
	},
	//Solidarity (AtG 156)
	'08156': function() {
		var factions = _(deck.get_character_deck()).map('faction_code').uniq().value();
		var cards = app.data.cards.find({
			type_code: {$in: ['upgrade', 'event', 'support']},
			indeck: {cards: {$gt: 1}}
		});
		return factions.length == 1 && cards.length == 0;
	},
	//Temporarty Truce (SoH 119)
	'11119': function() {
		var greys = deck.get_draw_deck().filter(card => card.faction_code=='gray');
		var nonReylo = deck.get_cards(null, {type_code: 'character', name: {$nin: ['Rey', 'Kylo Ren']}});

		return greys.length+nonReylo.length == 0;
	},
	//Spectre Cell (CM 104)
	'12104': function() {
		return _.every(deck.get_character_deck(), function(card) {
			return _.includes(_.map(card.subtypes, 'code'), 'spectre');
		});
	}
};

deck.check_plots = function check_plots() {
	var plots = deck.get_plot_deck();
	if(plots.length == 0) {
		return true;
	} else {
		return _.every(plots, function(plot) {
			return !!!plotChecks[plot.code] || plotChecks[plot.code]();
		});
	}
}

deck.get_invalid_cards = function get_invalid_cards() {
	return _.filter(deck.get_cards(), function (card) {
		return ! deck.can_include_card(card);
	});
}

deck.get_notmatching_cards = function get_notmatching_cards() {
	var character_factions = deck.get_nongray_factions(deck.get_character_deck());
	return _.filter(deck.get_draw_deck().concat(deck.get_plot_deck()), function (card) {
		return ! deck.card_spot_faction(card, character_factions);
	});
}

/**
 * returns true if the deck can include the card as parameter
 * @memberOf deck
 */
deck.can_include_card = function can_include_card(card) {
	// card not valid in format
	if(!deck.within_format_sets(card)) return false;

	// banned card
	if(_.includes(app.deck.get_format_data().data.banned, card.code)) return false;

	// neutral card => yes
	if(card.affiliation_code === 'neutral') return true;

	// affiliation card => yes
	if(card.affiliation_code === affiliation_code) return true;

	// Finn (AW #45) special case
	if(deck.is_included('01045')) {
		if(card.affiliation_code==='villain' && card.faction_code==='red' && _.some(card.subtypes, st => ['weapon', 'vehicle'].includes(st.code)))
			return true;
	}

	// Bo-Katan Kryze (WotF #89) special case
	if(deck.is_included('07089')) {
		if(card.affiliation_code==='villain' && card.faction_code==='yellow' && card.type_code==='upgrade')
			return true;
	}

	// Leia Organa (AtG #90) special case
	if(deck.is_included('08090')) {
		if(card.affiliation_code==='villain')
			return true;
	}

	// Qi'Ra (AtG #135) special case
	if(deck.is_included('08135')) {
		if(card.faction_code==='yellow' && card.type_code==='event')
			return true;
	}

	// Enfys Nest (CONV #141) special case
	if(deck.is_included('09141')) {
		if(card.type_code !== 'character')
			return true;
	}

	// Enfys Nest's Marauder (CONV #142) special case
	if(deck.is_included('09142')) {
		if(card.type_code !== 'character')
			return true;
	}

	// Temporary Truce (SoH #119) special case
	if(deck.is_included('11119')) {
		return true;
	}

	// Pong Krell (CM #3)
	if(deck.is_included('12003')) {
		if(card.faction_code==='blue' && card.affiliation_code==='hero' && card.type_code !== 'character') {
			return true;
		}
	}

	// if none above => no
	return false;
}

/**
* returns true if the card set (or a set with a reprint of the card) is
* included within valid set of formats
* @memberOf deck
*/
deck.within_format_sets = function within_format_sets(card) {
	var set_codes = [card.set_code];
	if(_.has(card, 'reprints')) {
		card.reprints.forEach(function(code) {
			set_codes.push(app.data.cards.findById(code).set_code);
		});
	}
	if(_.has(card, 'reprint_of')) {
		set_codes.push(app.data.cards.findById(card.reprint_of).set_code);
	}
	set_codes = _.uniq(set_codes);
	return _.intersection(set_codes, deck.get_format_data().data.sets).length > 0;
}

/**
 * returns true if the card has a matching character faction
 * @memberOf deck
 */
deck.card_spot_faction = function card_spot_faction(card, character_factions) {
	character_factions = character_factions || deck.get_nongray_factions(deck.get_character_deck());

	if(card.faction_code == 'gray' || _.includes(character_factions, card.faction_code))
		return true;

	// Finn (AW #45) special case
	if(deck.get_cards(null, {code: '01045'}).length > 0) {
		if(card.affiliation_code==='villain' && card.faction_code==='red' && _.includes(['vehicle','weapon'], card.subtype_code))
			return true;
	}

	return false;
}

deck.own_enough_cards = function own_enough_cards(card) {
	if(!card.owned) return true;

	return card.indeck.cards <= card.owned.cards;
}

deck.own_enough_dice = function own_enough_dice(card) {
	if(!card.owned) return true;

	return card.indeck.dice <= card.owned.dice;
}

/**
 * returns the number of cards in the restricted list that are included in the deck
 * @memberOf deck
 */
deck.get_restricted_count = function get_restricted_count() {
	return _.reduce(deck.get_format_data().data.restricted, function(sum, code) {
		return sum + (deck.is_included(code) ? 1 : 0);
	}, 0);
}

})(app.deck = {}, jQuery);
