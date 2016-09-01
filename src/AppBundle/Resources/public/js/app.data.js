(function app_data(data, $) {

var force_update = false;

/**
 * loads the database from local
 * sets up a Promise on all data loading/updating
 * @memberOf data
 */
data.load = function load() {
	
	data.isLoaded = false;

	var fdb = new ForerunnerDB();
	data.db = fdb.db('swdestinydb');

	data.masters = {
		sets: data.db.collection('master_set', {primaryKey:'code', changeTimestamp: true}),
		cards: data.db.collection('master_card', {primaryKey:'code', changeTimestamp: true})
	};

	data.dfd = {
		sets: new $.Deferred(),
		cards: new $.Deferred()
	};

	$.when(data.dfd.sets, data.dfd.cards).done(data.update_done).fail(data.update_fail);

	data.masters.sets.load(function (err) {
		if(err) {
			console.log('error when loading sets', err);
			force_update = true;
		}
		data.masters.cards.load(function (err) {
			if(err) {
				console.log('error when loading cards', err);
				force_update = true;
			}

			/*
			 * data has been fetched from local store
			 */

			/*
			 * if database is older than 10 days, we assume it's obsolete and delete it
			 */
			var age_of_database = new Date() - new Date(data.masters.cards.metaData().lastChange);
			if(age_of_database > 864000000) {
				console.log('database is older than 10 days => refresh it');
				data.masters.sets.setData([]);
				data.masters.cards.setData([]);
			}

			/*
			 * if database is empty, we will wait for the new data
			 */
			if(data.masters.sets.count() === 0 || data.masters.cards.count() === 0) {
				console.log('database is empty => load it');
				force_update = true;
			}

			/*
			 * triggering event that data is loaded
			 */
			if(!force_update) {
				data.release();
			}

			/*
			 * then we ask the server if new data is available
			 */
			data.query();
		});
	});
}

/**
 * release the data for consumption by other modules
 * @memberOf data
 */
data.release = function release() {
	data.sets = data.db.collection('set', {primaryKey:'code', changeTimestamp: false});
	data.sets.setData(data.masters.sets.find());

	data.cards = data.db.collection('card', {primaryKey:'code', changeTimestamp: false});
	data.cards.setData(data.masters.cards.find());

	data.isLoaded = true;
	
	$(document).trigger('data.app');
}

/**
 * triggers a forced update of the database
 * @memberOf data
 */
data.update = function update() {
	_.each(data.masters, function (collection) {
		collection.drop();
	});
	data.load();
}

/**
 * queries the server to update data
 * @memberOf data
 */
data.query = function query() {
	$.ajax({
		url: Routing.generate('api_sets'),
		success: data.parse_sets,
		error: function (jqXHR, textStatus, errorThrown) {
			console.log('error when requesting sets', errorThrown);
			data.dfd.sets.reject(false);
		}
	});

	$.ajax({
		url: Routing.generate('api_cards'),
		success: data.parse_cards,
		error: function (jqXHR, textStatus, errorThrown) {
			console.log('error when requesting cards', errorThrown);
			data.dfd.cards.reject(false);
		}
	});
};

/**
 * called if all operations (load+update) succeed (resolve)
 * deferred returns true if data has been updated
 * @memberOf data
 */
data.update_done = function update_done(sets_updated, cards_updated) {
	if(force_update || (sets_updated[1] === true && cards_updated[1] === true)) {
		data.release();
		return;
	}

	if(sets_updated[0] === true || cards_updated[0] === true) {
		/*
		 * we display a message informing the user that they can reload their page to use the updated data
		 * except if we are on the front page, because data is not essential on the front page
		 */
		if($('.site-title').size() === 0) {
			var message = "A new version of the data is available. Click <a href=\"javascript:window.location.reload(true)\">here</a> to reload your page.";
			app.ui.insert_alert_message('warning', message);
		}
	}
};

/**
 * called if an operation (load+update) fails (reject)
 * deferred returns true if data has been loaded
 * @memberOf data
 */
data.update_fail = function update_fail(sets_loaded, cards_loaded) {
	if(sets_loaded === false || cards_loaded === false) {
		var message = Translator.trans('data_load_fail');
		app.ui.insert_alert_message('danger', message);
	} else {
		/*
		 * since data hasn't been persisted, we will have to do the query next time as well
		 * -- not much we can do about it
		 * but since data has been loaded, we call the promise
		 */
		data.release();
	}
};

/**
 * updates the database if necessary, from fetched data
 * @memberOf data
 */
data.update_collection = function update_collection(data, collection, lastModifiedData, locale, deferred) {
	var lastChangeDatabase = new Date(collection.metaData().lastChange);
	var lastLocale = collection.metaData().locale;
	var isCollectionUpdated = false;

	/*
	 * if we decided to force the update,
	 * or if the database is fresh,
	 * or if the database is older than the data,
	 * or if the locale has changed
	 * then we update the database
	 */
	if(force_update || !lastChangeDatabase || lastChangeDatabase < lastModifiedData || locale != lastLocale) {
		console.log('data is newer than database or update forced or locale has changed => update the database')
		collection.setData(data);
		collection.metaData().locale = locale;
		isCollectionUpdated = true;
	}

	collection.save(function (err) {
		if(err) {
			console.log('error when saving '+collection.name(), err);
			deferred.reject(true)
		} else {
			deferred.resolve(isCollectionUpdated, locale != lastLocale);
		}
	});
}

/**
 * handles the response to the ajax query for sets data
 * @memberOf data
 */
data.parse_sets = function parse_sets(response, textStatus, jqXHR) {
	var lastModified = new Date(jqXHR.getResponseHeader('Last-Modified'));
	var locale = jqXHR.getResponseHeader('Content-Language');
	data.update_collection(response, data.masters.sets, lastModified, locale, data.dfd.sets);
};

/**
 * handles the response to the ajax query for the cards data
 * @memberOf data
 */
data.parse_cards = function parse_cards(response, textStatus, jqXHR) {
	var lastModified = new Date(jqXHR.getResponseHeader('Last-Modified'));
	var locale = jqXHR.getResponseHeader('Content-Language');
	data.update_collection(response, data.masters.cards, lastModified, locale, data.dfd.cards);
};

$(function() {
	data.load();
});

})(app.data = {}, jQuery);
