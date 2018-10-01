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
                        <b-identifier v-model="props.item.data.left"></b-identifier>
                        und
                        <b-identifier v-model="props.item.data.right"></b-identifier>
                        ) zusammengeführt:
                        <b-identifier v-model="props.item.data.merged"></b-identifier>
                    </td>
                </template>
                <template v-else-if="props.item.type === 'App\\Notifications\\ObjectCopied'">
                    <td class="text-xs-right">Objekt (
                        <b-identifier v-model="props.item.data.source"></b-identifier>
                        ) kopiert:
                        <b-identifier v-model="props.item.data.target"></b-identifier>
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

    const NotificationsModule = namespace('shared/notifications');
    const AuthModule = namespace('auth');
    const PersonShowModule = namespace('modules/person/show');
    const RefreshModule = namespace('shared/refresh');

    @Component
    export default class Notifications extends Vue {
        @NotificationsModule.State('open')
        open: boolean;

        @NotificationsModule.State('notifications')
        notifications: Array<Object>;

        @NotificationsModule.Action('getNotifications')
        getNotifications: Function;

        @NotificationsModule.Mutation('appendNotification')
        appendNotification: Function;

        @AuthModule.State('user')
        user;

        @PersonShowModule.Action('updatePerson')
        updatePerson: Function;

        @RefreshModule.Mutation('requestRefresh')
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
                    .notification(function (notification) {
                        if (notification.data.type === 'App\\Notifications\\NotificationsUpdated') {
                            vm.getNotifications(vm.user.id);
                            vm.requestRefresh();
                        }
                    });
            }
        }

        @Prop({type: String})
        id: string;

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
        position: sticky;
        bottom: 0;
    }

    .notifications-inline {
        position: relative;
    }
</style>