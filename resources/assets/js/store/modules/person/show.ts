import axios from "../../../libraries/axios";
import {Person} from "../../../server/resources";

export default {
    namespaced: true,
    state() {
        return {
            id: null,
            person: null
        }
    },
    getters: {
        getPerson(state) {
            return state.person;
        }
    },
    mutations: {
        updateId(state, id) {
            state.id = id;
        },

        updatePerson(state, person) {
            if (person) {
                Person.applyPrototype(person);
            }
            state.person = person;
        }
    },
    actions: {
        updatePerson({commit, dispatch}, id) {
            commit('updateId', id);
            commit('updatePerson', null);
            dispatch('getPerson', id).then((response) => {
                commit('updatePerson', response.data);
            });
        },
        getPerson(_context, id) {
            return axios.get('/api/v1/persons/' + id);
        }
    }
}