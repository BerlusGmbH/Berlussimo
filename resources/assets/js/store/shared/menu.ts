export default {
    namespaced: true,
    state() {
        return {
            openedMenu: null,
        }
    },
    mutations: {
        updateOpenedMenu(state, menuState) {
            state.openedMenu = menuState;
        }
    }
}