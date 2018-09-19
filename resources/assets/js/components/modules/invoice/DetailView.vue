<template>
    <v-container grid-list-md fluid :key="key">
        <v-layout v-if="invoice" row wrap>
            <v-flex xs12 :md9="invoice.isAdvancePaymentInvoice()">
                <app-invoice-card :value="invoice"></app-invoice-card>
            </v-flex>
            <v-flex xs12 md3>
                <app-advance-payment-invoices-card v-if="invoice.isAdvancePaymentInvoice()"
                                                   :invoices="invoice.advance_payment_invoices"
                                                   headline="Abschläge"
                ></app-advance-payment-invoices-card>
            </v-flex>
            <v-flex xs12>
                <app-invoice-lines-card headline="Artikel" :invoice="invoice"></app-invoice-lines-card>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {namespace} from "vuex-class";
    import {Prop, Watch} from "vue-property-decorator";
    import invoiceCard from "./cards/InvoiceCard.vue";
    import AdvancePaymentInvoicesCard from "./cards/AdvancePaymentInvoicesCard.vue";
    import invoiceLinesCard from "./cards/InvoiceLinesCard.vue";

    const ShowModule = namespace('modules/invoice/show');
    const RefreshModule = namespace('shared/refresh');

    @Component({
        components: {
            'app-invoice-card': invoiceCard,
            'app-advance-payment-invoices-card': AdvancePaymentInvoicesCard,
            'app-invoice-lines-card': invoiceLinesCard
        }
    })
    export default class DetailView extends Vue {
        @Prop()
        id: string;

        @ShowModule.Action('updateInvoice')
        fetchInvoice;

        @ShowModule.State('invoice')
        invoice;

        @RefreshModule.State('dirty')
        dirty;

        @RefreshModule.Mutation('refreshFinished')
        refreshFinished: Function;

        @Watch('dirty')
        onDirtyChange(val) {
            if (val) {
                this.fetchInvoice(this.id).then(() => {
                    this.refreshFinished();
                }).catch(() => {
                    this.refreshFinished();
                })
            }
        }

        created() {
            if (this.id) {
                this.fetchInvoice(this.id);
            }
        }

        @Watch('$route')
        onRouteChange() {
            if (this.id) {
                this.fetchInvoice(this.id);
            }
        }

        get key() {
            if (this.invoice) {
                return btoa('invoice-' + this.invoice.getID());
            }
            return Math.random();
        }
    }
</script>