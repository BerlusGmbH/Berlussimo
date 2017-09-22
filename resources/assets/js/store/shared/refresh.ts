export default {
    namespaced: true,
    state() {
        return {
            dirty: false
        }
    },
    mutations: {
        requestRefresh(state) {
            state.dirty = true;
        },
        refreshFinished(state) {
            state.dirty = false;
        }
    }
}