import {Model} from "../server/resources";
import router from "../router";
import axios from "../libraries/axios";
import Echo from "../libraries/Echo";

export default {
    namespaced: true,
    state() {
        return {
            user: null,
            csrf: null
        }
    },
    mutations: {
        updateUser(state, user) {
            Echo.disconnect();
            Echo.connect();
            if (user) {
                Model.applyPrototype(user);
            }
            state.user = user;
        },
        updateCsrf(state, csrf) {
            state.csrf = csrf;
        }
    },
    getters: {
        check(state) {
            return state.user !== null && state.user !== undefined;
        },
        user(state, getters) {
            if (getters.check) {
                return Model.applyPrototype(state.user);
            }
            return state.user;
        }
    },
    actions: {
        logout(context) {
            axios.get('/logout').then(() => {
                context.dispatch('appLogout');
            });
        },
        appLogout(context) {
            if (!context.rootState.shared.legacy.isLegacy) {
                router.push({name: 'web.login'});
                context.commit('updateUser', null);
            } else {
                window.location.assign('/login')
            }
        }
    }
}