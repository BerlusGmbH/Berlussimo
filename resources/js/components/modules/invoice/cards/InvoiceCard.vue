<template>
    <v-card>
        <v-card-title>
            <app-identifier class="headline"
                            :value="value"
            ></app-identifier>
        </v-card-title>
        <v-card-text>
            <v-layout row wrap>
                <v-flex xs12 md6>
                    <v-layout row wrap>
                        <v-flex v-if="value.issuer" xs9>
                            Von:
                            <app-identifier :value="value.issuer" style="width: calc(100% - 40px)"
                            ></app-identifier>
                        </v-flex>
                        <v-flex xs3>
                            <v-tooltip bottom>
                                <v-icon slot='activator' style="font-size: inherit">mdi-call-made</v-icon>
                                <span>Warenausgangsnummer</span>
                            </v-tooltip>
                            {{value.issuerInvoiceNumber}}
                        </v-flex>
                        <v-flex v-if="value.recipient" xs9>
                            An:
                            <app-identifier :value="value.recipient" style="width: calc(100% - 40px)"
                            ></app-identifier>
                        </v-flex>
                        <v-flex xs3>
                            <v-tooltip bottom>
                                <v-icon slot='activator' style="font-size: inherit">mdi-call-received</v-icon>
                                <span>Wareneingangsnummer</span>
                            </v-tooltip>
                            {{value.recipientInvoiceNumber}}
                        </v-flex>
                        <v-flex v-if="value.bankAccount" xs9>
                            <app-identifier :value="value.bankAccount"></app-identifier>
                        </v-flex>
                        <v-flex :offset-xs9="!value.bankAccount" xs3>
                            <v-tooltip bottom>
                                <v-icon slot='activator' style="font-size: inherit">mdi-package-variant-closed</v-icon>
                                <span>Wareneingang Kunde</span>
                            </v-tooltip>
                            {{value.costForwarded}}
                        </v-flex>
                        <v-flex xs12>
                            <b-icon :tooltips="['Kurzbeschreibung']">mdi-note</b-icon>
                            {{value.description}}
                        </v-flex>
                    </v-layout>
                </v-flex>
                <v-flex xs12 md6>
                    <v-layout row wrap>
                        <v-flex xs6>
                            <v-layout column>
                                <v-flex>
                                    <v-tooltip bottom>
                                        <v-icon slot='activator' style="font-size: inherit">mdi-calendar-blank</v-icon>
                                        <span>Rechnungsdatum</span>
                                    </v-tooltip>
                                    {{value.invoiceDate|dformat}}
                                </v-flex>
                                <v-flex>
                                    <v-tooltip bottom>
                                        <v-icon slot='activator' style="font-size: inherit">mdi-calendar</v-icon>
                                        <span>Eingangsdatum</span>
                                    </v-tooltip>
                                    {{value.dateOfReceipt|dformat}}
                                </v-flex>
                                <v-flex>
                                    <v-tooltip bottom>
                                        <v-icon slot='activator' style="font-size: inherit">mdi-calendar-clock</v-icon>
                                        <span>Fälligkeitsdatum</span>
                                    </v-tooltip>
                                    {{value.dueDate|dformat}}
                                </v-flex>
                                <v-flex v-if="paydateIsValid">
                                    <v-tooltip bottom>
                                        <v-icon slot='activator' style="font-size: inherit">mdi-calendar-check</v-icon>
                                        <span>Geldeingangsdatum</span>
                                    </v-tooltip>
                                    {{value.payDate|dformat}}
                                </v-flex>
                                <v-flex v-if="value.serviceTimeStart">
                                    <v-tooltip bottom>
                                        <v-icon slot='activator' style="font-size: inherit">mdi-calendar-range</v-icon>
                                        <span v-if="value.serviceTimeEnd">Leistungsdatum</span>
                                        <span v-else>Leistungszeitraum</span>
                                    </v-tooltip>
                                    {{value.serviceTimeStart|dformat}}
                                    <template v-if="value.serviceTimeEnd">
                                        - {{value.serviceTimeEnd|dformat}}
                                    </template>
                                </v-flex>
                            </v-layout>
                        </v-flex>
                        <v-flex xs2 sm2>
                            <v-layout column class="text-xs-right">
                                <v-flex>
                                    Netto:
                                </v-flex>
                                <v-flex>
                                    Brutto:
                                </v-flex>
                                <v-flex>
                                    Skontiert:
                                </v-flex>
                            </v-layout>
                        </v-flex>
                        <v-flex xs4 sm4>
                            <v-layout column class="text-xs-right">
                                <v-flex>
                                    {{value.netAmount | nformat}} €
                                </v-flex>
                                <v-flex>
                                    {{value.grossAmount | nformat}} €
                                </v-flex>
                                <v-flex>
                                    {{value.discountAmount | nformat}} €
                                </v-flex>
                            </v-layout>
                        </v-flex>
                    </v-layout>
                </v-flex>
            </v-layout>
        </v-card-text>
    </v-card>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import moment from 'moment';
    import {Invoice} from "../../../../models";

    @Component
    export default class InvoiceCard extends Vue {
        @Prop()
        value: Invoice;

        get paydateIsValid() {
            return moment(this.value.payDate).isValid();
        }
    }
</script>
