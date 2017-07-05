/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

window.$ = window.jQuery = require('jquery');
window._ = require('lodash');
window.KeyCode = require('keycode-js');
require('urijs');
require('materialize-css');
window.Vue = require('vue');
window.Vuex = require('vuex');
window.Models = require('./models.js');

require('./materialize_chips_autocomplete.js');
require('./materialize_autocomplete.js');
require('./materialize_datepicker_defaults.js');
require('./materialize_init.js');
require('./mainmenu.js');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.use(Vuex);

import store from "./store.js";

const nav = new Vue({
    el: 'nav',
    store,
    components: {
        'searchbar': require('./components/common/Searchbar.vue')
    }
});

const main = new Vue({
    el: 'main',
    store,
    components: {
        'person-merge-dialog': require('./components/modules/person/merge/Dialog.vue')
    }
});