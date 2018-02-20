<template>
    <span>
        <i class="mdi mdi-subdirectory-arrow-right" style="margin-right: -2px"></i>
        <v-breadcrumbs style="display: inline-flex" class="pl-0">
            <v-icon slot="divider">chevron_right</v-icon>
            <v-breadcrumbs-item>
                <a href="/rechnungen?option=erfasste_rechnungen">Rechnungen</a>
            </v-breadcrumbs-item>
            <v-breadcrumbs-item v-if="invoice">
                <app-identifier :value="invoice"></app-identifier>
            </v-breadcrumbs-item>
        </v-breadcrumbs>
    </span>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {namespace, State} from "vuex-class";
    import {Invoice} from "../../../server/resources/models";

    const ShowState = namespace('modules/invoice/show', State);

    @Component
    export default class DeatilViewBreadcrumbs extends Vue {
        @ShowState('invoice')
        invoice: Invoice;
    }
</script>