<template>
    <v-btn icon @click="toggle">
        <template v-if="unread">
            <v-icon v-badge="{ value: unread, overlap: true}">message</v-icon>
        </template>
        <template v-else>
            <v-icon>message</v-icon>
        </template>
    </v-btn>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Mutation, namespace, State} from "vuex-class";

    const NotificationsMutation = namespace('shared/notifications', Mutation);
    const NotificationsState = namespace('shared/notifications', State);

    @Component
    export default class NotificationsToggle extends Vue {
        @NotificationsMutation('toggleOpen')
        toggle;

        @NotificationsState('unread')
        unread: number;
    }
</script>