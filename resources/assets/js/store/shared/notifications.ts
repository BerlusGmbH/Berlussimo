import axios from "../../libraries/axios";
import PersonMerged from "../../server/resources/notifications";

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
            state.unread = 0;
            state.open = !state.open;
        },
        updateNotifications(state, notifocations) {
            state.notifications = notifocations;
        },
        appendNotification(state, notification) {
            state.notifications.unshift(PersonMerged.typeOne(notification));
            state.unread++;
        }
    },
    actions: {
        getNotifications({dispatch}, user) {
            axios.get('/api/v1/personen/' + user + '/notifications').then((reply) => {
                dispatch('typeNotifications', reply.data);
            });
        },
        typeNotifications({commit}, notifications) {
            commit('updateNotifications', PersonMerged.type(notifications));
        }
    }
}