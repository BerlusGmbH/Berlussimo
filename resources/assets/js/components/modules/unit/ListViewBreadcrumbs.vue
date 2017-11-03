<template>
    <span>
        <i class="mdi mdi-subdirectory-arrow-right" style="margin-right: -2px"></i>
        <v-breadcrumbs style="display: inline-flex" icons divider="chevron_right" class="pl-0">
            <v-breadcrumbs-item>
                <router-link :to="{name: 'web.units.index'}">Einheiten</router-link>
            </v-breadcrumbs-item>
        </v-breadcrumbs>
    </span>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";

    @Component
    export default class ListViewBreadcrumbs extends Vue {

    }
</script>