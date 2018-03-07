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
import {substituteNewlineWithBr} from "./filters";
import VEditDialog from "./components/common/VEditDialog.vue";
import BIcon from "./components/common/BIcon.vue";


Vue.component('app-identifier', identifier);
Vue.component('app-tile', tile);
Vue.component('app-chip', chip);
Vue.component('app-edit-dialog', VEditDialog);
Vue.component('b-icon', BIcon);
Vue.filter('substituteNewlineWithBr', substituteNewlineWithBr);

new Vue({
    el: 'app-notifications',
    store,
    components: {
        'app-notifications': notifications
    }
});

new Vue({
    el: 'app-snackbar',
    store,
    components: {
        'app-snackbar': snackbar
    }
});

new Vue({
    el: '#bottom',
    store,
    components: {
        'app-footer': footer
    }
});

new Vue({
    el: '#top',
    store,
    components: {
        'app-toolbar': toolBar,
        'app-menu': menu,
        'app-user-loader': userLoader,
        'app-global-select-loader': globalSelectLoader,
        'app-legacy-loader': legacyLoader,
        'app-messages-loader': messagesLoader,
        'app-messages': messages,
    }
});