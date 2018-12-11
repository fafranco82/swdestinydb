(function() {
    Handlebars.registerHelper('trans', function(key, opt) {
        return new Handlebars.SafeString(Translator.trans(key, opt.hash));
    });

    Handlebars.registerHelper('transChoice', function(key, value, opt) {
        return new Handlebars.SafeString(Translator.transChoice(key, value, opt.hash));
    });

    Handlebars.registerHelper('int_or_x', function(value, opt) {
        if(_.isNaN(_.toNumber(value)))
            return 'X';
        else
            return value;
    });

    Handlebars.registerHelper('nonzero_or_x', function(value, opt) {
        if(value==0)
            return 'X';
        else
            return value;
    });

    Handlebars.registerHelper('text', function(text, opt) {
        var str = text || '';
        var icons = {
            'blank': '<span class="icon-blank"></span>',
            'discard': '<span class="icon-discard"></span>',
            'disrupt': '<span class="icon-disrupt"></span>',
            'focus': '<span class="icon-focus"></span>',
            'melee': '<span class="icon-melee"></span>',
            'ranged': '<span class="icon-ranged"></span>',
            'indirect': '<span class="icon-indirect"></span>',
            'shield': '<span class="icon-shield"></span>',
            'resource': '<span class="icon-resource"></span>',
            'special': '<span class="icon-special"></span>',
            'unique': '<span class="icon-unique"></span>',
            'AW': '<span class="icon-set-AW"></span>',
            'SoR': '<span class="icon-set-SoR"></span>',
            'EaW': '<span class="icon-set-EaW"></span>',
            'TPG': '<span class="icon-set-TPG"></span>',
            'LEG': '<span class="icon-set-LEG"></span>',
            'RIV': '<span class="icon-set-RIV"></span>',
            'WotF': '<span class="icon-set-WotF"></span>',
            'AtG': '<span class="icon-set-AtG"></span>',
            'CON': '<span class="icon-set-CON"></span>'
        };
        
        _.forEach(icons, function(span, key) {
            str = str.replace(new RegExp("\\["+key+"\\]", "g"), span);
        });
        str = str.split("\n").join('</p><p>');
        return new Handlebars.SafeString('<p>'+str+'</p>');
    });

    Handlebars.registerHelper('set_icon', function(code) {
        return new Handlebars.SafeString('<span class="icon-set-'+code+'"></span>');
    });

    Handlebars.registerHelper('dieside', function(side) {
    	var codes = {'-': 'blank', 'MD': 'melee', 'RD': 'ranged', 'ID': 'indirect', 'Dr': 'disrupt', 'Dc': 'discard', 'F': 'focus', 'R': 'resource', 'Sp': 'special', 'Sh': 'shield', '*': ''};
    	var elems = /^([-+]?)(\d+|X)?([-*A-VYZ][a-zA-Z]?)(\d*?)$/.exec(side);
        var side = {
            code: elems[3],
            icon: codes[elems[3]],
            cost: elems[4],
            modifier: elems[1] ? '1' : null,
            value: elems[2]
        };
        return side;
    });

    Handlebars.registerHelper('card', function(code) {
        if(app.data && app.data.cards) {
            return app.data.cards.findById(code);
        }
        return {};
    });

    Handlebars.registerHelper('format', function(code) {
        if(app.data && app.data.formats) {
            return app.data.formats.findById(code);
        }
        return {};
    });

    Handlebars.registerHelper('legal', function(card_code, format_code) {
        if(arguments < 2)
            throw new Error("Handlerbars Helper 'legal' needs 2 parameters");

        if(app.data && app.data.formats && app.data.cards) {
            var card = app.data.cards.findById(card_code);
            var format = app.data.formats.findById(format_code);

            //if card's set included in legal sets of the format
            if(_.includes(format.data.sets, card.set_code))
                return true;

            //if not, but the card is a reprint of a legal card...
            if(card.reprint_of) {
                var reprint = app.data.cards.findById(card.reprint_of);
                if(_.includes(format.data.sets, reprint.set_code))
                    return true;
            }

            //or the card has a reprint that is legal...
            if(card.reprints) {
                var legal = _.some(card.reprints, function(reprint_code) {
                    var reprint = app.data.cards.findById(reprint_code);
                    return _.includes(format.data.sets, reprint.set_code);
                });
                if(legal)
                    return true;
            }

            return false;
        }
        
        return false;
    });

    Handlebars.registerHelper('balance', function(card_code, format_code) {
        if(arguments < 2)
            throw new Error("Handlerbars Helper 'legal' needs 2 parameters");

        if(app.data && app.data.formats && app.data.cards) {
            var card = app.data.cards.findById(card_code);
            var format = app.data.formats.findById(format_code);

            if(card.reprint_of) {
                card = app.data.cards.findById(card.reprint_of);
            }

            if(_.has(format.data.balance, card.code))
                return new Handlebars.SafeString("<b>"+format.data.balance[card.code]+"</b>");
            else
                return new Handlebars.SafeString(card.points);
        }

        return new Handlebars.SafeString("");
    });

    Handlebars.registerHelper('routing', function(path, options) {
        return Routing.generate(path, options.hash || {});
    });

    Handlebars.registerHelper("math", function(lvalue, operator, rvalue, options) {
        lvalue = parseFloat(lvalue);
        rvalue = parseFloat(rvalue);
            
        return {
            "+": lvalue + rvalue,
            "-": lvalue - rvalue,
            "*": lvalue * rvalue,
            "/": lvalue / rvalue,
            "%": lvalue % rvalue
        }[operator];
    });

    Handlebars.registerHelper('concat', function() {
    	var str = '';
    	for(var i=0;i < arguments.length-1;i++) {
    		str += arguments[i];
    	}
    	return str;
    });

    Handlebars.registerHelper('compare', function(lvalue, rvalue, options) {
        if (arguments.length < 3)
            throw new Error("Handlerbars Helper 'compare' needs 2 parameters");

        var operator = options.hash.operator || "==";

        var operators = {
            '==':       function(l,r) { return l == r; },
            '===':      function(l,r) { return l === r; },
            '!=':       function(l,r) { return l != r; },
            '<':        function(l,r) { return l < r; },
            '>':        function(l,r) { return l > r; },
            '<=':       function(l,r) { return l <= r; },
            '>=':       function(l,r) { return l >= r; },
            'typeof':   function(l,r) { return typeof l == r; }
        }

        if (!operators[operator])
            throw new Error("Handlerbars Helper 'compare' doesn't know the operator "+operator);

        var result = operators[operator](lvalue,rvalue);

        if(typeof options.fn === 'function') {
            if( result ) {
                return options.fn(this);
            } else {
                return options.inverse(this);
            }
        } else {
            return result;
        }
    });

    Handlebars.registerHelper('ternary', function(cond, true_value, false_value, options) {
        if (arguments.length < 3)
            throw new Error("Handlerbars Helper 'ternary' needs at least 2 parameters");

        if(arguments.length < 4) {
            options = false_value;
            false_value = undefined;
        }

        if(cond) {
            return true_value;
        } else {
            return false_value;
        }
    });

    Handlebars.registerHelper('in', function(needle) {
        if (arguments.length < 3)
            throw new Error("Handlerbars Helper 'in' needs at least 2 parameters");

        var haystack = Array.prototype.slice.call(arguments, 1, arguments.length-1);
        var result = haystack.indexOf(needle) >= 0;
        var options = arguments[arguments.length-1];

        if(typeof options.fn === 'function') {
            if( result ) {
                return options.fn(this);
            } else {
                return options.inverse(this);
            }
        } else {
            return result;
        }
    });

    Handlebars.registerHelper('range', function() {
        var rangeArgs = Array.prototype.slice.call(arguments, 0, arguments.length-1);
        var options = arguments[arguments.length-1];

        if(options.hash && options.hash.inclusive)
            rangeArgs[1] += 1;

        return _.range.apply(null, rangeArgs).map(function(num) {
            return options.fn(num);
        }).join('');
    });

})();