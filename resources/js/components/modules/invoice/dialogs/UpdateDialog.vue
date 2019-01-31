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
                        <v-flex :lg4="advancePayment" :lg6="!advancePayment" :md6="!advancePayment" xs12>
                            <v-text-field label="Rechnungsnummer"
                                          prepend-icon="mdi-numeric"
                                          type="text"
                                          v-model="invoiceValue.invoiceNumber"
                                          :error-messages="errorMessages.for('input.invoiceNumber')"
                            ></v-text-field>
                        </v-flex>
                        <v-flex :lg4="advancePayment" :lg6="!advancePayment" md6 xs12>
                            <v-select :error-messages="errorMessages.for('input.invoiceType')"
                                      label="Rechnungstyp"
                                      prepend-icon="mdi-shape"
                                      type="text"
                                      :items="invoiceTypes"
                                      v-model="invoiceValue.invoiceType"
                            ></v-select>
                        </v-flex>
                        <v-flex lg4 md6 style="display: inherit" v-if="advancePayment" xs12>
                            <b-entity-select :advance-payment="advancePaymentFilter"
                                             :entities="['Invoice']"
                                             clearable
                                             label="Abschlagsrechnung ist Teil von"
                                             prepend-icon="mdi-cards-outline"
                                             :error-messages="errorMessages.for('input.firstAdvancePaymentInvoiceId')"
                                             v-model="invoiceValue.firstAdvancePayment"
                            ></b-entity-select>
                        </v-flex>
                        <v-flex md6 xs12>
                            <v-text-field label="Warenausgangsnummer"
                                          prepend-icon="mdi-call-made"
                                          step="1"
                                          type="number"
                                          v-model="invoiceValue.issuerInvoiceNumber"
                                          :error-messages="errorMessages.for('input.issuerInvoiceNumber')"
                            ></v-text-field>
                        </v-flex>
                        <v-flex md6 xs12>
                            <v-text-field label="Wareneingangsnummer"
                                          prepend-icon="mdi-call-received"
                                          step="1"
                                          type="number"
                                          v-model="invoiceValue.recipientInvoiceNumber"
                                          :error-messages="errorMessages.for('input.recipientInvoiceNumber')"
                            ></v-text-field>
                        </v-flex>
                        <v-flex md3 xs12>
                            <v-text-field label="Rechnungsdatum"
                                          prepend-icon="mdi-calendar-blank"
                                          type="date"
                                          v-model="invoiceValue.invoiceDate"
                                          :error-messages="errorMessages.for('input.invoiceDate')"
                            ></v-text-field>
                        </v-flex>
                        <v-flex md3 xs12>
                            <v-text-field label="Eingangsdatum"
                                          prepend-icon="mdi-calendar"
                                          type="date"
                                          v-model="invoiceValue.dateOfReceipt"
                                          :error-messages="errorMessages.for('input.dateOfReceipt')"
                            ></v-text-field>
                        </v-flex>
                        <v-flex md3 xs12>
                            <v-text-field label="Fällig am"
                                          prepend-icon="mdi-calendar-clock"
                                          type="date"
                                          v-model="invoiceValue.dueDate"
                                          :error-messages="errorMessages.for('input.dueDate')"
                            ></v-text-field>
                        </v-flex>
                        <v-flex md3 xs12>
                            <v-select :error-messages="errorMessages.for('input.costForwarded')"
                                      label="WEK"
                                      prepend-icon="mdi-package-variant-closed"
                                      :items="items"
                                      v-model="invoiceValue.costForwarded"
                            ></v-select>
                        </v-flex>
                        <v-flex md6 xs12>
                            <v-text-field clearable
                                          label="Leistungsanfang"
                                          prepend-icon="mdi-calendar-blank"
                                          type="date"
                                          v-model="invoiceValue.serviceTimeStart"
                                          :error-messages="errorMessages.for('input.serviceTimeStart')"
                            ></v-text-field>
                        </v-flex>
                        <v-flex md6 xs12>
                            <v-text-field clearable
                                          label="Leistungsende"
                                          prepend-icon="mdi-calendar-blank"
                                          type="date"
                                          v-model="invoiceValue.serviceTimeEnd"
                                          :error-messages="errorMessages.for('input.serviceTimeEnd')"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12>
                            <v-textarea label="Beschreibung"
                                        prepend-icon="mdi-note"
                                        type="text"
                                        v-model="invoiceValue.description"
                                        :error-messages="errorMessages.for('input.description')"
                            ></v-textarea>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn @click="$emit('input', false)" flat>Abbrechen</v-btn>
                <v-btn :loading="saving"
                       @click="onSave"
                       color="error"
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
    import {Prop, Watch} from "vue-property-decorator";
    import {Invoice} from "../../../../models";
    import _ from 'lodash';
    import BEntitySelect from '../../../common/EntitySelect.vue';
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../../mixins";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";
    import InvoiceTypesQuery from "./InvoiceTypesQuery.graphql";
    import UpdateMutation from "./UpdateMutation.graphql"
    import {FetchResult} from "apollo-link";

    @Component({
        components: {
            'b-entity-select': BEntitySelect
        },
        mixins: [
            DisplaysMessages,
            DisplaysErrors
        ],
        apollo: {
            invoiceTypes: {
                query: InvoiceTypesQuery,
                update(data) {
                    if(data.__type && data.__type.enumValues) {
                        return data.__type.enumValues.map(v => {
                            return {
                                value: v.name,
                                text: v.description
                            }
                        })
                    }
                    return [];
                }
            }
        }
    })
    export default class EditDialog extends Vue implements DisplaysMessagesContract, DisplaysErrorsContract {
        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        invoice: Invoice;

        invoiceValue: Invoice = new Invoice();

        showMessage: <R = any>(message: string) => Promise<FetchResult<R>>;

        errorMessages: ErrorMessages;

        clearErrorMessages: () => void;

        extractErrorMessages: (error: any) => void;

        @Watch('invoice', {immediate: true})
        onInvoiceChange(val) {
            if (val) {
                this.invoiceValue = _.cloneDeep(val);
            }
        }

        @Watch('value')
        onValueChange(val) {
            if (val) {
                this.$apollo.queries.invoiceTypes.refetch();
            }
        }

        saving: boolean = false;

        get items() {
            return [
                {
                    text: 'auto',
                    value: 'AUTO'
                },
                {
                    text: 'ja',
                    value: 'COMPLETE'
                },
                {
                    text: 'nein',
                    value: 'NONE'
                },
                {
                    text: 'teilweise',
                    value: 'PARTIAL'
                }
            ]
        }

        get advancePayment() {
            return this.invoiceValue.invoiceType == 'ADVANCE_PAYMENT_INVOICE' || this.invoiceValue.invoiceType == 'FINAL_ADVANCE_PAYMENT_INVOICE'
        }

        get advancePaymentFilter() {
            if (this.invoice.issuer.id && this.invoice.recipient.id && this.invoice.invoiceDate) {
                return {
                    issuer: this.invoice.issuer.id,
                    recipient: this.invoice.recipient.id,
                    before: this.invoice.invoiceDate
                }
            }
            return undefined
        }

        onSave() {
            this.saving = true;
            this.clearErrorMessages();
            this.$apollo.mutate({
                mutation: UpdateMutation,
                variables: {
                    input: {
                        id: this.invoiceValue.id,
                        invoiceNumber: this.invoiceValue.invoiceNumber,
                        issuerInvoiceNumber: this.invoiceValue.issuerInvoiceNumber,
                        recipientInvoiceNumber: this.invoiceValue.recipientInvoiceNumber,
                        invoiceType: this.invoiceValue.invoiceType,
                        invoiceDate: this.invoiceValue.invoiceDate,
                        dateOfReceipt: this.invoiceValue.dateOfReceipt,
                        dueDate: this.invoiceValue.dueDate,
                        description: this.invoiceValue.description,
                        serviceTimeStart: this.invoiceValue.serviceTimeStart,
                        serviceTimeEnd: this.invoiceValue.serviceTimeEnd,
                        costForwarded: this.invoiceValue.costForwarded,
                        firstAdvancePaymentInvoiceId: this.firstAdvancePaymentInvoiceId
                    }
                }
            }).then(() => {
                this.saving = false;
                this.showMessage('Rechnung geändert.');
                this.$emit('input', false);
            }).catch(error => {
                this.saving = false;
                this.extractErrorMessages(error);
                this.showMessage('Fehler beim Ändern der Rechnung. Message: ' + error.message);
            });
        }

        get firstAdvancePaymentInvoiceId() {
            if(
                this.invoiceValue.firstAdvancePayment
                && ['FINAL_ADVANCE_PAYMENT_INVOICE', 'ADVANCE_PAYMENT_INVOICE'].includes(this.invoiceValue.invoiceType)
            ) {
                return this.invoiceValue.firstAdvancePayment.id;
            }
            return null;
        }
    }
</script>
