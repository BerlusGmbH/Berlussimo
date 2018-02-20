export default {
    namespaced: true,
    state() {
        return {
            isLegacy: false
        }
    },
    mutations: {
        updateIsLegacy(state, isLegacy) {
            state.isLegacy = isLegacy;
        }
    }
}