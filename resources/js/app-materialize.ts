import Vue from "./imports";
import toolBar from "./components/shared/Toolbar.vue";
import footer from "./components/shared/Footer.vue";
import menu from "./components/shared/Menu.vue";
import notifications from "./components/shared/Notifications.vue";
import messagesLoader from "./components/shared/MessagesLoader.vue";
import messages from "./components/shared/Messages.vue";
import snackbar from "./components/shared/Snackbar.vue";
import identifier from "./components/common/identifiers/Identifier.vue";
import tile from "./components/common/tiles/Tile.vue";
import chip from "./components/common/chips/Chip.vue";
import {dformat, nformat, sformat, substituteNewlineWithBr} from "./filters";
import BEditDialog from "./components/common/BEditDialog.vue";
import BIcon from "./components/common/BIcon.vue";
import BInput from "./components/common/BInput.vue";
import apolloProvider from "./berlussimo-apollo-client";
import StateLoader from "./components/shared/StateLoader.vue";
import gql from "graphql-tag";


Vue.component('app-identifier', identifier);
Vue.component('app-tile', tile);
Vue.component('app-chip', chip);
Vue.component('app-edit-dialog', BEditDialog);
Vue.component('b-icon', BIcon);
Vue.component('b-input', BInput);
Vue.filter('substituteNewlineWithBr', substituteNewlineWithBr);
Vue.filter('nformat', nformat);
Vue.filter('dformat', dformat);
Vue.filter('sformat', sformat);

apolloProvider.defaultClient.cache.writeFragment({
    id: 'State',
    fragment: gql`
        fragment UpdateIsLegacy on State {
            isLegacy
        }
    `,
    data: {
        __typename: 'State',
        isLegacy: true
    }
});

new Vue({
    el: '#notification',
    apolloProvider,
    components: {
        'app-notifications': notifications,
        'app-snackbar': snackbar
    }
});

new Vue({
    el: '#bottom',
    apolloProvider,
    components: {
        'app-footer': footer
    }
});

new Vue({
    el: '#top',
    apolloProvider,
    components: {
        'b-state-loader': StateLoader,
        'app-toolbar': toolBar,
        'app-menu': menu,
        'app-messages-loader': messagesLoader,
        'app-messages': messages,
    },
    mounted() {
        let element = document.getElementById("berlussimo-content");
        if (element) {
            element.style.display = 'inherit';
        }
    }
});
