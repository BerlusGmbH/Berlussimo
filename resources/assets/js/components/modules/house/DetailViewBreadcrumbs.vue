<template>
    <span>
        <i class="mdi mdi-subdirectory-arrow-right" style="margin-right: -2px"></i>
        <v-breadcrumbs style="display: inline-flex" icons divider="chevron_right" class="pl-0">
            <v-breadcrumbs-item>
                <router-link @click.native.stop :to="{name: 'web.houses.index'}">Häuser</router-link>
            </v-breadcrumbs-item>
            <v-breadcrumbs-item v-if="house">
                <app-identifier :value="house"></app-identifier>
            </v-breadcrumbs-item>
        </v-breadcrumbs>
    </span>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {namespace, State} from "vuex-class";
    import {Haus} from "../../../server/resources/models";

    const ShowState = namespace('modules/house/show', State);

    @Component
    export default class DeatilViewBreadcrumbs extends Vue {
        @ShowState('house')
        house: Haus;
    }
</script>