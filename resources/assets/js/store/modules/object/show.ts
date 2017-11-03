import axios from "libraries/axios";
import {Objekt} from "server/resources/models";

export default {
    namespaced: true,
    state() {
        return {
            id: null,
            object: null
        }
    },
    getters: {
        getUnit(state) {
            return state.object;
        }
    },
    mutations: {
        updateId(state, id) {
            state.id = id;
        },

        updateObject(state, object) {
            if (object) {
                Objekt.applyPrototype(object);
            }
            state.object = object;
        }
    },
    actions: {
        updateObject({commit, dispatch}, id) {
            commit('updateId', id);
            commit('updateObject', null);
            dispatch('getObject', id).then((response) => {
                commit('updateObject', response.data);
            });
        },
        getObject(_context, id) {
            return axios.get('/api/v1/objects/' + id);
        },
    }
}