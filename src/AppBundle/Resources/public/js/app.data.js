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

    data.collections = ['formats', 'sets', 'cards'];

    data.masters = {};
    data.collections.forEach(function(collection) {
        data.masters[collection] = data.db.collection('master_'+collection, {primaryKey: 'code', changeTimestamp: true});
    });

    data.dfd = {};
    data.collections.forEach(function(collection) {
        data.dfd[collection] = new $.Deferred();
    });


    $.when.apply(_.map(data.collections, _.partial(_.get, data.dfd))).done(data.update_done).fail(data.update_fail);

    async.eachSeries(data.collections, function(collection, done) {
        data.masters[collection].load(function(err) {
            if(err) {
                console.log('error when loading '+collection, err);
                done(err);
            } else {
                //if database is older than 10 days, we assume it's obsolete and delete it
                var age_of_database = new Date() - new Date(data.masters[collection].metaData().lastChange);
                if(age_of_database > 864000000) {
                    console.log(collection+' database is older than 10 days => refresh it');
                    data.masters[collection].setData([]);
                }

                //if database is empty, we will wait for the new data
                if(data.masters[collection].count() === 0) {
                    console.log(collection+' database is empty => load it');
                    force_update = true;
                }

                done();
            }
        });
    }, function(err) {
        if(err) {
            force_update = true;
        }

        //triggering event that data is loaded
        if(!force_update) {
            data.release();
        }

        //then we ask the server if new data is available
        data.query();
    });
};

/**
 * release the data for consumption by other modules
 * @memberOf data
 */
data.release = function release() {
    async.each(data.collections, function(collection, done) {
        data[collection] = data.db.collection(collection, {primaryKey: 'code', changeTimestamp: false});
        data[collection].setData(data.masters[collection].find());
        done();
    }, function(err) {
        data.isLoaded = true;
        $(document).trigger('data.app');
    });
}

/**
 * triggers a forced update of the database
 * @memberOf data
 */
data.update = function update() {
    _.each(data.masters, function(collection) {
        collection.drop();
    });
    data.load();
}

/**
 * queries the server to update data
 * @memberOf data
 */
data.query = function query() {
    async.each(data.collections, function(collection, done) {
        $.ajax({
            url: Routing.generate('api_'+collection),
            success: function(response, textStatus, jqXHR) {
                var master = data.masters[collection];
                var deferred = data.dfd[collection];

                var lastModified = new Date(jqXHR.getResponseHeader('Last-Modified'));
                var lastChangeDatabase = new Date(master.metaData().lastChange);
                var isCollectionUpdated = false;

                /*
                 * if we decided to force the update,
                 * or if the database is fresh,
                 * or if the database is older than the data,
                 * then we update the database
                 */
                if(force_update || !lastChangeDatabase || lastChangeDatabase < lastModified) {
                    console.log(collection+' data is newer than database or update forced => update the database')
                    master.setData(response);
                    isCollectionUpdated = true;
                }

                master.save(function (err) {
                    if(err) {
                        console.log('error when saving '+master.name(), err);
                        deferred.reject(true)
                    } else {
                        deferred.resolve(isCollectionUpdated);
                    }
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('error when requesting '+collection, errorThrown);
                data.dfd[collection].reject(false);
            }
        });
        done();
    }, function(err) {

    });
};

/**
 * called if all operations (load+update) succeed (resolve)
 * deferred returns true if data has been updated
 * @memberOf data
 */
data.update_done = function update_done(updated) {
    if(force_update) {
        data.release();
        return;
    }

    if(updated) {
        /*
         * we display a message informing the user that they can reload their page to use the updated data
         * except if we are on the front page, because data is not essential on the front page
         */
        if($('.site-title').size() === 0) {
            var message = "A new version of the data is available. Click <a href=\"javascript:window.location.reload(true)\">here</a> to reload your page.";
            //app.ui.insert_alert_message('warning', message);
            alert(message);
        }
    }
};


/**
 * called if an operation (load+update) fails (reject)
 * deferred returns true if data has been loaded
 * @memberOf data
 */
data.update_fail = function update_fail(loaded) {
    if(!loaded) {
        //var message = Translator.trans('data_load_fail');
        //app.ui.insert_alert_message('danger', message);
    } else {
        /*
         * since data hasn't been persisted, we will have to do the query next time as well
         * -- not much we can do about it
         * but since data has been loaded, we call the promise
         */
        data.release();
    }
};

$(function() {
    data.load();
});

})(app.data = {}, jQuery);