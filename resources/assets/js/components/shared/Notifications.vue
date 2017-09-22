<template>
    <v-card v-if="open" class="notifications-fixed">
        <v-card-title>
            <span class="headline"><i class="mdi mdi-message"></i> Benachrichtigungen</span>
            <v-spacer></v-spacer>
            <v-text-field
                    append-icon="search"
                    label="Search"
                    single-line
                    hide-details
                    v-model="search"
            ></v-text-field>
        </v-card-title>
        <v-data-table
                :headers="headers"
                :items="notifications"
                :search="search"
        >
            <template slot="items" scope="props">
                <td class="text-xs-right">{{ props.item.created_at }}</td>
                <td class="text-xs-right">Personen (
                    <app-identifier v-model="props.item.data.left"></app-identifier>
                    und
                    <app-identifier v-model="props.item.data.right"></app-identifier>
                    ) zusammengeführt:
                    <app-identifier v-model="props.item.data.merged"></app-identifier>
                </td>
            </template>
            <template slot="pageText" scope="{ pageStart, pageStop }">
                From {{ pageStart }} to {{ pageStop }}
            </template>
        </v-data-table>
    </v-card>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import {Action, Mutation, namespace, State} from "vuex-class";
    import Echo from "../../libraries/Echo";

    const NotificationsState = namespace('shared/notifications', State);
    const NotificationsAction = namespace('shared/notifications', Action);
    const NotificationsMutation = namespace('shared/notifications', Mutation);
    const AuthState = namespace('auth', State);
    const PersonShowAction = namespace('modules/personen/show', Action);

    @Component
    export default class Notifications extends Vue {
        @NotificationsState('open')
        open: boolean;

        @NotificationsState('notifications')
        notifications: Array<Object>;

        @NotificationsAction('getNotifications')
        getNotifications: Function;

        @NotificationsMutation('appendNotification')
        appendNotification: Function;

        @AuthState('user')
        user;

        @PersonShowAction('updatePerson')
        updatePerson: Function;

        mounted() {
            this.onUserChange();
        }

        @Watch('user')
        onUserChange() {
            if (this.user) {
                this.getNotifications(this.user.id);
                let vm = this;
                Echo.private('Notification.Person.' + this.user.id)
                    .notification(function (notification) {
                        notification.type = 'App\\Notifications\\PersonMerged';
                        notification.created_at = notification.data.created_at;
                        vm.appendNotification(notification);
                        if (window.location.pathname === '/personen/' + notification.data.merged.id) {
                            vm.updatePerson(notification.data.merged.id);
                        }
                    });
            }
        }

        @Prop({type: String})
        id: string;

        search: string = '';
        headers: Array<Object> = [
            {text: 'Datum', sortable: false, value: 'created_at'},
            {text: 'Nachricht', sortable: false, value: 'notification'}
        ];
    }
</script>

<style>
    .notifications {
        margin: 0;
        width: 100%;
    }

    .notifications-fixed {
        position: sticky;
        bottom: 0;
    }

    .notifications-inline {
        position: relative;
    }
</style>