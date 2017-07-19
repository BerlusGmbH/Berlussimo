import {Person} from "../server/resources";

export default {
    namespaced: true,
    state() {
        return {
            user: null
        }
    },
    mutations: {
        updateUser(state, user) {
            state.user = user;
        }
    },
    getters: {
        check(state) {
            return state.user !== null && state.user !== undefined;
        },
        user(state, getters) {
            if (getters.check) {
                return Object.assign(new Person(), state.user);
            }
            return state.user;
        }
    }
}