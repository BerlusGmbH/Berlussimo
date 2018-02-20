import axios from "../../../libraries/axios";
import {Einheit} from "../../../server/resources/models";

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
            if (unit) {
                Einheit.applyPrototype(unit);
            }
            state.unit = unit;
        }
    },
    actions: {
        updateUnit({commit, dispatch}, id) {
            commit('updateId', id);
            commit('updateUnit', null);
            dispatch('getUnit', id).then((response) => {
                commit('updateUnit', response.data);
            });
        },
        getUnit(_context, id) {
            return axios.get('/api/v1/units/' + id);
        }
    }
}