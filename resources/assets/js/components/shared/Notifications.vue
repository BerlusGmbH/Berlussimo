<template>
    <v-card class="notifications-fixed" dark="dark" v-if="open">
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
            <template slot="headers" slot-scope="props">
                <th class="text-xs-right" v-for="header in props.headers"
                    :key="header.text"
                >
                    <template v-if="header.text === 'Gelesen'">
                        <v-btn icon @click.native="markAllAsRead">
                            <v-icon>mdi-eye</v-icon>
                        </v-btn>
                    </template>
                    <template v-else>
                        {{ header.text }}
                    </template>
                </th>
            </template>
            <template slot="items" slot-scope="props">
                <td class="text-xs-right">
                    <template v-if="props.item.read_at">
                        <v-btn icon @click.native="toggleRead(props.item)">
                            <v-icon>mdi-eye</v-icon>
                        </v-btn>
                    </template>
                    <template v-else>
                        <v-btn icon @click.native="toggleRead(props.item)">
                            <v-icon>mdi-eye-off</v-icon>
                        </v-btn>
                    </template>
                </td>
                <td class="text-xs-right">{{ props.item.created_at }}</td>
                <template v-if="props.item.type === 'App\\Notifications\\PersonMerged'">
                    <td class="text-xs-right">Personen (
                        <app-identifier v-model="props.item.data.left"></app-identifier>
                        und
                        <app-identifier v-model="props.item.data.right"></app-identifier>
                        ) zusammengeführt:
                        <app-identifier v-model="props.item.data.merged"></app-identifier>
                    </td>
                </template>
                <template v-else-if="props.item.type === 'App\\Notifications\\ObjectCopied'">
                    <td class="text-xs-right">Objekt (
                        <app-identifier v-model="props.item.data.source"></app-identifier>
                        ) kopiert:
                        <app-identifier v-model="props.item.data.target"></app-identifier>
                    </td>
                </template>
            </template>
            <template slot="pageText" slot-scope="{ pageStart, pageStop }">
                From {{ pageStart }} to {{ pageStop }}
            </template>
        </v-data-table>
    </v-card>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import {namespace} from "vuex-class";
    import Echo from "../../libraries/Echo";
    import axios from "../../libraries/axios";

    const NotificationsState = namespace('shared/notifications');
    const Auth = namespace('auth');
    const PersonShow = namespace('modules/person/show');
    const Refresh = namespace('shared/refresh');

    @Component
    export default class Notifications extends Vue {
        @NotificationsState.State('open')
        open: boolean;

        @NotificationsState.State('notifications')
        notifications: Array<Object>;

        @NotificationsState.Action('getNotifications')
        getNotifications: Function;

        @NotificationsState.Mutation('appendNotification')
        appendNotification: Function;

        @Auth.State('user')
        user;

        @PersonShow.Action('updatePerson')
        updatePerson: Function;

        @Refresh.Mutation('requestRefresh')
        requestRefresh: Function;

        mounted() {
            this.onUserChange();
        }

        @Watch('user')
        onUserChange() {
            if (this.user) {
                this.getNotifications(this.user.id);
                let vm = this;
                Echo.private('Notification.Person.' + this.user.id)
                    .notification(function (event) {
                        if (event.type === 'App\\Notifications\\NotificationsUpdated') {
                            vm.getNotifications(vm.user.id);
                            vm.requestRefresh();
                        }
                    });
            }
        }

        @Prop({type: String})
        id: string;

        @Prop({type: Boolean})
        dark: boolean;

        search: string = '';
        headers: Array<Object> = [
            {text: 'Gelesen', sortable: false, value: 'read_at'},
            {text: 'Datum', sortable: false, value: 'created_at'},
            {text: 'Nachricht', sortable: false, value: 'notification'}
        ];

        toggleRead(notification) {
            axios.get('/api/v1/notifications/' + notification.id + '/toggle').then(() => {
                this.getNotifications(this.user.id);
            });
        }

        markAllAsRead() {
            axios.get('/api/v1/persons/' + this.user.id + '/notifications/mark_all_as_read').then(() => {
                this.getNotifications(this.user.id);
            });
        }
    }
</script>

<style>
    .notifications {
        margin: 0;
        width: 100%;
    }

    .notifications-fixed {
        position: sticky !important;
        bottom: 0;
    }

    .notifications-inline {
        position: relative;
    }
</style>