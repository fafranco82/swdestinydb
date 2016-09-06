(function app_diff(diff, $) {

diff.compute_property = function compute_property(contents, prop) {
	var ensembles = _.map(contents, function(deck) {
		return _(deck).map(function(qtys, code) {
			return _.times(qtys[prop], _.constant(code));
		}).flatten().value();
	});
	
	var conjunction = [];
	for(var i=0; i<ensembles[0].length; i++) {
		var code = ensembles[0][i];
		var indexes = [ i ];
		for(var j=1; j<ensembles.length; j++) {
			var index = ensembles[j].indexOf(code);
			if(index > -1) indexes.push(index);
			else break;
		}
		if(indexes.length === ensembles.length) {
			conjunction.push(code);
			for(var j=0; j<indexes.length; j++) {
				ensembles[j].splice(indexes[j], 1);
			}
			i--;
		}
	}
	
	var listings = _(ensembles).map(function(e) {
		return _(e).countBy().mapValues(function(v) { 
			var result = {};
			result[prop] = v;
			return result;
		}).value();
	}).value();
	var intersect = _(conjunction).countBy().mapValues(function(v) { 
			var result = {};
			result[prop] = v;
			return result;
		}).value();
	
	return [ listings, intersect ];
};

var merge_results = function merge_results(cards, dice) {
	return _(cards).mapValues(_.partial(_.set, _, 'dice', 0)).merge(dice).value();
}
/**
 * contents is an array of content
 * content is a hash of pairs code-qtys
 * qtys is a object with cards and dice quantities
 * @memberOf diff
 */
diff.compute_simple = function compute_simple(contents) {
	var diff_cards = diff.compute_property(contents, 'quantity');
	var diff_dice = diff.compute_property(contents, 'dice');

	return _.map(_.zip(diff_cards, diff_dice), function(elem, i) {
		if(i==0) {
			return _.map(_.zip(elem[0], elem[1]), function(elem2) {
				return merge_results(elem2[0], elem2[1]);
			});
		} else {
			return merge_results(elem[0], elem[1]);
		}
	});
};

})(app.diff = {}, jQuery);
