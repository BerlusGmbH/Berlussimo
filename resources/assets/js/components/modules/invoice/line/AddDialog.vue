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
                            <app-entity-select @input="select"
                                               :value="selected"
                                               append-icon=""
                                               prepend-icon="mdi-cart-outline"
                                               label="Artikel"
                                               :entities="entities"
                                               tabindex="1"
                                               ref="invoiceItemSelect"
                            >
                            </app-entity-select>
                        </v-flex>
                        <v-flex xs12 md2>
                            <v-text-field label="Artikelnummer"
                                          v-model="lineValue.ARTIKEL_NR"
                                          type="text"
                                          tabindex="2"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 md4>
                            <v-text-field label="Beschreibung"
                                          v-model="lineValue.BEZEICHNUNG"
                                          type="text"
                                          multi-line
                                          tabindex="3"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs6 md1>
                            <v-text-field label="Menge"
                                          v-model="lineValue.MENGE"
                                          step="0.01"
                                          type="number"
                                          tabindex="4"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs6 md1>
                            <v-select label="Einheit"
                                      v-model="lineValue.EINHEIT"
                                      :items="units"
                                      item-value="V_EINHEIT"
                                      item-text="BEZEICHNUNG"
                                      autocomplete
                                      tabindex="5"
                            ></v-select>
                        </v-flex>
                        <v-flex xs6 md2>
                            <v-layout column>
                                <v-flex>
                                    <b-number-field append-icon="mdi-currency-eur"
                                                    label="Einkaufspreis"
                                                    v-model="price"
                                                    format="0.0000"
                                                    tabindex="6"
                                                    hide-details
                                    ></b-number-field>
                                </v-flex>
                                <v-flex offset-xs4 xs8 offset-lg6 lg6>
                                    <v-text-field append-icon="mdi-percent"
                                                  label="Rabatt"
                                                  v-model="lineValue.RABATT_SATZ"
                                                  step="0.01"
                                                  type="number"
                                                  tabindex="8"
                                                  hide-details
                                    ></v-text-field>
                                </v-flex>
                                <v-flex offset-xs4 xs8 offset-lg6 lg6>
                                    <v-select append-icon="mdi-percent"
                                              label="MwSt."
                                              :items="[19, 7]"
                                              v-model="lineValue.MWST_SATZ"
                                              tabindex="9"
                                              hide-details
                                    ></v-select>
                                </v-flex>
                                <v-flex offset-xs4 xs8 offset-lg6 lg6>
                                    <v-text-field append-icon="mdi-percent"
                                                  label="Skonto"
                                                  v-model="lineValue.SKONTO"
                                                  step="0.01"
                                                  type="number"
                                                  tabindex="10"
                                                  hide-details
                                    ></v-text-field>
                                </v-flex>
                            </v-layout>
                        </v-flex>
                        <v-flex xs6 md2>
                            <v-layout column>
                                <v-flex>
                                    <b-number-field append-icon="mdi-currency-eur"
                                                    label="Gesamtnettopreis"
                                                    v-model="net"
                                                    format="0.0000"
                                                    tabindex="7"
                                                    hide-details
                                    ></b-number-field>
                                </v-flex>
                                <v-flex>
                                    <b-number-field append-icon="mdi-currency-eur"
                                                    prefix="-"
                                                    v-model="discount"
                                                    format="0.0000"
                                                    disabled
                                                    hide-details
                                    ></b-number-field>
                                </v-flex>
                                <v-flex>
                                    <b-number-field append-icon="mdi-currency-eur"
                                                    prefix="+"
                                                    v-model="tax"
                                                    format="0.0000"
                                                    disabled
                                                    hide-details
                                    ></b-number-field>
                                </v-flex>
                                <v-flex>
                                    <b-number-field append-icon="mdi-currency-eur"
                                                    prefix="-"
                                                    v-model="promptPaymentDiscount"
                                                    format="0.0000"
                                                    disabled
                                                    hide-details
                                    ></b-number-field>
                                </v-flex>
                                <v-flex>
                                    <b-number-field append-icon="mdi-currency-eur"
                                                    label="Gesamt"
                                                    v-model="total"
                                                    format="0.0000"
                                                    tabindex="11"
                                                    style="background: rgba(255,255,255,0.1)"
                                                    hide-details
                                    ></b-number-field>
                                </v-flex>
                            </v-layout>
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
                    <v-icon>mdi-plus</v-icon>
                    Hinzufügen
                </v-btn>
                <v-btn color="error"
                       :loading="saving"
                       @click="onSave(false)"
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
    import {Mutation, namespace} from "vuex-class";
    import {Prop, Watch} from "vue-property-decorator";
    import EntitySelect from "../../../common/EntitySelect.vue"
    import {Invoice, InvoiceLine} from "../../../../server/resources";
    import axios from "../../../../libraries/axios";

    const SnackbarMutation = namespace('shared/snackbar', Mutation);
    const RefreshMutation = namespace('shared/refresh', Mutation);

    @Component({components: {'app-entity-select': EntitySelect}})
    export default class AddDialog extends Vue {
        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        invoice: Invoice;

        @Watch('value')
        onValueChange(val) {
            if (val) {
                this.updateUnits();
                this.lineValue.BELEG_NR = this.invoice.BELEG_NR;
                this.lineValue.ART_LIEFERANT = this.invoice.from.PARTNER_ID;
                this.$nextTick(() => this.input.focus());
            }
        }

        selected: Array<any> = [];

        units: Array<string> = [];

        @SnackbarMutation('updateMessage')
        updateMessage: Function;

        @RefreshMutation('requestRefresh')
        requestRefresh: Function;

        lineValue: InvoiceLine = new InvoiceLine();

        saving: boolean = false;

        get price() {
            return Number(this.lineValue.PREIS);
        }

        set price(val) {
            this.lineValue.PREIS = String(val);
        }

        set net(val) {
            let menge = Number(this.lineValue.MENGE);
            this.price = val
                / menge;
        }

        get net() {
            let menge = Number(this.lineValue.MENGE);
            let net = this.price
                * menge;
            net = net ? net : 0;
            return net;
        }

        get discount() {
            let rabatt = Number(this.lineValue.RABATT_SATZ);
            let net = this.net
                * (rabatt / 100);
            net = net ? net : 0;
            if (this.lineValue) {
                this.lineValue.GESAMT_NETTO = this.net
                    * this.calcPercentage(rabatt);
            }
            return net;
        }

        get tax() {
            let gross = (this.net - this.discount) * this.mwst;
            return gross ? gross : 0;
        }

        get promptPaymentDiscount() {
            let gross = (this.net - this.discount + this.tax) * (Number(this.lineValue.SKONTO) / 100);
            return gross ? gross : 0;
        }

        get total() {
            let total = this.net - this.discount + this.tax - this.promptPaymentDiscount;
            return total ? total : 0;
        }

        set total(val) {
            this.price = val / this.calcPercentage(Number(this.lineValue.SKONTO))
                / (1 + this.mwst)
                / this.calcPercentage(Number(this.lineValue.RABATT_SATZ))
                / Number(this.lineValue.MENGE);
        }

        get mwst() {
            return Number(this.lineValue.MWST_SATZ) / 100;
        }

        get entities() {
            return this.invoice ? ['artikel:' + this.invoice.from.PARTNER_ID] : ['artikel'];
        }

        select(selected) {
            this.lineValue = new InvoiceLine();
            this.lineValue.BELEG_NR = this.invoice.BELEG_NR;
            if (selected) {
                this.lineValue.fill(selected);
            }
        }

        calcPercentage(val: number): number {
            return 1 - (val / 100);
        }

        updateUnits() {
            axios.get('/api/v1/invoices/units')
                .then(response => {
                    this.units = response.data;
                });
        }

        onSave(close: boolean = true) {
            this.saving = true;
            this.lineValue.create().then(() => {
                this.saving = false;
                this.updateMessage('Position hinzugefügt.');
                this.requestRefresh();
                if (close) {
                    this.$emit('input', false);
                } else {
                    this.input.focus();
                }
                this.reinit();
            }).catch(error => {
                this.saving = false;
                this.updateMessage('Fehler beim Hinzufügen der Position. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
            });
        }

        get input() {
            return ((this.$refs.invoiceItemSelect as EntitySelect).$refs.input as HTMLInputElement);
        }

        reinit() {
            this.selected = [];
            this.lineValue = new InvoiceLine();
        }
    }
</script>