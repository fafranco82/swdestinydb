(function app_textcomplete(textcomplete, $) {

var icons = 'unique battlefield character upgrade support event cards die melee ranged disrupt discard focus resource shield special blank'.split(' ');

/**
 * options: cards, icons, users
 */
textcomplete.setup = function setup(textarea, options) {

	options = _.extend({cards: true, icons: true, users: false}, options);

	var actions = [];

	if(options.cards) {
		actions.push({
			match : /\B#([\-+\w]*)$/,
			search : function(term, callback) {
				var regexp = new RegExp('\\b' + term, 'i');
				callback(app.data.cards.find({
					name : regexp
				}));
			},
			template : function(value) {
				return value.label;
			},
			replace : function(value) {
				return '[' + value.label + ']('
						+ Routing.generate('cards_zoom', {card_code:value.code})
						+ ')';
			},
			index : 1
		})
	}

	if(options.icons) {
		actions.push({
			match : /\$([\-+\w]*)$/,
			search : function(term, callback) {
				var regexp = new RegExp(term, 'i');
				callback(_.filter(icons,
					function(symbol) { return regexp.test(Translator.trans('icon.'+symbol)); }
				));
			},
			template : function(value) {
				return Translator.trans('icon.'+value);
			},
			replace : function(value) {
				return '<span class="icon-' + value + '"></span>';
			},
			index : 1
		});
	}

	if(options.users) {
		actions.push({
			match : /\B@([\-+\w]*)$/,
			search : function(term, callback) {
				var regexp = new RegExp('^' + term, 'i');
				callback($.grep(options.users, function(user) {
					return regexp.test(user);
				}));
			},
			template : function(value) {
				return value;
			},
			replace : function(value) {
				return '`@' + value + '`';
			},
			index : 1
		});
	}

	$(textarea).textcomplete(actions);

}

})(app.textcomplete = {}, jQuery);
