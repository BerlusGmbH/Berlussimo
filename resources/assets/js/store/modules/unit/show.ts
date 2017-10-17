import axios from "libraries/axios";
import {Einheit} from "server/resources/models";

export default {
    namespaced: true,
    state() {
        return {
            id: null,
            unit: null
        }
    },
    getters: {
        getUnit(state) {
            return state.unit;
        }
    },
    mutations: {
        updateId(state, id) {
            state.id = id;
        },

        updateUnit(state, unit) {
            state.unit = unit;
        }
    },
    actions: {
        updateUnit({commit, dispatch}, id) {
            commit('updateId', id);
            dispatch('getUnit', id).then((response) => {
                dispatch('prototypeUnit', response.data).then((unit) => {
                    commit('updateUnit', unit);
                });
            });
        },
        getUnit(_context, id) {
            return axios.get('/api/v1/units/' + id);
        },
        prototypeUnit(_context, unit): Einheit {
            return Einheit.applyPrototype(unit);
        }
    }
}