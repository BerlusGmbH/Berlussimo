<template>
    <v-toolbar>
        <img class="app-toolbar-logo" src="/images/berlus_logo.svg">
        <v-toolbar-title class="headline" style="margin-left: 0">
            berlussimo
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <app-search-dialog v-if="authCheck && $vuetify.breakpoint.smAndDown" v-model="search"></app-search-dialog>
        <app-user-menu-dialog v-if="authCheck && $vuetify.breakpoint.smAndDown" :userId="user.id"
                              v-model="userMenu"></app-user-menu-dialog>
        <v-btn icon v-if="authCheck && $vuetify.breakpoint.smAndDown" @click.stop="search = true">
            <v-icon>search</v-icon>
            <v-icon>mdi-tag-multiple</v-icon>
        </v-btn>
        <app-notifications-toggle v-if="authCheck"></app-notifications-toggle>
        <v-menu v-if="authCheck && $vuetify.breakpoint.mdAndUp"
                offset-y
                open-on-hover
        >
            <v-toolbar-title slot="activator">
                <app-identifier v-model="user"></app-identifier>
                <v-icon>arrow_drop_down</v-icon>
            </v-toolbar-title>
            <app-user-menu-list></app-user-menu-list>
        </v-menu>
        <v-toolbar-side-icon v-if="authCheck && $vuetify.breakpoint.smAndDown"
                             @click.stop="userMenu = true"></v-toolbar-side-icon>
    </v-toolbar>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';
    import {Getter, namespace} from 'vuex-class';
    import searchbar from "./Searchbar.vue";
    import notificationsToggle from "./NotificationsToggle.vue";
    import searchDialog from "../common/dialogs/SearchDialog.vue";
    import userMenuDialog from "../common/dialogs/UserMenuDialog.vue";
    import userMenuList from "./UserMenuList.vue";

    const AuthGetter = namespace('auth', Getter);

    @Component({
        components: {
            'app-searchbar': searchbar,
            'app-notifications-toggle': notificationsToggle,
            'app-search-dialog': searchDialog,
            'app-user-menu-dialog': userMenuDialog,
            'app-user-menu-list': userMenuList
        }
    })
    export default class Toolbar extends Vue {
        @AuthGetter('check')
        authCheck;
        @AuthGetter('user')
        user;

        search: boolean = false;
        userMenu: boolean = false;
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