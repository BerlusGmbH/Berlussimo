<template>
    <span>
        <i class="mdi mdi-subdirectory-arrow-right" style="margin-right: -2px"></i>
        <v-breadcrumbs style="display: inline-flex" icons divider="chevron_right" class="pl-0">
            <v-breadcrumbs-item>
                <router-link @click.native.stop :to="{name: 'web.units.index'}">Einheiten</router-link>
            </v-breadcrumbs-item>
            <v-breadcrumbs-item v-if="unit">
                <app-identifier :value="unit"></app-identifier>
            </v-breadcrumbs-item>
        </v-breadcrumbs>
    </span>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {namespace, State} from "vuex-class";
    import {Einheit} from "../../../server/resources/models";

    const ShowState = namespace('modules/unit/show', State);

    @Component
    export default class DeatilViewBreadcrumbs extends Vue {
        @ShowState('unit')
        unit: Einheit;
    }
</script>