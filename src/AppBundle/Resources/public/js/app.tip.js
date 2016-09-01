(function app_tip(tip, $) {

var cards_zoom_regexp = /card\/(\d\d\d\d\d)$/,
	mode = 'text',
	hide_event = 'mouseout',
	template = Handlebars.compile(
'{{#if card.imagesrc}}' +
'<div class="card-thumbnail card-thumbnail-{{ternary (compare card.type_code "battlefield") 4 3}}x card-thumbnail-{{card.type_code}}" style="background-image:url(\'{{card.imagesrc}}\')"> ' +
'</div>' +
'{{/if}}' +
'<h4 class="card-name">' +
'	{{#if card.is_unique}}<span class="icon-unique"></span>{{/if}}' +
'	{{card.name}}' +
'	{{#if card.subtitle}}' +
'	<br/><span class="card-subtitle">{{card.subtitle}}</span>' +
'	{{/if}}' +
'</h4>' +
'<div class="card-faction">' +
'	<span class="card-affiliation">{{card.affiliation_name}}</span>.' +
'	<span class="card-faction">{{card.faction_name}}</span>.' +
'	<span class="card-rarity">{{card.rarity_name}}</span>.' +
'</div>' +
'<div class="card-info">' +
'	<span class="card-type">{{ card.type_name }}{{#if card.subtype_code }} - {{card.subtype_name}}{{/if}}.</span>' +
'	<span class="card-props">' +
'		{{#compare card.type_code "character"}}' +
'			{{trans "card.info.points"}}: {{card.points}}.' +
'		{{/compare}}' +
'		{{#in card.type_code "upgrade" "support" "event"}}' +
'			{{trans "card.info.cost"}}: {{int_or_x card.cost}}.' +
'		{{/in}}' +
'		{{#compare card.type_code "character"}}' +
'			{{trans "card.info.health"}}: {{int_or_x card.health}}.' +
'		{{/compare}}' +
'	</span>' +
'</div>' +
'{{#if card.has_die}}' +
'<div class="card-die">' +
'  {{#each card.sides}}' +
'  {{#with (dieside this)}}' +
'  <div class="card-die-face border-{{@root.card.faction_code}} card-die-face-{{icon}}{{#unless cost}} card-die-face-nocost{{/unless}}{{#if modifier}} card-die-face-modifier{{/if}}">' +
'    <div class="card-die-face-content">' +
'      <span class="card-die-face-value">{{#if modifier}}+{{/if}}{{value}}</span><span class="icon-{{icon}}"></span>' +
'    </div>' +
'    {{#if cost}}' +
'    <div class="card-die-face-cost">' +
'      {{cost}}<span class="icon-resource"></span>' +
'    </div>' +
'    {{/if}}' +
'  </div>' +
'  {{/with}}' +
'  {{/each}}' +
'</div>' +
'{{/if}}' +
'<div class="card-text border-{{card.faction_code}}">{{text card.text}}</div>' +
'<div class="card-set">{{card.set_name}} #{{card.position}}.</div>'
	);

function display_card_on_element(card, element, event) {
	var content;
	if(mode == 'text') {
		content = template({card: card});
	}
	else {
		content = card.imagesrc ? '<img src="'+card.imagesrc+'">' : "";
	}

	var qtip = {
		content : {
			text : content
		},
		style : {
			classes : 'card-content qtip-bootstrap qtip-swdestinydb qtip-swdestinydb-' + mode
		},
		position : {
			my : mode == 'text' ? 'center left' : 'top left',
			at : mode == 'text' ? 'center right' : 'bottom right',
			viewport : $(window)
		},
		show : {
			event : event.type,
			ready : true,
			solo : true
		},
		hide: {
			event: hide_event
		}
	};
	
	$(element).qtip(qtip, event);
}

/**
 * @memberOf tip
 * @param event
 */
tip.display = function display(event) {
	var code = $(this).data('code');
	var card = app.data.cards.findById(code);
	if (!card) return;
	display_card_on_element(card, this, event);
};

/**
 * @memberOf tip
 * @param event
 */
tip.guess = function guess(event) {
	if($(this).hasClass('no-popup')) return;
	var href = $(this).get(0).href;
	if(href && href.match(cards_zoom_regexp)) {
		var code = RegExp.$1;
		var generated_url = Routing.generate('cards_zoom', {card_code:code}, true);
		var card = app.data.cards.findById(code);
		if(card && href === generated_url) {
			display_card_on_element(card, this, event);
		}
	}
}

tip.set_mode = function set_mode(opt_mode) {
	if(opt_mode == 'text' || opt_mode == 'image') {
		mode = opt_mode;
	}
}

tip.set_hide_event = function set_hide_event(opt_hide_event) {
	if(opt_hide_event == 'mouseout' || opt_hide_event == 'unfocus') {
		hide_event = opt_hide_event;
	}
}

$(document).on('start.app', function () {
	$('body').on({
		mouseover : tip.display
	}, 'a.card-tip');

	$('body').on({
		mouseover : tip.guess
	}, 'a:not(.card-tip)');
});

})(app.tip = {}, jQuery);
