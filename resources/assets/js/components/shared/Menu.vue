<template>
    <v-expansion-panel v-if="authCheck" class="extension-panel__menu">
        <v-expansion-panel-content v-model="mainmenuOpen">
            <div slot="header">
                <slot name="breadcrumbs"><i class="mdi mdi-home"></i>Bereiche</slot>
            </div>
            <v-card>
                <v-card-text>
                    <slot name="mainmenu"></slot>
                </v-card-text>
            </v-card>
        </v-expansion-panel-content>
        <v-expansion-panel-content class="primary" v-model="submenuOpen">
            <template slot="header">
                <v-layout row align-center style="width: 100%">
                    <v-flex xs3 md4 lg6 class="pa-0">
                        <i class="mdi mdi-settings"></i>Tools
                    </v-flex>
                    <v-flex xs9 md8 lg6 class="pa-0">
                        <app-global-select
                                style="padding-right: 3em; padding-top: 8px; padding-bottom: 8px">
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
    import {State, Getter, Mutation, namespace} from 'vuex-class';
    import globalSelect from "./GlobalSelect.vue";

    const AuthGetter = namespace('auth', Getter);

    const MenuState = namespace('shared/menu', State);
    const MenuMutation = namespace('shared/menu', Mutation);

    @Component({
        components: {
            'app-global-select': globalSelect
        }
    })
    export default class Menu extends Vue {
        @AuthGetter('check') authCheck;

        @MenuState('mainmenuOpen') mainmenuOpenState;
        @MenuState('submenuOpen') submenuOpenState;

        @MenuMutation('updateMainmenuOpen') updateMainmenuOpen;
        @MenuMutation('updateSubmenuOpen') updateSubmenuOpen;

        get hasSubmenu() {
            return this.$slots.submenu.length > 0;
        }

        get mainmenuOpen(): boolean {
            return this.mainmenuOpenState;
        }

        set mainmenuOpen(value) {
            this.updateMainmenuOpen(value);
        }

        get submenuOpen(): boolean {
            return this.submenuOpenState;
        }

        set submenuOpen(value) {
            this.updateSubmenuOpen(value);
        }
    }
</script>

<style>
    .extension-panel__menu a {
        color: inherit;
    }

    .extension-panel__menu .expansion-panel__header {
        min-height: 48px;
        height: initial;
    }

    .extension-panel__menu .expansion-panel__header .chip i {
        margin-right: 0;
    }
</style>