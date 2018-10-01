<template>
    <v-toolbar>
        <template v-if="authCheck">
            <router-link class="b-toolbar-logo-link" v-if="$router" :to="{name: 'web.dashboard.show'}"
            >
                <img class="b-toolbar-logo" src="/images/berlus_logo.svg">
                <v-toolbar-title class="headline" style="margin-left: 0">
                    berlussimo
                </v-toolbar-title>
            </router-link>
            <a class="b-toolbar-logo-link" v-else href="/"><img class="b-toolbar-logo"
                                                                src="/images/berlus_logo.svg">
                <v-toolbar-title class="headline" style="margin-left: 0">
                    berlussimo
                </v-toolbar-title>
            </a>
        </template>
        <template v-else>
            <img class="b-toolbar-logo" style="margin-left: 0" src="/images/berlus_logo.svg">
            <v-toolbar-title class="headline" style="margin-left: 0">
                berlussimo
            </v-toolbar-title>
        </template>
        <v-spacer></v-spacer>
        <b-search-dialog v-if="authCheck && $vuetify.breakpoint.smAndDown" v-model="search"></b-search-dialog>
        <b-user-menu-dialog v-if="authCheck && $vuetify.breakpoint.smAndDown" :userId="user.id"
                            v-model="userMenu"></b-user-menu-dialog>
        <v-btn icon v-if="authCheck && $vuetify.breakpoint.smAndDown" @click.stop="search = true">
            <v-icon>search</v-icon>
            <v-icon>mdi-tag-multiple</v-icon>
        </v-btn>
        <b-notifications-toggle v-if="authCheck"></b-notifications-toggle>
        <v-menu v-if="authCheck && $vuetify.breakpoint.mdAndUp"
                offset-y
                open-on-hover
        >
            <v-toolbar-title slot="activator">
                <b-identifier v-model="user"></b-identifier>
                <v-icon>arrow_drop_down</v-icon>
            </v-toolbar-title>
            <b-user-menu-list></b-user-menu-list>
        </v-menu>
        <v-toolbar-side-icon v-if="authCheck && $vuetify.breakpoint.smAndDown"
                             @click.stop="userMenu = true"></v-toolbar-side-icon>
    </v-toolbar>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';
    import {namespace} from 'vuex-class';
    import searchbar from "./Searchbar.vue";
    import notificationsToggle from "./NotificationsToggle.vue";
    import searchDialog from "../common/dialogs/SearchDialog.vue";
    import userMenuDialog from "../common/dialogs/UserMenuDialog.vue";
    import userMenuList from "./UserMenuList.vue";

    const AuthModule = namespace('auth');

    @Component({
        components: {
            'b-searchbar': searchbar,
            'b-notifications-toggle': notificationsToggle,
            'b-search-dialog': searchDialog,
            'b-user-menu-dialog': userMenuDialog,
            'b-user-menu-list': userMenuList
        }
    })
    export default class Toolbar extends Vue {
        @AuthModule.Getter('check')
        authCheck;
        @AuthModule.Getter('user')
        user;

        search: boolean = false;
        userMenu: boolean = false;
    }
</script>

<style>
    .b-toolbar-logo-link {
        height: 100%;
        display: flex;
        align-items: center;
        color: white;
        text-decoration: none;
    }

    .b-toolbar-logo {
        height: calc(100% - 10px);
        padding-right: .5em;
    }
</style>