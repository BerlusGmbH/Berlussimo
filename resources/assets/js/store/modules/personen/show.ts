import axios, {AxiosPromise} from "axios";
import {
    Detail,
    Einheit,
    Haus,
    Job,
    JobTitle,
    Objekt,
    Partner,
    Person,
    PurchaseContract,
    RentalContract
} from "../../../server/resources";

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
            Object.setPrototypeOf(person, Person.prototype);
            Array.prototype.forEach.call(['common_details', 'hinweise', 'adressen', 'emails', 'faxs', 'phones'], (details) => {
                Array.prototype.forEach.call(person[details], (detail) => {
                    Object.setPrototypeOf(detail, Detail.prototype);
                });
            });
            Array.prototype.forEach.call(person.audits, (audit) => {
                if (audit.user) {
                    Object.setPrototypeOf(audit.user, Person.prototype);
                }
            });
            Array.prototype.forEach.call(person.mietvertraege, (mietvertrag) => {
                Object.setPrototypeOf(mietvertrag, RentalContract.prototype);
                if (mietvertrag.einheit) {
                    Object.setPrototypeOf(mietvertrag.einheit, Einheit.prototype);
                    if (mietvertrag.einheit.haus) {
                        Object.setPrototypeOf(mietvertrag.einheit.haus, Haus.prototype);
                        if (mietvertrag.einheit.haus.objekt) {
                            Object.setPrototypeOf(mietvertrag.einheit.haus.objekt, Objekt.prototype);
                        }
                    }
                }
            });
            Array.prototype.forEach.call(person.kaufvertraege, (kaufvertrag) => {
                Object.setPrototypeOf(kaufvertrag, PurchaseContract.prototype);
                if (kaufvertrag.einheit) {
                    Object.setPrototypeOf(kaufvertrag.einheit, Einheit.prototype);
                    if (kaufvertrag.einheit.haus) {
                        Object.setPrototypeOf(kaufvertrag.einheit.haus, Haus.prototype);
                        if (kaufvertrag.einheit.haus.objekt) {
                            Object.setPrototypeOf(kaufvertrag.einheit.haus.objekt, Objekt.prototype);
                        }
                    }
                }
            });
            Array.prototype.forEach.call(person.jobs_as_employee, (job) => {
                Object.setPrototypeOf(job, Job.prototype);
                if (job.employer) {
                    Object.setPrototypeOf(job.employer, Partner.prototype);
                }
                if (job.title) {
                    Object.setPrototypeOf(job.title, JobTitle.prototype);
                }
            });
            return person;
        }
    }
}