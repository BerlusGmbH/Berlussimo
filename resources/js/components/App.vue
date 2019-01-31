<template>
    <v-app dark>
        <b-state-loader></b-state-loader>
        <div style="position: sticky; top: 0; z-index: 1">
            <b-toolbar></b-toolbar>
            <b-menu v-if="user">
                <template slot="breadcrumbs">
                    <router-view name="breadcrumbs"></router-view>
                </template>
                <template slot="mainmenu">
                    <router-view name="mainmenu"></router-view>
                </template>
                <template slot="submenu">
                    <router-view name="submenu"></router-view>
                </template>
            </b-menu>
            <b-loading-bar></b-loading-bar>
        </div>
        <v-content style="z-index: 0">
            <transition mode="out-in" name="fade">
                <router-view>
                    <router-view name="defaultSlot"></router-view>
                </router-view>
            </transition>
        </v-content>

        <b-notifications id="notifications" v-if="user"></b-notifications>
        <b-snackbar id="snackbar"></b-snackbar>
        <b-footer></b-footer>
    </v-app>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';
    import UserQuery from './auth/UserQuery.graphql';
    import {Model, Person} from "../models";
    import ToolBar from "./shared/Toolbar.vue";
    import Footer from "./shared/Footer.vue";
    import Menu from "./shared/Menu.vue";
    import Notifications from "./shared/Notifications.vue";
    import StateLoader from "./shared/StateLoader.vue";
    import LoadingBar from "./shared/LoadingBar.vue";
    import Snackbar from "./shared/Snackbar.vue";

    @Component({
        components: {
            'b-notifications': Notifications,
            'b-snackbar': Snackbar,
            'b-toolbar': ToolBar,
            'b-footer': Footer,
            'b-menu': Menu,
            'b-state-loader': StateLoader,
            'b-loading-bar': LoadingBar,
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
    export default class App extends Vue {
        user: Person | null = null;
    }
</script>

<style>
    .extension-panel__menu a {
        color: inherit;
    }

    .extension-panel__menu .v-expansion-panel__header {
        min-height: 48px;
        height: initial;
        padding: 0 0 0 24px;
    }

    .extension-panel__menu .expansion-panel__header .chip i {
        margin-right: 0;
    }
</style>