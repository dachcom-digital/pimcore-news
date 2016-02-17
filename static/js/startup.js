pimcore.registerNS("pimcore.plugin.news");

pimcore.plugin.news = Class.create(pimcore.plugin.admin, {
    getClassName: function() {
        return "pimcore.plugin.news";
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },
 
    pimcoreReady: function (params,broker){
        // alert("News Ready!");
    }
});

var newsPlugin = new pimcore.plugin.news();

