<template>
    <v-card>
        <v-card-title>
            <router-link v-if="filter"
                         :to="{name: 'web.invoices.index', query: {q: filter}}"
            >
                <h3 class="headline">
                    {{headline}}
                    <v-chip color="primary">
                        {{invoices.length}}
                    </v-chip>
                </h3>
            </router-link>
            <h3 class="headline" v-else>
                {{headline}}
                <v-chip color="primary">
                    {{invoices.length}}
                </v-chip>
            </h3>
        </v-card-title>
        <v-card-text>
            <v-data-table
                    :headers="headers"
                    :items="invoices"
                    :search="search"
                    :hide-actions="invoices.length <= 5"
                    class="elevation-1"
                    disable-initial-sort
            >
                <template slot="items" slot-scope="props">
                    <td>
                        <app-identifier v-model="props.item"></app-identifier>
                    </td>
                </template>
                <template v-slot:pageText="props">
                    {{ props.pageStart }} - {{ props.pageStop }} von {{ props.itemsLength }}
                </template>
            </v-data-table>
        </v-card-text>
    </v-card>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Invoice} from "../../../../models";

    @Component
    export default class AdvancePaymentInvoicesCard extends Vue {
        @Prop({type: Array})
        invoices: Invoice[];

        @Prop({type: String})
        headline;

        @Prop({type: String, default: ''})
        filter;

        search: string = '';
        headers = [
            {text: 'Abschlagsrechnung', value: 'id'}
        ];
    }
</script>
