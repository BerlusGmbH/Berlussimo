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
import messagesLoader from "./components/shared/MessagesLoader.vue";
import messages from "./components/shared/Messages.vue";
import snackbar from "./components/shared/Snackbar.vue";
import assignmentListView from "./components/modules/assignment/ListView.vue";
import personDetailView from "./components/modules/person/DetailView.vue";
import personListView from "./components/modules/person/ListView.vue";
import unitDetailView from "./components/modules/unit/DetailView.vue";
import unitListView from "./components/modules/unit/ListView.vue";
import houseDetailView from "./components/modules/house/DetailView.vue";
import houseListView from "./components/modules/house/ListView.vue";
import objectDetailView from "./components/modules/object/DetailView.vue";
import objectListView from "./components/modules/object/ListView.vue";
import identifier from "./components/common/identifiers/Identifier.vue";
import tile from "./components/common/tiles/Tile.vue";
import chip from "./components/common/chips/Chip.vue";
import textFieldEditDialog from "./components/common/TextFieldEditDialog.vue";
import VEditDialog from "./components/common/VEditDialog.vue";
import {substituteNewlineWithBr} from "./filters";

Vue.use(Vuex);
Vue.use(Vuetify);
Vue.component('app-identifier', identifier);
Vue.component('app-tile', tile);
Vue.component('app-chip', chip);
Vue.component('app-text-field-edit-dialog', textFieldEditDialog);
Vue.component('app-edit-dialog', VEditDialog);
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
        'app-messages-loader': messagesLoader,
        'app-messages': messages,
        'app-global-select-loader': globalSelectLoader,
        'app-workplace-loader': workplaceLoader,
        'app-assignment-list-view': assignmentListView,
        'app-person-detail-view': personDetailView,
        'app-person-list-view': personListView,
        'app-unit-detail-view': unitDetailView,
        'app-unit-list-view': unitListView,
        'app-house-detail-view': houseDetailView,
        'app-house-list-view': houseListView,
        'app-object-detail-view': objectDetailView,
        'app-object-list-view': objectListView
    }
});