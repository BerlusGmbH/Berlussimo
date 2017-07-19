/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import Vue from "vue";
import Vuex from "vuex";
import Vuetify from "vuetify";
import store from "./store";
import jQuery from "jquery";
import lodash from "lodash";
import keycodeJs from "keycode-js";
import Echo from "laravel-echo";
import userLoader from "./components/auth/UserLoader.vue";
import toolBar from "./components/shared/Toolbar.vue";
import login from "./components/auth/Login.vue";
import footer from "./components/shared/Footer.vue";
import menu from "./components/shared/Menu.vue";
//import personMergeDialog from "./components/modules/person/merge/Dialog.vue";
//import notifications from "./components/common/Notifications.vue";
import personShow from "./components/modules/person/Show.vue";
import identifier from "./components/common/identifiers/Identifier.vue";
import {substituteNewlineWithBr} from "./filters";

window['$'] = window['jQuery'] = jQuery;
window['_'] = lodash;
window['KeyCode'] = keycodeJs;
window['Vue'] = Vue;
window['Vuex'] = Vuex;
window['Echo'] = new Echo({
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
Vue.component('app-identifier', identifier);
Vue.filter('substituteNewlineWithBr', substituteNewlineWithBr);

new Vue({
    el: 'v-app',
    store,
    components: {
        //'person-merge-dialog': personMergeDialog,
        //'notifications': notifications,
        'app-login': login,
        'app-toolbar': toolBar,
        'app-footer': footer,
        'app-menu': menu,
        'app-user-loader': userLoader,
        'app-person-show': personShow
    }
});