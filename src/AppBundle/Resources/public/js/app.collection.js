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
		actualOwned: null,
		owned: 0
	});
	data.forEach(function(owned) {
		app.data.cards.updateById(owned.card, {
			actualOwned: owned.quantity,
			owned: owned.quantity
		});
	});
	collection.isLoaded = true;
	$(document).trigger('collection.app');
}

collection.get_copies_owned = function get_copies_owned(code) {
	var card = app.data.cards.findById(code);
	if(!card)
		return null;
	return card.owned;
}

})(app.collection = {}, jQuery);