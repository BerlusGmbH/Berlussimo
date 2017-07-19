import authStore from "./store/auth";
import menuStore from "./store/shared/menu";
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
                menu: menuStore
            }
        },
        auth: authStore
    }
});

export default store;