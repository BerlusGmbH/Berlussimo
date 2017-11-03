import {Model} from "../server/resources/models";
import {router} from "app";
import axios from "libraries/axios";

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
            router.push({name: 'web.login'});
            context.commit('updateUser', null);
        }
    }
}