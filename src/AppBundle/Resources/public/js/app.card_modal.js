(function app_card_modal(card_modal, $) {

var modal = null;

/**
 * @memberOf card_modal
 */
card_modal.display_modal = function display_modal(event, element) {
	event.preventDefault();
	$(element).qtip('destroy', true);
	fill_modal($(element).data('code'));
};

/**
 * @memberOf card_modal
 */
card_modal.typeahead = function typeahead(event, card) {
	fill_modal(card.code);
	$('#cardModal').modal('show');
};

var NameTemplate = Handlebars.compile(
	'{{#if card.is_unique}}<span class="icon-unique"></span>{{/if}}' +
	'{{card.name}}' +
	'{{#if card.subtitle}}' +
	' - <span class="card-subtitle">{{card.subtitle}}</span>' +
	'{{/if}}'
);

var InfoTemplate = Handlebars.compile(
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

var OptionsTemplate = Handlebars.compile(
	'<div class="btn-group" data-toggle="buttons">' +
	'	{{#range 0 card.maxqty.cards inclusive=true}}' +
	'	<label class="btn btn-xs btn-default{{#compare this ../card.indeck.cards}} active{{/compare}}">' +
	'		<input type="radio" name="qty-{{../card.code}}" value="{{this}}"/>' +
	'		{{this}}' +
	'	</label>' +
	'	{{/range}}' +
	'</div>' +
	'{{#if second_die}} ' +
	'<div class="btn-group" data-toggle="buttons">' +
	'	<label class="btn btn-default btn-xs">' +
	'		<input type="checkbox" name="2nd-{{card.code}}" value="2"/>2 <span class="icon-die"></span>' +
	'	</label>' +
	'</div>' +
	'{{/if}}'
);

function fill_modal (code) {
	var card = app.data.cards.findById(code),
		modal = $('#cardModal');

	if(!card) return;

	modal.data('code', code);
	modal.find('.card-modal-link').attr('href', card.url);
	modal.find('h3.modal-title').html(NameTemplate({card: card}));
	modal.find('.modal-image').html(card.imagesrc ? '<img class="img-responsive" src="'+card.imagesrc+'">' : '');
	modal.find('.modal-info').html(InfoTemplate({card: card}));

	var qtyelt = modal.find('.modal-qty');
	if(qtyelt) {

		qtyelt.html(OptionsTemplate({
			card: card,
			second_die: card.type_code=='character' && card.is_unique && card.maxqty.dice > 1
		}));

		qtyelt.find('input[name="2nd-' + card.code + '"]').each(function(i, element) {
			// if that switch is NOT the one with the new quantity, uncheck it
			// else, check it
			if($(element).val() != card.indeck.dice) {
				$(element).prop('checked', false).closest('label').removeClass('active');
			} else {
				$(element).prop('checked', true).closest('label').addClass('active');
			}
		});

	} else {
		if(qtyelt) qtyelt.closest('.row').remove();
	}
}

$(function () {

	$('body').on({click: function (event) {
		var element = $(this);
		if(event.shiftKey || event.altKey || event.ctrlKey || event.metaKey) {
			event.stopPropagation();
			return;
		}
		card_modal.display_modal(event, element);
	}}, '.card');

})

})(app.card_modal = {}, jQuery);
