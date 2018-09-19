<template>
    <v-dialog :value="value" @input="$emit('input', $event)">
        <v-card>
            <v-card-title>
                <v-icon>mdi-pencil</v-icon>
                <span class="headline">Rechnung bearbeiten</span>
            </v-card-title>
            <v-card-text>
                <v-container fluid grid-list-md>
                    <v-layout row wrap>
                        <v-flex xs12 :md6="!advancePayment" :lg6="!advancePayment" :lg4="advancePayment">
                            <v-text-field label="Rechnungsnummer"
                                          prepend-icon="mdi-numeric"
                                          v-model="invoiceValue.RECHNUNGSNUMMER"
                                          type="text"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 md6 :lg6="!advancePayment" :lg4="advancePayment">
                            <v-select label="Rechnungstyp"
                                      prepend-icon="mdi-shape"
                                      :items="types"
                                      v-model="invoiceValue.RECHNUNGSTYP"
                                      type="text"
                            ></v-select>
                        </v-flex>
                        <v-flex xs12 md6 lg4 v-if="advancePayment" style="display: inherit">
                            <b-entity-select label="Abschlagsrechnung ist Teil von"
                                             prepend-icon="mdi-cards-outline"
                                             @input="selectAdvancePaymentInvoice"
                                             v-model="invoiceValue.advance_payment_invoice"
                                             :entities="entities"
                                             clearable
                            ></b-entity-select>
                        </v-flex>
                        <v-flex xs12 md6>
                            <v-text-field label="Warenausgangsnummer"
                                          prepend-icon="mdi-call-made"
                                          v-model="invoiceValue.AUSTELLER_AUSGANGS_RNR"
                                          type="number"
                                          step="1"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 md6>
                            <v-text-field label="Wareneingangsnummer"
                                          prepend-icon="mdi-call-received"
                                          v-model="invoiceValue.EMPFAENGER_EINGANGS_RNR"
                                          type="number"
                                          step="1"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 md4>
                            <v-text-field label="Rechnungsdatum"
                                          prepend-icon="mdi-calendar-blank"
                                          v-model="invoiceValue.RECHNUNGSDATUM"
                                          type="date"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 md4>
                            <v-text-field label="Eingangsdatum"
                                          prepend-icon="mdi-calendar"
                                          v-model="invoiceValue.EINGANGSDATUM"
                                          type="date"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 md4>
                            <v-text-field label="Fällig am"
                                          prepend-icon="mdi-calendar-clock"
                                          v-model="invoiceValue.FAELLIG_AM"
                                          type="date"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 md6>
                            <v-text-field label="Leistungsanfang"
                                          prepend-icon="mdi-calendar-blank"
                                          v-model="invoiceValue.servicetime_from"
                                          type="date"
                                          clearable
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 md6>
                            <v-text-field label="Leistungsende"
                                          prepend-icon="mdi-calendar-blank"
                                          v-model="invoiceValue.servicetime_to"
                                          type="date"
                                          clearable
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12>
                            <v-text-field label="Beschreibung"
                                          prepend-icon="mdi-note"
                                          v-model="invoiceValue.KURZBESCHREIBUNG"
                                          multi-line
                                          type="text"
                            ></v-text-field>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn flat @click="$emit('input', false)">Abbrechen</v-btn>
                <v-btn color="error"
                       :loading="saving"
                       @click="onSave"
                >
                    <v-icon>mdi-pencil</v-icon>
                    Ändern
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {namespace} from "vuex-class";
    import {Prop, Watch} from "vue-property-decorator";
    import {Invoice} from "../../../../server/resources";
    import _ from 'lodash';
    import axios from '../../../../libraries/axios';
    import BEntitySelect from '../../../common/EntitySelect.vue';

    const SnackbarModule = namespace('shared/snackbar');
    const RefreshModule = namespace('shared/refresh');

    @Component({
        components: {
            'b-entity-select': BEntitySelect
        }
    })
    export default class EditDialog extends Vue {
        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        invoice: Invoice;

        invoiceValue: Invoice = new Invoice();

        types: Array<string> = [];

        @Watch('invoice')
        onInvoiceChange(val) {
            if (val) {
                this.invoiceValue = _.cloneDeep(val);
            }
        }

        @Watch('value')
        onValueChange(val) {
            if (val) {
                this.updateTypes();
            }
        }

        mounted() {
            this.onInvoiceChange(this.invoice);
        }

        @SnackbarModule.Mutation('updateMessage')
        updateMessage: Function;

        @RefreshModule.Mutation('requestRefresh')
        requestRefresh: Function;

        saving: boolean = false;

        get advancePayment() {
            return this.invoiceValue.RECHNUNGSTYP == 'Teilrechnung' || this.invoiceValue.RECHNUNGSTYP == 'Schlussrechnung'
        }

        get entities() {
            let entity = 'teilrechnung';
            if (this.invoice.from.PARTNER_ID && this.invoice.to.PARTNER_ID && this.invoice.RECHNUNGSDATUM) {
                entity += ':' + this.invoice.from.PARTNER_ID + ':' + this.invoice.to.PARTNER_ID + ':' + this.invoice.RECHNUNGSDATUM
            }
            return [entity]
        }

        onSave() {
            this.saving = true;
            this.invoiceValue.save().then(() => {
                this.saving = false;
                this.updateMessage('Rechnung geändert.');
                this.requestRefresh();
                this.$emit('input', false);
            }).catch(error => {
                this.saving = false;
                this.updateMessage('Fehler beim Ändern der Rechnung. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
            });
        }

        selectAdvancePaymentInvoice(invoice) {
            this.invoiceValue.advance_payment_invoice_id = invoice ? invoice.BELEG_NR : null;
        }

        updateTypes() {
            axios.get('/api/v1/invoices/types')
                .then(response => {
                    this.types = response.data;
                });
        }
    }
</script>