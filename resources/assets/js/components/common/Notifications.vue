<template>
    <v-card class="notifications-fixed">
        <v-card-title>
            <i class="mdi mdi-message"></i> Benachrichtigungen
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
                v-bind:headers="headers"
                v-bind:items="typedNotifications"
                v-bind:search="search"
        >
            <template slot="items" scope="props">
                <td class="text-xs-right">{{ props.item.created_at }}</td>
                <td class="text-xs-right" v-html="String(props.item)"></td>
            </template>
            <template slot="pageText" scope="{ pageStart, pageStop }">
                From {{ pageStart }} to {{ pageStop }}
            </template>
        </v-data-table>
    </v-card>
</template>

<script>
    export default {
        props: {
            initNotifications: Array,
            id: String,
            user: Number
        },
        data() {
            return {
                notifications: this.initNotifications,
                search: '',
                headers: [
                    {text: 'Datum', value: 'created_at'},
                    {text: 'Nachricht', value: 'notification'}
                ]
            }
        },
        mounted() {
            let vm = this;
            Echo.private('Notification.Person.' + this.user)
                .notification(function (notification) {
                    notification.type = 'App\\Notifications\\PersonMerged';
                    notification.created_at = notification.data.created_at;
                    vm.notifications.unshift(notification);
                });
        },
        computed: {
            typedNotifications() {
                return Notifications.type(this.notifications);
            }
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
        z-index: 1;
    }

    .notifications-inline {
        position: relative;
    }
</style>