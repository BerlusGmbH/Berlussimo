import dialogStore from "./merge/dialog";
import selectorStore from "../../../store/common/selector";
import showStore from "./show";

export default {
    namespaced: true,
    modules: {
        show: showStore,
        merge: {
            namespaced: true,
            modules: {
                dialog: dialogStore,
                selector: selectorStore
            }
        }
    }
}