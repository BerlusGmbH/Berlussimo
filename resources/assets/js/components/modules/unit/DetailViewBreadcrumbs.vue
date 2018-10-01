<template>
    <span>
        <i class="mdi mdi-subdirectory-arrow-right" style="margin-right: -2px"></i>
        <v-breadcrumbs style="display: inline-flex" class="pl-0">
            <v-icon slot="divider">chevron_right</v-icon>
            <v-breadcrumbs-item>
                <router-link @click.native.stop :to="{name: 'web.units.index'}">Einheiten</router-link>
            </v-breadcrumbs-item>
            <v-breadcrumbs-item v-if="unit">
                <b-identifier :value="unit"></b-identifier>
            </v-breadcrumbs-item>
        </v-breadcrumbs>
    </span>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {namespace} from "vuex-class";
    import {Einheit} from "../../../server/resources";

    const ShowModule = namespace('modules/unit/show');

    @Component
    export default class DeatilViewBreadcrumbs extends Vue {
        @ShowModule.State('unit')
        unit: Einheit;
    }
</script>