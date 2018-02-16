export default {
    namespaced: true,
    state() {
        return {
            mainmenuOpen: false,
            submenuOpen: false
        }
    },
    mutations: {
        updateMainmenuOpen(state, menuState) {
            state.mainmenuOpen = menuState;
        },
        updateSubmenuOpen(state, menuState) {
            state.submenuOpen = menuState;
        }
    }
}