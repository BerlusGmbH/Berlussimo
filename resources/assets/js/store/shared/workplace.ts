export default {
    namespaced: true,
    state() {
        return {
            hasPhone: false
        }
    },
    mutations: {
        updateHasPhone(state, hasPhone) {
            state.hasPhone = hasPhone;
        }
    }
}