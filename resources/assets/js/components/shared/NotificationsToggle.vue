<template>
    <v-btn icon @click="toggle">
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
    import {namespace} from "vuex-class";

    const Notifications = namespace('shared/notifications');

    @Component
    export default class NotificationsToggle extends Vue {
        @Notifications.Mutation('toggleOpen')
        toggle;

        @Notifications.State('unread')
        unread: number;
    }
</script>