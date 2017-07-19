<template>
    <v-expansion-panel v-if="authCheck">
        <v-expansion-panel-content @mouseenter.native="mainmenuOpen = true" @mouseleave.native="mainmenuOpen = false"
                                   v-model="mainmenuOpen">
            <div slot="header">
                <slot name="breadcrumbs"><i class="mdi mdi-home"></i>Bereiche</slot>
            </div>
            <v-card>
                <v-card-text>
                    <slot name="mainmenu"></slot>
                </v-card-text>
            </v-card>
        </v-expansion-panel-content>
        <v-expansion-panel-content @mouseenter.native="submenuOpen = true" @mouseleave.native="submenuOpen = false"
                                   class="secondary" v-model="submenuOpen">
            <div slot="header"><i class="mdi mdi-settings"></i>Tools</div>
            <v-card class="secondary">
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

    const AuthGetter = namespace('auth', Getter);

    const MenuState = namespace('shared/menu', State);
    const MenuMutation = namespace('shared/menu', Mutation);

    @Component
    export default class Menu extends Vue {
        @AuthGetter('check') authCheck;

        @MenuState('mainmenuOpen') mainmenuOpenState;
        @MenuState('submenuOpen') submenuOpenState;

        @MenuMutation('updateMainmenuOpen') updateMainmenuOpen;
        @MenuMutation('updateSubmenuOpen') updateSubmenuOpen;

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