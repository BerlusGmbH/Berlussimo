<template>
    <v-toolbar>
        <template v-if="user">
            <router-link :to="{name: 'web.dashboard.show'}" class="app-toolbar-logo-link" v-if="$router"
            >
                <img class="app-toolbar-logo" src="/images/berlus_logo.svg">
                <v-toolbar-title class="headline" style="margin-left: 0">
                    berlussimo
                </v-toolbar-title>
            </router-link>
            <a class="app-toolbar-logo-link" href="/" v-else>
                <img class="app-toolbar-logo"
                     src="/images/berlus_logo.svg">
                <v-toolbar-title class="headline" style="margin-left: 0">
                    berlussimo
                </v-toolbar-title>
            </a>
        </template>
        <template v-else>
            <img class="app-toolbar-logo" src="/images/berlus_logo.svg" style="margin-left: 0">
            <v-toolbar-title class="headline" style="margin-left: 0">
                berlussimo
            </v-toolbar-title>
        </template>
        <v-spacer></v-spacer>
        <app-search-dialog v-if="user && $vuetify.breakpoint.smAndDown" v-model="search"></app-search-dialog>
        <app-user-menu-dialog :userId="user.id" v-if="user && $vuetify.breakpoint.smAndDown"
                              v-model="userMenu"></app-user-menu-dialog>
        <v-btn @click.stop="search = true" icon v-if="user && $vuetify.breakpoint.smAndDown">
            <v-icon>search</v-icon>
            <v-icon>mdi-tag-multiple</v-icon>
        </v-btn>
        <app-notifications-toggle v-if="user"></app-notifications-toggle>
        <v-menu offset-y
                open-on-hover
                v-if="user && $vuetify.breakpoint.mdAndUp"
        >
            <v-toolbar-title slot="activator">
                <app-identifier v-model="user"></app-identifier>
                <v-icon>arrow_drop_down</v-icon>
            </v-toolbar-title>
            <app-user-menu-list></app-user-menu-list>
        </v-menu>
        <v-toolbar-side-icon @click.stop="userMenu = true"
                             v-if="user && $vuetify.breakpoint.smAndDown"></v-toolbar-side-icon>
    </v-toolbar>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';
    import searchbar from "./Searchbar.vue";
    import notificationsToggle from "./NotificationsToggle.vue";
    import searchDialog from "../common/dialogs/SearchDialog.vue";
    import userMenuDialog from "../common/dialogs/UserMenuDialog.vue";
    import userMenuList from "./UserMenuList.vue";
    import UserQuery from "../auth/UserQuery.graphql";
    import {Model} from "../../models";

    @Component({
        components: {
            'app-searchbar': searchbar,
            'app-notifications-toggle': notificationsToggle,
            'app-search-dialog': searchDialog,
            'app-user-menu-dialog': userMenuDialog,
            'app-user-menu-list': userMenuList
        },
        apollo: {
            user: {
                query: UserQuery,
                update(data) {
                    if (data.state && data.state.user) {
                        return Model.applyPrototype(data.state.user);
                    }
                    return null;
                }
            }
        }
    })
    export default class Toolbar extends Vue {
        user = null;

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

    .v-toolbar__content {
        padding-left: 0 !important;
        padding-right: 1px !important;
    }
</style>