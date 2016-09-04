(function app_collection(collection, $) {

collection.isLoaded = false;
/**
 * @memberOf collection
 */
collection.init = function init(data) {
	if(app.data.isLoaded) {
		collection.set_owned(data);
	} else {
		console.log("collection.set_owned put on hold until data.app");
		$(document).on('data.app', function () { collection.set_owned(data); });
	}
}

collection.set_owned = function set_owned(data) {
	app.data.cards.update({}, {
		owned: {
			cards: 0,
			dice: 0
		}
	});
	$.each(data, function(code, owned) {
		app.data.cards.updateById(code, {
			owned: {
				cards: owned.quantity,
				dice: owned.dice
			}
		});
	})
	collection.isLoaded = true;
	$(document).trigger('collection.app');
}

collection.get_copies_owned = function get_copies_owned(code) {
	var card = app.data.cards.findById(code);
	if(!card)
		return null;
	return card.owned.cards;
}

/**
 * @memberOf collection
 * @return boolean true if at least one other card quantity was updated
 */
collection.set_card_owns = function set_card_owns(card_code, coll, quantity) {
	var card = app.data.cards.findById(card_code);
	if(!card) return false;

	var updated_other_card = false;

	var change = {owned: {}};
	change.owned[coll] = quantity;

	app.data.cards.updateById(card_code, change);

	return updated_other_card;
}

})(app.collection = {}, jQuery);