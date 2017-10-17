import axios from "libraries/axios";
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
            state.person = person;
        }
    },
    actions: {
        updatePerson({commit, dispatch}, id) {
            commit('updateId', id);
            dispatch('getPerson', id).then((response) => {
                dispatch('prototypePerson', response.data).then((person) => {
                    commit('updatePerson', person);
                });
            });
        },
        getPerson(_context, id) {
            return axios.get('/api/v1/persons/' + id);
        },
        prototypePerson(_context, person: any): Person {
            return Person.applyPrototype(person);
        }
    }
}