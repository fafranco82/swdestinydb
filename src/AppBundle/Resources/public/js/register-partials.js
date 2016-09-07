(function() {
	_.keys(Handlebars.templates).forEach(function(key) {
		Handlebars.partials[key] = Handlebars.templates[key];
	});
})();