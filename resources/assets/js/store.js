import dialogStore from "./store/modules/person/merge/dialog.js";
import selectorStore from "./store/common/selector.js";
import Vue from "vue";
import Vuex from "vuex";

Vue.use(Vuex);
const store = new Vuex.Store({
    modules: {
        modules: {
            namespaced: true,
            modules: {
                person: {
                    namespaced: true,
                    modules: {
                        merge: {
                            namespaced: true,
                            modules: {
                                dialog: dialogStore,
                                selector: selectorStore
                            }
                        }
                    }
                }
            }
        }
    }
});

export default store;