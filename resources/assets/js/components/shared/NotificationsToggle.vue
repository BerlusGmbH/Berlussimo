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

    const NotificationsModule = namespace('shared/notifications');

    @Component
    export default class NotificationsToggle extends Vue {
        @NotificationsModule.Mutation('toggleOpen')
        toggle;

        @NotificationsModule.State('unread')
        unread: number;
    }
</script>