import authStore from "./store/auth";
import menuStore from "./store/shared/menu";
import notificationsStore from "./store/shared/notifications";
import globalSelectStore from "./store/shared/global_select";
import workplaceStore from "./store/shared/workplace";
import legacyStore from "./store/shared/legacy";
import messagesStore from "./store/shared/messages";
import snackbarStore from "./store/shared/snackbar";
import personenStore from "./store/modules/personen/store";

import Vue from "vue";
import Vuex from "vuex";

Vue.use(Vuex);
const store = new Vuex.Store({
    modules: {
        modules: {
            namespaced: true,
            modules: {
                personen: personenStore
            }
        },
        shared: {
            namespaced: true,
            modules: {
                menu: menuStore,
                notifications: notificationsStore,
                snackbar: snackbarStore,
                globalSelect: globalSelectStore,
                legacy: legacyStore,
                workplace: workplaceStore,
                messages: messagesStore
            }
        },
        auth: authStore
    }
});

export default store;