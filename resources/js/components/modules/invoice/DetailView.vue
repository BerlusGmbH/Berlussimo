<template>
    <v-container grid-list-md fluid :key="key">
        <v-layout v-if="invoice" row wrap>
            <v-flex :md9="invoice.isAdvancePayment()" xs12>
                <b-invoice-card :value="invoice"></b-invoice-card>
            </v-flex>
            <v-flex xs12 md3>
                <b-advance-payment-invoices-card :invoices="invoice.advancePayments"
                                                 v-if="invoice.isAdvancePayment()"
                                                 headline="Abschläge"
                ></b-advance-payment-invoices-card>
            </v-flex>
            <v-flex xs12>
                <b-invoice-lines-card :invoice="invoice" headline="Artikel"></b-invoice-lines-card>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import InvoiceCard from "./cards/InvoiceCard.vue";
    import AdvancePaymentInvoicesCard from "./cards/AdvancePaymentInvoicesCard.vue";
    import InvoiceLinesCard from "./cards/InvoiceLinesCard.vue";
    import {Invoice} from "../../../models";
    import DetailViewQuery from "./DetailView.graphql";

    @Component({
        components: {
            'b-invoice-card': InvoiceCard,
            'b-advance-payment-invoices-card': AdvancePaymentInvoicesCard,
            'b-invoice-lines-card': InvoiceLinesCard
        },
        apollo: {
            invoice: {
                query: DetailViewQuery,
                variables(this: DetailView) {
                    return {
                        id: this.id
                    }
                }
            }
        }
    })
    export default class DetailView extends Vue {
        @Prop()
        id: string;

        invoice: Invoice;

        onSave() {
            this.$apollo.queries.invoice.refetch();
        }

        get key() {
            if (this.invoice) {
                return btoa('invoice-' + this.invoice.id);
            }
            return Math.random();
        }
    }
</script>
