import axios from "../../libraries/axios";
import {ObjectCopied, PersonMerged} from "../../server/resources/notifications";

export default {
    namespaced: true,
    state() {
        return {
            open: false,
            unread: 0,
            notifications: []
        }
    },
    mutations: {
        updateOpen(state, open) {
            state.open = open;
        },
        toggleOpen(state) {
            state.open = !state.open;
        },
        updateNotifications(state, notifications) {
            state.notifications = notifications;
            state.unread = notifications.reduce((cur, val) => cur + (val.read_at ? 0 : 1), 0);
        }
    },
    actions: {
        getNotifications({dispatch}, user) {
            axios.get('/api/v1/persons/' + user + '/notifications').then((reply) => {
                dispatch('typeNotifications', reply.data);
            });
        },
        typeNotifications({commit}, notifications) {
            notifications.forEach(v => {
                switch (v.type) {
                    case 'App\\Notifications\\PersonMerged':
                        PersonMerged.applyPrototype(v);
                        break;
                    case 'App\\Notifications\\ObjectCopied':
                        ObjectCopied.applyPrototype(v);
                        break;
                }
            });
            commit('updateNotifications', notifications);
        }
    }
}