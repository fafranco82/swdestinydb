(function app_deck_history(deck_history, $) {

var tbody,
	clock,
	snapshots_init = [],
	snapshots = [],
	progressbar,
	timer,
	ajax_in_process = false,
	period = 60,
	changed_since_last_autosave = false;

/**
 * @memberOf deck_history
 */
deck_history.autosave = function autosave() {

	// check if deck has been modified since last autosave
	if(!changed_since_last_autosave) return;

	// compute diff between last snapshot and current deck
	var last_snapshot = snapshots[snapshots.length-1].content;
	var current_deck = app.deck.get_content();

	changed_since_last_autosave = false;

	var result = app.diff.compute_simple([current_deck, last_snapshot]);
	if(!result) return;

	var diff = result[0];
	var diff_json = JSON.stringify(diff);
	if(diff_json == '[{},{}]') return;

	// send diff to autosave
	$('#tab-header-history').html(Translator.trans('decks.history.autosave'));
	ajax_in_process = true;

	$.ajax(Routing.generate('deck_autosave'), {
		data: {
			diff: diff_json,
			deck_id: app.deck.get_id()
		},
		type: 'POST',
		success: function(data, textStatus, jqXHR) {
			deck_history.add_snapshot({datecreation: data, variation: diff, content: current_deck, is_saved: false});
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log('['+moment().format('YYYY-MM-DD HH:mm:ss')+'] Error on '+this.url, textStatus, errorThrown);
			changed_since_last_autosave = true;
		},
		complete: function () {
			$('#tab-header-history').html(Translator.trans('decks.edit.tabs.history'));
			ajax_in_process = false;
		}
	});
}

/**
 * @memberOf deck_history
 */
deck_history.autosave_interval = function autosave_interval() {
	// if we are in the process of an ajax autosave request, do nothing now
	if(ajax_in_process) return;

	// making sure we don't go into negatives
	if(timer < 0) timer = period;

	// update progressbar
	$(progressbar).css('width', (timer*100/period)+'%').attr('aria-valuenow', timer).find('span').text(timer+' seconds remaining.');

	// timer action
	if(timer === 0) {
		deck_history.autosave();
	}

	timer--;
}

var SnapshotTemplate = Handlebars.templates['deck_history-snapshot'];
/**
 * @memberOf deck_history
 */
deck_history.add_snapshot = function add_snapshot(snapshot) {
	snapshot.date_creation = snapshot.date_creation ? moment(snapshot.date_creation) : moment();
	snapshots.push(snapshot);

	tbody.prepend(SnapshotTemplate({
		snapshot: snapshot, 
		date: snapshot.date_creation.calendar(),
		revertTo: snapshots.length-1
	}));

	timer = -1; // start autosave timer

}

/**
 * @memberOf deck_history
 */
deck_history.load_snapshot = function load_snapshot(event) {

	var index = $(this).data('index');
	var snapshot = snapshots[index];
	if(!snapshot) return;

	app.data.cards.find({}).forEach(function(card) {
		var indeck = {
			cards: 0,
			dice: 0
		};
		if (snapshot.content[card.code]) {
			indeck = {
				cards: snapshot.content[card.code].quantity,
				dice: snapshot.content[card.code].dice
			}
		}
		app.data.cards.updateById(card.code, {
			indeck : indeck
		});
	});

	app.ui.on_deck_modified();
	changed_since_last_autosave = true;

	// cancel event
	return false;

}

/**
 * @memberOf deck_history
 */
deck_history.notify_change = function notify_change() {
	changed_since_last_autosave = true;
}

deck_history.get_unsaved_edits = function get_unsaved_edits() {
	return _.filter(snapshots, function (snapshot) {
		return snapshot.is_saved === false;
	}).sort(function (a, b) {
		return a.date_creation - b.datecreation;
	});
}

deck_history.is_changed_since_last_autosave = function is_changed_since_last_autosave() {
	return changed_since_last_autosave;
}

deck_history.init = function init(data) 
{
	snapshots_init = data;
}

/**
 * @memberOf deck_history
 * @param container
 */
deck_history.setup = function setup_history(container) 
{
	tbody = $(container).find('tbody').on('click', 'a[role=button]', deck_history.load_snapshot);
	progressbar = $(container).find('.progress-bar');

	clock = setInterval(deck_history.autosave_interval, 1000);
	
	snapshots_init.forEach(function (snapshot) {
		deck_history.add_snapshot(snapshot);
	});

}

deck_history.get_snapshots = function get_snapshots() {
	return snapshots;
};

})(app.deck_history = {}, jQuery);
