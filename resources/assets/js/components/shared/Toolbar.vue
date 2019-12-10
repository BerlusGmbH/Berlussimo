<template>
    <v-toolbar>
        <template v-if="authCheck">
            <router-link class="app-toolbar-logo-link" v-if="$router" :to="{name: 'web.dashboard.show'}"
            >
                <img class="app-toolbar-logo" src="/images/berlus_logo.svg">
                <v-toolbar-title class="headline" style="margin-left: 0">
                    berlussimo
                </v-toolbar-title>
            </router-link>
            <a class="app-toolbar-logo-link" v-else href="/"><img class="app-toolbar-logo"
                                                                  src="/images/berlus_logo.svg">
                <v-toolbar-title class="headline" style="margin-left: 0">
                    berlussimo
                </v-toolbar-title>
            </a>
        </template>
        <template v-else>
            <img class="app-toolbar-logo" style="margin-left: 0" src="/images/berlus_logo.svg">
            <v-toolbar-title class="headline" style="margin-left: 0">
                berlussimo
            </v-toolbar-title>
        </template>
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
    .app-toolbar-logo-link {
        height: 100%;
        margin-left: 0 !important;
        display: flex;
        align-items: center;
        color: white;
        text-decoration: none;
    }
    .app-toolbar-logo {
        height: calc(100% - 10px);
        padding-left: .5em;
        padding-right: .5em;
    }
</style>