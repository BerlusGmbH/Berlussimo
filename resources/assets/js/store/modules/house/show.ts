import axios from "libraries/axios";
import {Haus} from "server/resources/models";

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
            state.house = house;
        }
    },
    actions: {
        updateHouse({commit, dispatch}, id) {
            commit('updateId', id);
            dispatch('getHouse', id).then((response) => {
                dispatch('prototypeHouse', response.data).then((house) => {
                    commit('updateHouse', house);
                });
            });
        },
        getHouse(_context, id) {
            return axios.get('/api/v1/houses/' + id);
        },
        prototypeHouse(_context, house): Haus {
            return Haus.applyPrototype(house);
        }
    }
}