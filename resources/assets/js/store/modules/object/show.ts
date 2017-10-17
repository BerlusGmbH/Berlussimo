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
            state.object = object;
        }
    },
    actions: {
        updateObject({commit, dispatch}, id) {
            commit('updateId', id);
            dispatch('getObject', id).then((response) => {
                dispatch('prototypeObject', response.data).then((object) => {
                    commit('updateObject', object);
                });
            });
        },
        getObject(_context, id) {
            return axios.get('/api/v1/objects/' + id);
        },
        prototypeObject(_context, object): Objekt {
            return Objekt.applyPrototype(object);
        }
    }
}