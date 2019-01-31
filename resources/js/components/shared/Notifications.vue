<template v-if="open">
    <v-expand-transition>
        <v-card class="notifications-fixed elevation-20" dark="dark" v-show="open">
            <v-card-title>
                <span class="headline"><i class="mdi mdi-message"></i> Benachrichtigungen</span>
                <v-spacer></v-spacer>
                <v-text-field
                    append-icon="search"
                    hide-details
                    label="Search"
                    single-line
                    v-model="search"
                ></v-text-field>
            </v-card-title>
            <v-data-table
                :headers="headers"
                :items="notifications"
                :pagination.sync="pagination"
                :search="search"
            >
                <template slot="headers" slot-scope="props">
                    <th :key="header.text" class="text-xs-right"
                        v-for="header in props.headers"
                    >
                        <template v-if="header.text === 'Gelesen'">
                            <v-btn @click.native="markAllAsRead" icon>
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
                        <template v-if="props.item.readAt">
                            <v-btn @click.native="toggleRead(props.item)" icon>
                                <v-icon>mdi-eye</v-icon>
                            </v-btn>
                        </template>
                        <template v-else>
                            <v-btn @click.native="toggleRead(props.item)" icon>
                                <v-icon>mdi-eye-off</v-icon>
                            </v-btn>
                        </template>
                    </td>
                    <td class="text-xs-right">{{ props.item.createdAt }}</td>
                    <template v-if="props.item.__typename === 'PersonMergedNotification'">
                        <td class="text-xs-right">Personen (
                            <app-identifier v-model="props.item.left"></app-identifier>
                            und
                            <app-identifier v-model="props.item.right"></app-identifier>
                            ) zusammengeführt:
                            <app-identifier v-model="props.item.merged"></app-identifier>
                        </td>
                    </template>
                    <template v-else-if="props.item.__typename === 'PropertyCopiedNotification'">
                        <td class="text-xs-right">Objekt (
                            <app-identifier v-model="props.item.source"></app-identifier>
                            ) kopiert:
                            <app-identifier v-model="props.item.target"></app-identifier>
                        </td>
                    </template>
                </template>
                <template v-slot:pageText="props">
                    {{ props.pageStart }} - {{ props.pageStop }} von {{ props.itemsLength }}
                </template>
            </v-data-table>
        </v-card>
    </v-expand-transition>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Model, Person} from "../../models";
    import UserQuery from '../auth/UserQuery.graphql';
    import NotificationsQuery from "./NotificationsQuery.graphql";
    import NotificationsTabStateQuery from './NotificationsTabStateQuery.graphql';
    import NotificationAddedSubscription from './NotificationAddedSubscription.graphql';
    import MarkNotificationAsReadMutation from './MarkNotificationAsReadMutation.graphql';
    import MarkNotificationAsUnreadMutation from './MarkNotificationAsUnreadMutation.graphql';
    import MarkAllNotificationsAsReadMutation from './MarkAllNotificationsAsReadMutation.graphql';
    import EventBus from '../../EventBus';

    @Component({
        apollo: {
            user: {
                query: UserQuery,
                update(data) {
                    if (data.state && data.state.user) {
                        return Model.applyPrototype(data.state.user);
                    }
                    return null;
                }
            },
            open: {
                query: NotificationsTabStateQuery,
                update(data) {
                    if (data.state && data.state.notificationsTabOpen) {
                        return data.state.notificationsTabOpen;
                    }
                    return false;
                }
            },
            notifications: {
                query: NotificationsQuery,
                subscribeToMore: {
                    document: NotificationAddedSubscription,
                    updateQuery(this: Notifications, previousResult, {subscriptionData}) {
                        previousResult.notifications.push(Model.applyPrototype(subscriptionData.data.notificationAdded));
                        if (
                            this.shouldRefreshListView(subscriptionData.data.notificationAdded.__typename)
                        ) {
                            EventBus.$emit('list-view:refetch');
                        }
                        return previousResult;
                    },
                }
            }
        }
    })
    export default class Notifications extends Vue {
        user: Person | null = null;

        open: boolean = false;

        notifications: any[] = [];

        @Prop({type: String})
        id: string;

        @Prop({type: Boolean})
        dark: boolean;

        search: string = '';
        headers: Object[] = [
            {text: 'Gelesen', sortable: false, value: 'readAt'},
            {text: 'Datum', sortable: true, value: 'createdAt'},
            {text: 'Nachricht', sortable: false, value: 'notification'}
        ];

        pagination = {sortBy: 'createdAt', descending: true};

        toggleRead(notification) {
            let mutation;
            if (notification.readAt) {
                mutation = MarkNotificationAsUnreadMutation
            } else {
                mutation = MarkNotificationAsReadMutation
            }
            this.$apollo.mutate({
                mutation,
                variables: {
                    id: notification.id
                }
            })
        }

        markAllAsRead() {
            this.$apollo.mutate({
                mutation: MarkAllNotificationsAsReadMutation
            })
        }

        shouldRefreshListView(type) {
            return (type === "PersonMergedNotification"
                && this.$route.name === 'web.persons.index')
                || (type === "PropertyCopiedNotification"
                    && this.$route.name === 'web.properties.index')
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
