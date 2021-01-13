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

var NameTemplate = Handlebars.templates['card_modal-name'];
var InfoTemplate = Handlebars.templates['card_modal-info'];

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
		qtyelt.html(app.ui.build_quantity_options(card, 'modal'));
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
	
	$('[data-dismiss="modal"]').on({ click: function(event) {
		// try to empty filter text if needed
		setTimeout(function () {
			let tmp = document.createElement("div");
   			tmp.innerHTML = $('#filter-text').val();
			$('#filter-text').typeahead('val', tmp.textContent || tmp.innerText || "").focus();
		}, 100);
	}});

})

})(app.card_modal = {}, jQuery);
