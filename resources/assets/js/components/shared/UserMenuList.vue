<template>
    <v-list>
        <v-list-tile @click="onLogout">
            <v-list-tile-avatar>
                <v-icon>mdi-logout</v-icon>
            </v-list-tile-avatar>
            <v-list-tile-title>Abmelden</v-list-tile-title>
        </v-list-tile>
    </v-list>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Action, namespace} from "vuex-class";

    const AuthAction = namespace('auth', Action);

    @Component
    export default class UserMenuList extends Vue {

        @AuthAction('logout')
        logout: Function;

        onLogout() {
            this.logout();
        }
    }
</script>