import showStore from "./show";

export default {
    namespaced: true,
    modules: {
        show: showStore,
        merge: {
            namespaced: true
        }
    }
}