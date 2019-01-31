<template>
    <v-dialog :value="value" @input="$emit('input', $event)">
        <v-card>
            <v-card-title>
                <v-icon>mdi-cart-plus</v-icon>
                <span class="headline">Position ändern</span>
            </v-card-title>
            <v-card-text>
                <v-container fluid grid-list-md>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <app-entity-select :entities="['InvoiceItem']"
                                               :invoice-items-from="invoiceItemsFrom"
                                               :value="selected"
                                               @input="select"
                                               append-icon=""
                                               label="Artikel"
                                               prepend-icon="mdi-cart-outline"
                                               ref="invoiceItemSelect"
                                               tabindex="1"
                            >
                            </app-entity-select>
                        </v-flex>
                        <v-flex md2 xs12>
                            <v-text-field label="Artikelnummer"
                                          tabindex="2"
                                          type="text"
                                          v-model="lineValue.itemNumber"
                            ></v-text-field>
                        </v-flex>
                        <v-flex md4 xs12>
                            <v-textarea label="Beschreibung"
                                        tabindex="3"
                                        type="text"
                                        v-model="lineValue.description"
                            ></v-textarea>
                        </v-flex>
                        <v-flex md1 xs6>
                            <v-text-field label="Menge"
                                          step="0.01"
                                          tabindex="4"
                                          type="number"
                                          v-model="lineValue.quantity"
                            ></v-text-field>
                        </v-flex>
                        <v-flex md1 xs6>
                            <v-autocomplete :items="units"
                                            item-text="description"
                                            item-value="name"
                                            label="Einheit"
                                            tabindex="5"
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
                                                  v-model="lineValue.rebate"
                                    ></v-text-field>
                                </v-flex>
                                <v-flex lg6 offset-lg6 offset-xs4 xs8>
                                    <v-select
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
    import EntitySelect from "../../../common/EntitySelect.vue";
    import {Invoice, InvoiceItem, InvoiceLine} from "../../../../models";
    import _ from 'lodash';
    import QuantityUnitsQuery from "./QuantityUnitsQuery.graphql";
    import UpdateMutation from "./UpdateMutation.graphql";
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../../mixins";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";
    import {FetchResult} from "apollo-link";

    @Component({
        components: {'app-entity-select': EntitySelect},
        mixins: [DisplaysMessages, DisplaysErrors],
        apollo: {
            units: {
                query: QuantityUnitsQuery,
                fetchPolicy: "cache-and-network",
                skip(this: UpdateDialog) {
                    return !this.value;
                }
            }
        }
    })
    export default class UpdateDialog extends Vue implements DisplaysErrorsContract, DisplaysMessagesContract {
        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        invoice: Invoice;

        @Prop({type: Object})
        line: InvoiceLine;

        @Watch('value')
        onValueChange(val) {
            if (val) {
                this.updateUnits();
                this.selected = [];
                if (this.line) {
                    this.lineValue = _.cloneDeep(this.line);
                } else {
                    this.lineValue = new InvoiceLine();
                }
            }
        }

        errorMessages: ErrorMessages;

        clearErrorMessages: () => void;

        extractErrorMessages: (error: any) => void;

        showMessage: <R = any>(message: string) => Promise<FetchResult<R>>;

        lineValue: InvoiceLine = new InvoiceLine();

        selected: any[] = [];

        units: string[] = [];

        saving: boolean = false;

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

        calcPercentage(val: number): number {
            return 1 - (val / 100);
        }

        select(selected: InvoiceItem) {
            if (selected) {
                this.lineValue.price = selected.price;
                this.lineValue.discount = selected.discount;
                this.lineValue.VAT = selected.VAT;
                this.lineValue.rebate = selected.rebate;
                this.lineValue.supplierId = selected.supplierId;
                this.lineValue.itemNumber = selected.itemNumber;
                this.lineValue.quantityUnit = selected.quantityUnit;
                this.lineValue.description = selected.description;
            }
        }

        updateUnits() {
            this.$apollo.queries.units.refresh();
        }

        onSave() {
            this.lineValue.invoiceId = this.invoice.id;
            this.saving = true;
            this.$apollo.mutate({
                mutation: UpdateMutation,
                variables: {
                    input: {
                        id: this.line.id,
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
                this.showMessage('Position geändert.');
                this.$emit('input', false);
            }).catch(error => {
                this.saving = false;
                this.extractErrorMessages(error);
                this.showMessage('Fehler beim Ändern der Position. Nachricht: ' + error.message);
            });
        }
    }
</script>
