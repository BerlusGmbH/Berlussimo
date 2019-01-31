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
    import {namespace} from "vuex-class";

    const Auth = namespace('auth');

    @Component
    export default class UserMenuList extends Vue {

        @Auth.Action('logout')
        logout: Function;

        onLogout() {
            this.logout();
        }
    }
</script>