<template>
    <v-toolbar>
        <img class="app-toolbar-logo" src="/images/berlus_logo.svg">
        <v-toolbar-title class="headline" style="margin-left: 0">
            berlussimo
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-side-icon class="hidden-md-and-up"></v-toolbar-side-icon>
        <app-searchbar v-if="authCheck"></app-searchbar>
        <app-notifications-toggle v-if="authCheck"></app-notifications-toggle>
        <v-menu v-if="authCheck" offset-y open-on-hover>
            <v-toolbar-title slot="activator">
                <app-identifier :entity="user"></app-identifier>
                <v-icon>arrow_drop_down</v-icon>
            </v-toolbar-title>
            <v-list>
                <v-list-tile @click="onLogout">
                    <v-list-tile-title>Abmelden</v-list-tile-title>
                </v-list-tile>
            </v-list>
        </v-menu>
    </v-toolbar>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';
    import {Getter, namespace} from 'vuex-class';
    import searchbar from "./Searchbar.vue";
    import notificationsToggle from "./NotificationsToggle.vue";

    const AuthGetter = namespace('auth', Getter);

    @Component({
        components: {
            'app-searchbar': searchbar,
            'app-notifications-toggle': notificationsToggle
        }
    })
    export default class Toolbar extends Vue {
        @AuthGetter('check')
        authCheck;
        @AuthGetter('user')
        user;

        onLogout() {
            window.location.assign('/logout');
        }
    }
</script>

<style>
    .app-toolbar-logo {
        height: 100%;
        padding-top: 5px;
        padding-bottom: 5px;
        margin-left: 0 !important;
    }
</style>