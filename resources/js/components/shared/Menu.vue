<template>
    <v-expand-transition>
        <v-expansion-panel class="extension-panel__menu" v-model="panels">
            <v-expansion-panel-content class="pt-0">
                <div slot="header">
                    <v-layout align-center class="ma-0" row>
                        <v-flex lg6 md4 xs12>
                            <slot name="breadcrumbs"><i class="mdi mdi-home" style="padding-right: 14px"></i>Bereiche
                            </slot>
                        </v-flex>
                        <v-flex lg6 md8 v-if="$vuetify.breakpoint.mdAndUp" xs12>
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
                    <v-layout align-center class="ma-0" row>
                        <v-flex lg6 md4 xs3>
                            <i class="mdi mdi-settings"></i><span style="padding-left: 14px">Tools</span>
                        </v-flex>
                        <v-flex lg6 md8 xs9>
                            <app-global-select style="margin-top: 5px; margin-bottom: 5px"
                                               v-if="$vuetify.breakpoint.mdAndUp"
                            >
                            </app-global-select>
                        </v-flex>
                    </v-layout>
                </template>
                <v-card class="primary" v-if="hasSubmenu">
                    <v-card-text>
                        <slot name="submenu"></slot>
                    </v-card-text>
                </v-card>
            </v-expansion-panel-content>
        </v-expansion-panel>
    </v-expand-transition>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';
    import globalSelect from "./GlobalSelect.vue";
    import searchbar from "./Searchbar.vue";

    @Component({
        components: {
            'app-global-select': globalSelect,
            'app-searchbar': searchbar
        }
    })
    export default class Menu extends Vue {
        panels: boolean[] = [false, false];

        get hasSubmenu() {
            return this.$slots.submenu && this.$slots.submenu.length > 0;
        }
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