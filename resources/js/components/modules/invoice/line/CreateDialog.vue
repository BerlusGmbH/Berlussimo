<template>
    <v-dialog :value="value" @input="$emit('input', $event)">
        <v-card>
            <v-card-title>
                <v-icon>mdi-cart-plus</v-icon>
                <span class="headline">Position hinzufügen</span>
            </v-card-title>
            <v-card-text>
                <v-container fluid grid-list-md>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <b-entity-select :disabled="saving"
                                             :entities="['InvoiceItem']"
                                             :value="selected"
                                             @input="select"
                                             append-icon=""
                                             label="Artikel"
                                             prepend-icon="mdi-cart-outline"
                                             ref="invoiceItemSelect"
                                             tabindex="1"
                                             :invoice-items-from="invoiceItemsFrom"
                            >
                            </b-entity-select>
                        </v-flex>
                        <v-flex md2 xs12>
                            <v-text-field label="Artikelnummer"
                                          tabindex="2"
                                          type="text"
                                          :disabled="saving"
                                          :error-messages="errorMessages.for('input.itemNumber')"
                                          v-model="lineValue.itemNumber"
                            ></v-text-field>
                        </v-flex>
                        <v-flex md4 xs12>
                            <v-textarea label="Beschreibung"
                                        tabindex="3"
                                        type="text"
                                        :disabled="saving"
                                        :error-messages="errorMessages.for('input.description')"
                                        v-model="lineValue.description"
                            ></v-textarea>
                        </v-flex>
                        <v-flex md1 xs6>
                            <v-text-field label="Menge"
                                          ref="amountInput"
                                          step="0.01"
                                          tabindex="4"
                                          type="number"
                                          :disabled="saving"
                                          :error-messages="errorMessages.for('input.quantity')"
                                          v-model="lineValue.quantity"
                            ></v-text-field>
                        </v-flex>
                        <v-flex md1 xs6>
                            <v-autocomplete :items="units"
                                            :disabled="saving"
                                            :error-messages="errorMessages.for('input.quantityUnit')"
                                            label="Einheit"
                                            tabindex="5"
                                            item-text="description"
                                            item-value="name"
                                            v-model="lineValue.quantityUnit"
                            ></v-autocomplete>
                        </v-flex>
                        <v-flex md2 xs6>
                            <v-layout column>
                                <v-flex>
                                    <b-number-field append-icon="mdi-currency-eur"
                                                    format="0.0000"
                                                    hide-details
                                                    label="Einkaufspreis"
                                                    tabindex="6"
                                                    :disabled="saving"
                                                    :error-messages="errorMessages.for('input.price')"
                                                    v-model="price"
                                    ></b-number-field>
                                </v-flex>
                                <v-flex lg6 offset-lg6 offset-xs4 xs8>
                                    <v-text-field append-icon="mdi-percent"
                                                  hide-details
                                                  label="Rabatt"
                                                  step="0.01"
                                                  tabindex="8"
                                                  type="number"
                                                  :disabled="saving"
                                                  :error-messages="errorMessages.for('input.rebate')"
                                                  v-model="lineValue.rebate"
                                    ></v-text-field>
                                </v-flex>
                                <v-flex lg6 offset-lg6 offset-xs4 xs8>
                                    <v-select
                                        :disabled="saving"
                                        :error-messages="errorMessages.for('input.VAT')"
                                        :items="[{value: 19, text: '19'}, {value: 7, text: '7'}, {value: 0, text: '0'}]"
                                        append-icon="mdi-percent"
                                        hide-details
                                        label="MwSt."
                                        tabindex="9"
                                        v-model="lineValue.VAT"
                                    ></v-select>
                                </v-flex>
                                <v-flex lg6 offset-lg6 offset-xs4 xs8>
                                    <v-text-field append-icon="mdi-percent"
                                                  hide-details
                                                  label="Skonto"
                                                  step="0.01"
                                                  tabindex="10"
                                                  type="number"
                                                  :disabled="saving"
                                                  :error-messages="errorMessages.for('input.discount')"
                                                  v-model="lineValue.discount"
                                    ></v-text-field>
                                </v-flex>
                            </v-layout>
                        </v-flex>
                        <v-flex md2 xs6>
                            <v-layout column>
                                <v-flex>
                                    <b-number-field append-icon="mdi-currency-eur"
                                                    format="0.0000"
                                                    hide-details
                                                    label="Gesamtnettopreis"
                                                    tabindex="7"
                                                    :disabled="saving"
                                                    v-model="net"
                                    ></b-number-field>
                                </v-flex>
                                <v-flex>
                                    <b-number-field append-icon="mdi-currency-eur"
                                                    disabled
                                                    format="0.0000"
                                                    hide-details
                                                    prefix="-"
                                                    v-model="discount"
                                    ></b-number-field>
                                </v-flex>
                                <v-flex>
                                    <b-number-field append-icon="mdi-currency-eur"
                                                    disabled
                                                    format="0.0000"
                                                    hide-details
                                                    prefix="+"
                                                    v-model="tax"
                                    ></b-number-field>
                                </v-flex>
                                <v-flex>
                                    <b-number-field append-icon="mdi-currency-eur"
                                                    disabled
                                                    format="0.0000"
                                                    hide-details
                                                    prefix="-"
                                                    v-model="promptPaymentDiscount"
                                    ></b-number-field>
                                </v-flex>
                                <v-flex>
                                    <b-number-field append-icon="mdi-currency-eur"
                                                    format="0.0000"
                                                    hide-details
                                                    label="Gesamt"
                                                    style="background: rgba(255,255,255,0.1)"
                                                    tabindex="11"
                                                    :disabled="saving"
                                                    v-model="total"
                                    ></b-number-field>
                                </v-flex>
                            </v-layout>
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
                    <v-icon>mdi-plus</v-icon>
                    Hinzufügen
                </v-btn>
                <v-btn :loading="saving"
                       @click="onSave(false)"
                       color="error"
                >
                    <v-icon>mdi-library-plus</v-icon>
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import EntitySelect from "../../../common/EntitySelect.vue"
    import {Invoice, InvoiceLine} from "../../../../models";
    import QuantityUnitsQuery from "./QuantityUnitsQuery.graphql";
    import CreateMutation from "./CreateMutation.graphql";
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../../mixins";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";
    import {FetchResult} from "apollo-link";

    @Component({
        components: {'b-entity-select': EntitySelect},
        mixins: [DisplaysMessages, DisplaysErrors],
        apollo: {
            units: {
                query: QuantityUnitsQuery,
                fetchPolicy: "cache-and-network",
                skip(this: CreateDialog) {
                    return !this.value;
                }
            }
        }
    })
    export default class CreateDialog extends Vue implements DisplaysErrorsContract, DisplaysMessagesContract {
        @Prop({type: Boolean, default: false})
        value: boolean;

        @Prop({type: Object})
        invoice: Invoice;

        @Watch('value')
        onValueChange(val) {
            if (val) {
                this.updateUnits();
                this.lineValue.invoiceId = this.invoice.id;
                this.lineValue.supplierId = this.invoice.issuer.id;
                this.$nextTick(() => this.input.focus());
            }
        }

        selected: any[] = [];

        units: string[] = [];

        lineValue: InvoiceLine = new InvoiceLine();

        saving: boolean = false;

        errorMessages: ErrorMessages;

        clearErrorMessages: () => void;

        extractErrorMessages: (error: any) => void;

        showMessage: <R = any>(message: string) => Promise<FetchResult<R>>;

        get price() {
            return Number(this.lineValue.price);
        }

        set price(val) {
            this.lineValue.price = String(val);
        }

        set net(val) {
            let menge = Number(this.lineValue.quantity);
            this.price = val
                / menge;
        }

        get net() {
            let menge = Number(this.lineValue.quantity);
            let net = this.price
                * menge;
            net = net ? net : 0;
            return net;
        }

        get discount() {
            let rabatt = Number(this.lineValue.rebate);
            let net = this.net
                * (rabatt / 100);
            net = net ? net : 0;
            if (this.lineValue) {
                this.lineValue.netAmount = this.net
                    * this.calcPercentage(rabatt);
            }
            return net;
        }

        get tax() {
            let gross = (this.net - this.discount) * this.mwst;
            return gross ? gross : 0;
        }

        get promptPaymentDiscount() {
            let gross = (this.net - this.discount + this.tax) * (Number(this.lineValue.discount) / 100);
            return gross ? gross : 0;
        }

        get total() {
            let total = this.net - this.discount + this.tax - this.promptPaymentDiscount;
            return total ? total : 0;
        }

        set total(val) {
            this.price = val / this.calcPercentage(Number(this.lineValue.discount))
                / (1 + this.mwst)
                / this.calcPercentage(Number(this.lineValue.rebate))
                / Number(this.lineValue.quantity);
        }

        get mwst() {
            return Number(this.lineValue.VAT) / 100;
        }

        get invoiceItemsFrom() {
            return this.invoice ? this.invoice.issuer.id : undefined;
        }

        select(selected) {
            this.lineValue = new InvoiceLine();
            this.lineValue.invoiceId = this.invoice.id;
            if (selected) {
                this.lineValue.fill(selected);
                setTimeout(() => {
                    this.amountInput.focus();
                    this.amountInput.select();
                }, 200);
            }
        }

        calcPercentage(val: number): number {
            return 1 - (val / 100);
        }

        updateUnits() {
            this.$apollo.queries.units.refresh();
        }

        onSave(close: boolean = true) {
            this.saving = true;
            this.$apollo.mutate({
                mutation: CreateMutation,
                variables: {
                    input: {
                        invoiceId: this.lineValue.invoiceId,
                        supplierId: this.lineValue.supplierId,
                        itemNumber: this.lineValue.itemNumber,
                        quantity: this.lineValue.quantity,
                        price: this.lineValue.price,
                        VAT: this.lineValue.VAT,
                        rebate: this.lineValue.rebate,
                        discount: this.lineValue.discount,
                        netAmount: this.lineValue.netAmount,
                        quantityUnit: this.lineValue.quantityUnit,
                        description: this.lineValue.description,
                    }
                }
            }).then(() => {
                this.saving = false;
                this.showMessage('Position hinzugefügt.');
                if (close) {
                    this.$emit('input', false);
                } else {
                    this.input.focus();
                }
                this.reinit();
            }).catch(error => {
                this.saving = false;
                this.extractErrorMessages(error);
                this.showMessage('Fehler beim Hinzufügen der Position. Nachricht: ' + error.message);
            });
        }

        get input() {
            return ((this.$refs.invoiceItemSelect as EntitySelect).$refs.input as HTMLInputElement);
        }

        get amountInput() {
            return ((this.$refs.amountInput as any).$refs.input as HTMLInputElement);
        }

        reinit() {
            this.selected = [];
            this.lineValue = new InvoiceLine();
            this.lineValue.invoiceId = this.invoice.id;
            this.lineValue.supplierId = this.invoice.issuer.id;
        }
    }
</script>
