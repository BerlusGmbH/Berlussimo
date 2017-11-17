import axios from "../../../libraries/axios";
import {Haus} from "../../../server/resources/models";

export default {
    namespaced: true,
    state() {
        return {
            id: null,
            house: null
        }
    },
    getters: {
        getUnit(state) {
            return state.house;
        }
    },
    mutations: {
        updateId(state, id) {
            state.id = id;
        },

        updateHouse(state, house) {
            if (house) {
                Haus.applyPrototype(house);
            }
            state.house = house;
        }
    },
    actions: {
        updateHouse({commit, dispatch}, id) {
            commit('updateId', id);
            commit('updateHouse', null);
            dispatch('getHouse', id).then(response => {
                commit('updateHouse', response.data);
            });
        },
        getHouse(_context, id) {
            return axios.get('/api/v1/houses/' + id);
        }
    }
}