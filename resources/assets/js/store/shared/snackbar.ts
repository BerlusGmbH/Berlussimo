export default {
    namespaced: true,
    state() {
        return {
            show: false,
            message: ''
        }
    },
    mutations: {
        updateShow(state, show) {
            state.show = show;
        },
        toggleShow(state) {
            state.show = !state.show;
        },
        updateMessage(state, message) {
            state.show = true;
            state.message = message;
        }
    }
}