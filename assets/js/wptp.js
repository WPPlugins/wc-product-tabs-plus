(function($) {
    'use strict';

    /**
     * Helper functions
     * @type {{uniqid: utils.uniqid}}
     */
    var utils = {
        /**
         * Reference: https://github.com/elliotcondon/acf/blob/8ffdf88889c8c81e7f628e8e1ef95c6de17eb02d/js/field-group.js#L76
         *
         * uniqid
         *
         * It's slightly modified by @wooninjas
         *
         * @description: JS equivelant of PHP uniqid
         * @since: 3.6
         * @created: 7/03/13
         */
        uniqid: function uniqid(prefix, more_entropy) {

            // + original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
            // + revised by: Kankrelune (http://www.webfaktory.info/)
            // % note 1: Uses an internal counter (in php_js global) to avoid collision
            // * example 1: uniqid();
            // * returns 1: 'a30285b160c14'
            // * example 2: uniqid('foo');
            // * returns 2: 'fooa30285b1cd361'
            // * example 3: uniqid('bar', true);
            // * returns 3: 'bara20285b23dfd1.31879087'
            if (typeof prefix === 'undefined') {
                prefix = "wptp";
            }

            var retId;
            var formatSeed = function (seed, reqWidth) {
                seed = parseInt(seed, 10).toString(16); // to hex str
                if (reqWidth < seed.length) { // so long we split
                    return seed.slice(seed.length - reqWidth);
                }
                if (reqWidth > seed.length) { // so short we pad
                    return Array(1 + (reqWidth - seed.length)).join('0') + seed;
                }
                return seed;
            };

            // BEGIN REDUNDANT
            if (!this.php_js) {
                this.php_js = {};
            }
            // END REDUNDANT
            if (!this.php_js.uniqidSeed) { // init seed with big random int
                this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
            }
            this.php_js.uniqidSeed++;

            retId = prefix; // start with prefix, add current milliseconds hex string
            retId += formatSeed(parseInt(new Date().getTime() / 1000, 10), 8);
            retId += formatSeed(this.php_js.uniqidSeed, 5); // add seed hex string
            if (more_entropy) {
                // for more entropy we add a float lower to 10
                retId += (Math.random() * 10).toFixed(8).toString();
            }

            return retId;
        }
    };

    var WPTP = {};

    WPTP.BlockModel = Backbone.Model.extend({
        fieldID: null,
        hideID: null,
        contentName: null,
        contentID: null,
        titleID: null
    });

    WPTP.BlocksCollection = Backbone.Collection.extend({
        model: WPTP.BlockModel
    });

    /**
     * Main tab block view
     */
    WPTP.BlocksView = Backbone.View.extend({
        el: $('#wptp'),

        events: {
            'click button#wptp-repeater-add': 'render'
        },

        initialize: function() {
            // fixes loss of context for 'this' within methods
            _.bindAll(this, 'render');

            this.collection = new WPTP.BlocksCollection;

            this.counter = 0;
        },

        render: function() {
            var blockModel = new WPTP.BlockModel;

            blockModel.set(setModelObject(utils.uniqid()));

            this.collection.add(blockModel);

            var blockView = new WPTP.BlockView({
                model: blockModel,
                parent: this
            });

            this.$el.find('#wptp-container').append(blockView.render().el);
            // Initiate editor
            blockView.editor();
            // Recognize removed/added items
            reBindSort();
            // Rebind help tip
            tipTip();

            this.counter++;
        }
    });

    /**
     * Tab block view
     */
    WPTP.BlockView = Backbone.View.extend({
        tagName: 'div',

        template: _.template($('#wptp-block-template').html()),

        className: 'wc-metabox wptp-block',

        attributes: function() {
            return {
                'data-field-id': this.model.get('fieldID')
            };
        },

        events: {
            'click .wptp-delete-block': 'remove',
            'sort.stop': function() {
                // Tinymce dies when it's DOM is changed
                // so let's reload on every sort stop
                reloadTinyMCE(this.model.get('contentID'))
            }
        },

        initialize: function(options) {
            _.bindAll(this, 'render', 'remove', 'editor');

            this.parent = options.parent;

            this.model.bind('remove', this.remove);
        },

        render: function() {
            this.$el.html(this.template(this.model.attributes));
            return this;
        },

        /**
         * Initiate tinymce
         */
        editor: function() {
            var blockModel = this.model;

            // Initiate default tab content settings
            var mceInit = $.extend({}, tinyMCEPreInit.mceInit['wptp_settings']);
            mceInit.selector = blockModel.get('contentID');

            // Initiate default tab quicktag settings
            var qtInit = $.extend({}, tinyMCEPreInit.qtInit['wptp_settings']);
            qtInit.id = mceInit.id = blockModel.get('contentID');

            // Set object for tinymce settings
            tinyMCEPreInit.mceInit[ mceInit.id ] = mceInit;

            // Set object for quicktas settings
            tinyMCEPreInit.qtInit[ qtInit.id ] = qtInit;

            // Initiate tinymce
            tinymce.init(mceInit);
            tinymce.execCommand('mceAddEditor', true, blockModel.get('contentID'));

            // Initiate quicktags for html buttons
            var qt = quicktags(qtInit);
            _buttonsInit(qt);
        },

        remove: function() {
            // Destroy editor
            destroyTinyMCE(this.model.get('contentID'));
            // Remove model from the collection
            this.parent.collection.remove(this.parent.collection.where({ fieldID: this.model.get('fieldID') }));
            // Remove DOM
            $(this.el).remove();
            // Recognize removed/newer items
            reBindSort();
        }
    });

    var blocksView = new WPTP.BlocksView();

    $('.wptp-block').each(function() {
        bindExistingTabEl($(this));
    });

    $('#wptp-container').sortable({
        handle: '.wptp-sort-block',
        stop: function(event, ui) {
            // Trigger a drop event
            ui.item.trigger('sort.stop');
        }
    });

    /**
     * Bind backbone view (WPTP.BlockView) to existing rendered DOM
     * @param el
     */
    function bindExistingTabEl(el) {
        var blockModel = new WPTP.BlockModel;

        blockModel.set(setModelObject(el.data('field-id')));

        blocksView.collection.add(blockModel);

        new WPTP.BlockView({
            model: blockModel,
            parent: blocksView,
            el: el
        });

        blocksView.counter++;
    }

    /**
     * Set blockModel object properties
     * @param uniqid String
     * @returns {{fieldID: *, hideID: string, contentName: string, contentID: string, titleID: string}}
     */
    function setModelObject(uniqid) {
        return {
            fieldID: uniqid,
            hideID: 'wptp['+uniqid+'][hide]',
            contentName: 'wptp['+uniqid+'][content]',
            contentID: 'wptp-'+uniqid+'-content',
            titleID: 'wptp['+uniqid+'][title]',
            orderID: 'wptp['+uniqid+'][order]'
        };
    }

    /**
     * Copied from wp-includes/js/quicktags.js:266
     * This method is private under quicktags
     *
     * @param ed
     * @private
     */
    function _buttonsInit(ed) {
        var defaults = ',strong,em,link,block,del,ins,img,ul,ol,li,code,more,close,';

        var canvas = ed.canvas;
        var name = ed.name;
        var settings = ed.settings;
        var html = '';
        var theButtons = {};
        var use = '';

        // set buttons
        if ( settings.buttons ) {
            use = ','+settings.buttons+',';
        }

        for ( i in edButtons ) {
            if ( !edButtons[i] ) {
                continue;
            }

            var id = edButtons[i].id;
            if ( use && defaults.indexOf( ',' + id + ',' ) !== -1 && use.indexOf( ',' + id + ',' ) === -1 ) {
                continue;
            }

            if ( !edButtons[i].instance || edButtons[i].instance === inst ) {
                theButtons[id] = edButtons[i];

                if ( edButtons[i].html ) {
                    html += edButtons[i].html(name + '_');
                }
            }
        }

        if ( use && use.indexOf(',fullscreen,') !== -1 ) {
            theButtons.fullscreen = new ed.FullscreenButton();
            html += theButtons.fullscreen.html(name + '_');
        }


        if ( 'rtl' === document.getElementsByTagName('html')[0].dir ) {
            theButtons.textdirection = new ed.TextDirectionButton();
            html += theButtons.textdirection.html(name + '_');
        }

        ed.toolbar.innerHTML = html;
        ed.theButtons = theButtons;
    }

    /**
     * Destroy tinymce instance
     * @param id
     */
    function destroyTinyMCE(id) {
        var editor = tinyMCE.get(id);
        if (editor) {
            editor.destroy();
        }
        tinyMCEPreInit.mceInit[id] = undefined;
        tinyMCEPreInit.qtInit[id] = undefined;
    }

    /**
     * Reload WP Editor
     * Using this method after sorting is stopped
     * @param id
     */
    function reloadTinyMCE(id) {
        tinymce.execCommand('mceRemoveEditor', true, id);
        tinymce.execCommand('mceAddEditor', true, id);
    }

    /**
     * Rebind sortable for new items
     * @returns {*}
     */
    function reBindSort() {
        return $('#wptp-container').sortable('refresh');
    }

    /**
     * Initiate tooltip
     * @returns {*}
     */
    function tipTip() {
        return $('.woocommerce-help-tip').tipTip({
            'attribute': 'data-tip',
            'fadeIn': 50,
            'fadeOut': 50,
            'delay': 200
        });
    }

})(jQuery);
