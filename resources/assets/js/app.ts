import Vue from "./imports";
import router from "./router";
import store from "./store";
import userLoader from "./components/auth/UserLoader.vue";
import toolBar from "./components/shared/Toolbar.vue";
import footer from "./components/shared/Footer.vue";
import menu from "./components/shared/Menu.vue";
import notifications from "./components/shared/Notifications.vue";
import globalSelectLoader from "./components/shared/GlobalSelectLoader.vue";
import workplaceLoader from "./components/shared/WorkplaceLoader.vue";
import messagesLoader from "./components/shared/MessagesLoader.vue";
import messages from "./components/shared/Messages.vue";
import snackbar from "./components/shared/Snackbar.vue";

import identifier from "./components/common/identifiers/Identifier.vue";
import tile from "./components/common/tiles/Tile.vue";
import chip from "./components/common/chips/Chip.vue";
import textFieldEditDialog from "./components/common/TextFieldEditDialog.vue";
import VEditDialog from "./components/common/VEditDialog.vue";
import entitySelect from "./components/common/EntitySelect.vue";
import transitionCollapse from "./components/common/transitions/Collapse.vue";
import BNumberField from "./components/common/BNumberField.vue";
import BYearField from "./components/common/BYearField.vue";
import {nformat, substituteNewlineWithBr} from "./filters";

Vue.component('app-identifier', identifier);
Vue.component('app-tile', tile);
Vue.component('app-chip', chip);
Vue.component('app-text-field-edit-dialog', textFieldEditDialog);
Vue.component('app-edit-dialog', VEditDialog);
Vue.component('app-entity-select', entitySelect);
Vue.component('app-transition-collapse', transitionCollapse);
Vue.component('b-number-field', BNumberField);
Vue.component('b-year-field', BYearField);
Vue.filter('substituteNewlineWithBr', substituteNewlineWithBr);
Vue.filter('nformat', nformat);

new Vue({
    el: '#app',
    store,
    router,
    components: {
        'app-notifications': notifications,
        'app-snackbar': snackbar,
        'app-toolbar': toolBar,
        'app-footer': footer,
        'app-menu': menu,
        'app-user-loader': userLoader,
        'app-messages-loader': messagesLoader,
        'app-messages': messages,
        'app-global-select-loader': globalSelectLoader,
        'app-workplace-loader': workplaceLoader
    }
});