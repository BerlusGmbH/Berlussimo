<template>
    <v-expansion-panel v-if="authCheck" v-model="openedMenu" class="v-extension-panel__menu">
        <v-expansion-panel-content>
            <div slot="header">
                <v-layout row align-center class="ma-0">
                    <v-flex xs12 md4 lg6>
                        <slot name="breadcrumbs"><i class="mdi mdi-home" style="padding-right: 14px"></i>Bereiche</slot>
                    </v-flex>
                    <v-flex xs12 md8 lg6 v-if="$vuetify.breakpoint.mdAndUp">
                        <app-searchbar style="margin-top: 5px; margin-bottom: 5px"
                        ></app-searchbar>
                    </v-flex>
                </v-layout>
            </div>
            <v-card>
                <v-card-text>
                    <slot name="mainmenu"></slot>
                </v-card-text>
            </v-card>
        </v-expansion-panel-content>
        <v-expansion-panel-content class="primary">
            <template slot="header">
                <v-layout row align-center class="ma-0">
                    <v-flex xs3 md4 lg6>
                        <i class="mdi mdi-settings"></i><span style="padding-left: 14px">Tools</span>
                    </v-flex>
                    <v-flex xs9 md8 lg6>
                        <app-global-select v-if="$vuetify.breakpoint.mdAndUp"
                                           style="margin-top: 5px; margin-bottom: 5px"
                        >
                        </app-global-select>
                    </v-flex>
                </v-layout>
            </template>
            <v-card v-if="hasSubmenu" class="primary">
                <v-card-text>
                    <slot name="submenu"></slot>
                </v-card-text>
            </v-card>
        </v-expansion-panel-content>
    </v-expansion-panel>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';
    import {namespace} from 'vuex-class';
    import globalSelect from "./GlobalSelect.vue";
    import searchbar from "./Searchbar.vue";

    const AuthModule = namespace('auth');

    const MenuModule = namespace('shared/menu');

    @Component({
        components: {
            'app-global-select': globalSelect,
            'app-searchbar': searchbar
        }
    })
    export default class Menu extends Vue {
        @AuthModule.Getter('check') authCheck;

        @MenuModule.State('openedMenu') openedMenuState;

        @MenuModule.Mutation('updateOpenedMenu') updateOpenedMenu;

        get hasSubmenu() {
            return this.$slots.submenu && this.$slots.submenu.length > 0;
        }

        get openedMenu(): boolean {
            return this.openedMenuState;
        }

        set openedMenu(value) {
            this.updateOpenedMenu(value);
        }
    }
</script>

<style>
    .v-extension-panel__menu a {
        color: inherit;
    }

    .v-extension-panel__menu .v-expansion-panel__header {
        padding-top: 0;
        padding-bottom: 0;
    }

    .v-extension-panel__menu .v-expansion-panel__header .chip i {
    }
</style>