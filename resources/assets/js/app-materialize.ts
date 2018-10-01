import Vue from "./imports";
import store from "./store";
import userLoader from "./components/auth/UserLoader.vue";
import toolBar from "./components/shared/Toolbar.vue";
import footer from "./components/shared/Footer.vue";
import menu from "./components/shared/Menu.vue";
import notifications from "./components/shared/Notifications.vue";
import globalSelectLoader from "./components/shared/GlobalSelectLoader.vue";
import legacyLoader from "./components/shared/LegacyLoader.vue";
import messagesLoader from "./components/shared/MessagesLoader.vue";
import messages from "./components/shared/Messages.vue";
import snackbar from "./components/shared/Snackbar.vue";
import identifier from "./components/common/identifiers/Identifier.vue";
import tile from "./components/common/tiles/Tile.vue";
import chip from "./components/common/chips/Chip.vue";
import {dformat, nformat, sformat, substituteNewlineWithBr} from "./filters";
import VEditDialog from "./components/common/VEditDialog.vue";
import BIcon from "./components/common/BIcon.vue";


Vue.component('b-identifier', identifier);
Vue.component('b-tile', tile);
Vue.component('b-chip', chip);
Vue.component('b-edit-dialog', VEditDialog);
Vue.component('b-icon', BIcon);
Vue.filter('substituteNewlineWithBr', substituteNewlineWithBr);
Vue.filter('nformat', nformat);
Vue.filter('dformat', dformat);
Vue.filter('sformat', sformat);

new Vue({
    el: 'b-notifications',
    store,
    components: {
        'b-notifications': notifications
    }
});

new Vue({
    el: 'b-snackbar',
    store,
    components: {
        'b-snackbar': snackbar
    }
});

new Vue({
    el: '#bottom',
    store,
    components: {
        'b-footer': footer
    }
});

new Vue({
    el: '#top',
    store,
    components: {
        'b-toolbar': toolBar,
        'b-menu': menu,
        'b-user-loader': userLoader,
        'b-global-select-loader': globalSelectLoader,
        'b-legacy-loader': legacyLoader,
        'b-messages-loader': messagesLoader,
        'b-messages': messages,
    }
});