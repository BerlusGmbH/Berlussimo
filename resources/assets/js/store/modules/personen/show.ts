import axios, {AxiosPromise} from "axios";
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
        getPerson(_context, id): AxiosPromise {
            let token = document.head.querySelector('meta[name="csrf-token"]');

            if (token) {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = token['content'];
            }
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            return axios.get('/api/v1/persons/' + id);
        },
        prototypePerson(_context, person: any): Person {
            return Person.prototypePerson(person);
        }
    }
}