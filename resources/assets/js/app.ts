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
import BIcon from "./components/common/BIcon.vue";
import {dformat, nformat, sformat, substituteNewlineWithBr} from "./filters";

Vue.component('b-identifier', identifier);
Vue.component('b-tile', tile);
Vue.component('b-chip', chip);
Vue.component('b-text-field-edit-dialog', textFieldEditDialog);
Vue.component('b-edit-dialog', VEditDialog);
Vue.component('b-entity-select', entitySelect);
Vue.component('b-transition-collapse', transitionCollapse);
Vue.component('b-number-field', BNumberField);
Vue.component('b-year-field', BYearField);
Vue.component('b-icon', BIcon);
Vue.filter('substituteNewlineWithBr', substituteNewlineWithBr);
Vue.filter('nformat', nformat);
Vue.filter('dformat', dformat);
Vue.filter('sformat', sformat);

new Vue({
    el: '#app',
    store,
    router,
    components: {
        'b-notifications': notifications,
        'b-snackbar': snackbar,
        'b-toolbar': toolBar,
        'b-footer': footer,
        'b-menu': menu,
        'b-user-loader': userLoader,
        'b-messages-loader': messagesLoader,
        'b-messages': messages,
        'b-global-select-loader': globalSelectLoader,
        'b-workplace-loader': workplaceLoader
    }
});