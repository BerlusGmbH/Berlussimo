/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import Vue from "vue";
import Vuex from "vuex";
import Vuetify from "vuetify";
import store from "./store.js";

window.$ = window.jQuery = require('jquery');
window._ = require('lodash');
window.KeyCode = require('keycode-js');
require('urijs');
require('materialize-css');
window.Vue = Vue;
window.Vuex = Vuex;
window.Models = require('./models.js');
window.Notifications = require('./notifications.js');

require('./materialize_chips_autocomplete.js');
require('./materialize_autocomplete.js');
require('./materialize_datepicker_defaults.js');
require('./materialize_init.js');
require('./mainmenu.js');

window.Echo = require("laravel-echo");
window.Echo = new Echo({
    broadcaster: 'nchan',
    host: '/broadcasting/events'
});

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.use(Vuex);
Vue.use(Vuetify);

const nav = new Vue({
    el: 'nav',
    store,
    components: {
        'searchbar': require('./components/common/Searchbar.vue')
    }
});

const main = new Vue({
    el: 'v-app',
    store,
    components: {
        'person-merge-dialog': require('./components/modules/person/merge/Dialog.vue'),
        'notifications': require('./components/common/Notifications.vue')
    }
});