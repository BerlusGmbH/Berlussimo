import dialogStore from "./merge/dialog";
import showStore from "./show";

export default {
    namespaced: true,
    modules: {
        show: showStore,
        merge: {
            namespaced: true,
            modules: {
                dialog: dialogStore
            }
        }
    }
}