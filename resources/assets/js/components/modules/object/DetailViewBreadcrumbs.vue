<template>
    <span>
        <i class="mdi mdi-subdirectory-arrow-right" style="margin-right: -2px"></i>
        <v-breadcrumbs style="display: inline-flex" icons divider="chevron_right" class="pl-0">
            <v-breadcrumbs-item>
                <router-link @click.native.stop :to="{name: 'web.objects.index'}">Objekte</router-link>
            </v-breadcrumbs-item>
            <v-breadcrumbs-item v-if="object">
                <app-identifier :value="object"></app-identifier>
            </v-breadcrumbs-item>
        </v-breadcrumbs>
    </span>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {namespace, State} from "vuex-class";
    import {Objekt} from "../../../server/resources/models";

    const ShowState = namespace('modules/object/show', State);

    @Component
    export default class DeatilViewBreadcrumbs extends Vue {
        @ShowState('object')
        object: Objekt;
    }
</script>