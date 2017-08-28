import Vue from "vue";
import Vuex from "vuex";
import Vuetify from "vuetify";
import store from "./store";
import userLoader from "./components/auth/UserLoader.vue";
import toolBar from "./components/shared/Toolbar.vue";
import login from "./components/auth/Login.vue";
import footer from "./components/shared/Footer.vue";
import menu from "./components/shared/Menu.vue";
import notifications from "./components/shared/Notifications.vue";
import globalSelectLoader from "./components/shared/GlobalSelectLoader.vue";
import workplaceLoader from "./components/shared/WorkplaceLoader.vue";
import snackbar from "./components/shared/Snackbar.vue";
import personShow from "./components/modules/person/Show.vue";
import identifier from "./components/common/identifiers/Identifier.vue";
import tile from "./components/common/tiles/Tile.vue";
import chip from "./components/common/chips/Chip.vue";
import {substituteNewlineWithBr} from "./filters";

Vue.use(Vuex);
Vue.use(Vuetify);
Vue.component('app-identifier', identifier);
Vue.component('app-tile', tile);
Vue.component('app-chip', chip);
Vue.filter('substituteNewlineWithBr', substituteNewlineWithBr);

new Vue({
    el: '#app',
    store,
    components: {
        'app-notifications': notifications,
        'app-snackbar': snackbar,
        'app-login': login,
        'app-toolbar': toolBar,
        'app-footer': footer,
        'app-menu': menu,
        'app-user-loader': userLoader,
        'app-global-select-loader': globalSelectLoader,
        'app-workplace-loader': workplaceLoader,
        'app-person-show': personShow
    }
});