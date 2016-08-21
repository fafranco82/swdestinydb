(function app_format(format, $) {

/**
 * @memberOf format
 */
format.traits = function traits(card) {
	return card.traits || '';
};

/**
 * @memberOf format
 */
format.name = function name(card) {
	return (card.is_unique ? '<span class="icon-unique"></span> ' : "") + card.name;
}

format.faction = function faction(card) {
	var text = '<span class="fg-'+card.faction_code+' icon-'+card.faction_code+'"></span> '+ card.faction_name + '. ';
	if(card.faction_code != 'neutral') {
		if(card.is_loyal) text += Translator.trans('card.info.loyal')+'. ';
		else text += Translator.trans('card.info.nonloyal')+'. ';
	}
	return text;
}

/**
 * @memberOf format
 */
format.pack = function pack(card) {
	var text = card.pack_name + ' #' + card.position + '. ';
	return text;
}

/**
 * @memberOf format
 */
format.info = function info(card) {
	var text = '<span class="card-type">'+card.type_name+'. </span>';
	switch(card.type_code) {
	case 'character':
		text += Translator.trans('card.info.cost')+': '+(card.cost != null ? card.cost : 'X')+'. ';
		text += Translator.trans('card.info.str')+': '+(card.strength != null ? card.strength : 'X')+'. '
		if(card.is_military) text += '<span class="color-military icon-military" title="'+Translator.trans('challenges.military')+'"></span> ';
		if(card.is_intrigue) text += '<span class="color-intrigue icon-intrigue" title="'+Translator.trans('challenges.intrigue')+'"></span> ';
		if(card.is_power) text += '<span class="color-power icon-power" title="'+Translator.trans('challenges.power')+'"></span> ';
		break;
	case 'attachment':
	case 'location':
	case 'event':
		text += Translator.trans('card.info.cost')+': '+(card.cost != null ? card.cost : 'X')+'. ';
		break;
	case 'plot':
		text += Translator.trans('card.info.income')+': '+card.income+'. ';
		text += Translator.trans('card.info.initiative')+': '+card.initiative+'. ';
		text += Translator.trans('card.info.claim')+': '+card.claim+'. ';
		text += Translator.trans('card.info.reserve')+': '+card.reserve+'. ';
		text += Translator.trans('card.info.plotlimit')+': '+card.deck_limit+'. ';
		break;
	}
	return text;
};

/**
 * @memberOf format
 */
format.text = function text(card) {
	var text = card.text || '';
	text = text.replace(/\[(\w+)\]/g, '<span class="icon-$1"></span>')
	text = text.split("\n").join('</p><p>');
	return '<p>'+text+'</p>';
};

})(app.format = {}, jQuery);
