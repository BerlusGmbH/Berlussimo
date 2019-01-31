import Vue from "./imports";
import router from "./router";

import identifier from "./components/common/identifiers/Identifier.vue";
import App from "./components/App.vue";
import tile from "./components/common/tiles/Tile.vue";
import chip from "./components/common/chips/Chip.vue";
import textFieldEditDialog from "./components/common/TextFieldEditDialog.vue";
import BEditDialog from "./components/common/BEditDialog.vue";
import entitySelect from "./components/common/EntitySelect.vue";
import transitionCollapse from "./components/common/transitions/Collapse.vue";
import BNumberField from "./components/common/BNumberField.vue";
import BYearField from "./components/common/BYearField.vue";
import BIcon from "./components/common/BIcon.vue";
import {dformat, nformat, sformat, substituteNewlineWithBr} from "./filters";
import BInput from "./components/common/BInput.vue";
import apolloProvider from "./berlussimo-apollo-client";

Vue.component('app-identifier', identifier);
Vue.component('app-tile', tile);
Vue.component('app-chip', chip);
Vue.component('app-text-field-edit-dialog', textFieldEditDialog);
Vue.component('app-edit-dialog', BEditDialog);
Vue.component('app-entity-select', entitySelect);
Vue.component('app-transition-collapse', transitionCollapse);
Vue.component('b-number-field', BNumberField);
Vue.component('b-year-field', BYearField);
Vue.component('b-icon', BIcon);
Vue.component('b-input', BInput);
Vue.filter('substituteNewlineWithBr', substituteNewlineWithBr);
Vue.filter('nformat', nformat);
Vue.filter('dformat', dformat);
Vue.filter('sformat', sformat);

new Vue({
    el: '#app',
    router,
    apolloProvider,
    components: {
        'b-app': App
    }
});
