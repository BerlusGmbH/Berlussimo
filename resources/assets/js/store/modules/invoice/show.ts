import axios from "../../../libraries/axios";
import {Invoice} from "../../../server/resources/models";

export default {
    namespaced: true,
    state() {
        return {
            id: null,
            invoice: null
        }
    },
    getters: {
        getInvoice(state) {
            return state.invoice;
        }
    },
    mutations: {
        updateId(state, id) {
            state.id = id;
        },

        updateInvoice(state, invoice) {
            if (invoice) {
                Invoice.applyPrototype(invoice);
            }
            state.invoice = invoice;
        }
    },
    actions: {
        updateInvoice({commit, dispatch}, id) {
            commit('updateId', id);
            dispatch('getInvoice', id).then((response) => {
                commit('updateInvoice', response.data);
            });
        },
        getInvoice(_context, id) {
            return axios.get('/api/v1/invoices/' + id);
        },
    }
}