import axios from "../../libraries/axios";
import {AxiosPromise} from "axios";
import {Objekt} from "../../server/resources";

export default {
    namespaced: true,
    state() {
        return {
            partner: null,
            bankkonto: null,
            objekt: null
        }
    },
    mutations: {
        updatePartner(state, partner) {
            state.partner = partner;
        },
        updateBankkonto(state, bankkonto) {
            state.bankkonto = bankkonto;
        },
        updateObjekt(state, objekt) {
            state.objekt = objekt;
        }
    },
    actions: {
        updatePartner({commit}, partner) {
            let promise: AxiosPromise;
            if (partner) {
                promise = axios.get('/api/v1/partners/' + partner.PARTNER_ID + '/select');
            } else {
                promise = axios.get('/api/v1/partners/unselect');
            }
            promise.then((reply) => {
                if (reply.status === 200) {
                    commit('updatePartner', partner);
                }
            });
        },
        updateObjekt({commit}, objekt) {
            let promise: AxiosPromise;
            if (objekt) {
                promise = axios.get('/api/v1/objects/' + objekt.OBJEKT_ID + '/select');
            } else {
                promise = axios.get('/api/v1/objects/unselect');
            }
            promise.then((reply) => {
                if (reply.status === 200) {
                    commit('updateObjekt', objekt);
                }
            });
        },
        updateBankkonto({commit}, bankkonto) {
            let promise: AxiosPromise;
            if (bankkonto) {
                promise = axios.get('/api/v1/bankaccounts/' + bankkonto.KONTO_ID + '/select');
            } else {
                promise = axios.get('/api/v1/bankaccounts/unselect');
            }
            promise.then((reply) => {
                if (reply.status === 200) {
                    commit('updateBankkonto', bankkonto);
                    if (reply.data.object) {
                        commit('updateObjekt', Objekt.applyPrototype(reply.data.object));
                    }
                }
            });
        }
    }
}