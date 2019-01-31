<template>
    <v-btn @click="toggle" icon>
        <template v-if="unread">
            <v-badge overlap>
                <v-icon>message</v-icon>
                <span slot="badge">{{unread}}</span>
            </v-badge>
        </template>
        <template v-else>
            <v-icon>message</v-icon>
        </template>
    </v-btn>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import NotificationsTabStateQuery from "./NotificationsTabStateQuery.graphql";
    import NotificationsQuery from './NotificationsQuery.graphql';
    import ToggleNotificationsTabOpenMutation from './ToggleNotificationsTabOpenMutation.graphql';

    @Component({
        apollo: {
            open: {
                query: NotificationsTabStateQuery,
                update(this: NotificationsToggle, data) {
                    return data.state.notificationsTabOpen;
                }
            },
            notifications: {
                query: NotificationsQuery
            }
        }
    })
    export default class NotificationsToggle extends Vue {

        open: boolean = false;
        notifications: any[] = [];

        toggle() {
            this.$apollo.mutate({
                mutation: ToggleNotificationsTabOpenMutation
            });
        }

        get unread() {
            return this.notifications.reduce((carry, current) => {
                return !current.readAt ? carry + 1 : carry;
            }, 0)
        }
    }
</script>