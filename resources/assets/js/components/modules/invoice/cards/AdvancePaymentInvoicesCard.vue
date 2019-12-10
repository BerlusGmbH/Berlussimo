<template>
    <v-card>
        <v-card-title>
            <router-link v-if="filter"
                         :to="{name: 'web.invoices.index', query: {q: filter}}"
            >
                <h3 class="headline">{{headline}} ({{invoices.length}})</h3>
            </router-link>
            <h3 v-else class="headline">{{headline}} ({{invoices.length}})</h3>
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
                <template slot="pageText" slot-scope="{ pageStart, pageStop }">
                    Von {{ pageStart }} bis {{ pageStop }}
                </template>
            </v-data-table>
        </v-card-text>
    </v-card>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Invoice} from "../../../../server/resources";

    @Component
    export default class AdvancePaymentInvoicesCard extends Vue {
        @Prop({type: Array})
        invoices: Array<Invoice>;

        @Prop({type: String})
        headline;

        @Prop({type: String, default: ''})
        filter;

        search: string = '';
        headers = [
            {text: 'Abschlagsrechnung', value: 'BELEG_NR'}
        ];
    }
</script>