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
                                               :entities="entities">
                            </app-entity-select>
                        </v-flex>
                        <v-flex xs12 md10>
                            <v-text-field label="Artikelnummer"
                                          v-model="lineValue.ARTIKEL_NR"
                                          type="text"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 md2>
                            <v-select label="Einheit"
                                      v-model="lineValue.EINHEIT"
                                      :items="units"
                                      item-value="V_EINHEIT"
                                      item-text="BEZEICHNUNG"
                                      autocomplete
                            ></v-select>
                        </v-flex>
                        <v-flex xs12>
                            <v-text-field label="Beschreibung"
                                          v-model="lineValue.BEZEICHNUNG"
                                          multi-line
                                          type="text"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 sm4 lg2>
                            <b-number-field append-icon="mdi-currency-eur"
                                            label="Preis"
                                            v-model="price"
                                            format="0.0000"
                            ></b-number-field>
                        </v-flex>
                        <v-flex xs12 sm2 lg1>
                            <v-text-field label="Menge"
                                          v-model="lineValue.MENGE"
                                          step="0.01"
                                          type="number"
                                          :suffix="lineValue.EINHEIT"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 sm2 lg1>
                            <v-text-field append-icon="mdi-percent"
                                          label="Rabatt"
                                          v-model="lineValue.RABATT_SATZ"
                                          step="0.01"
                                          type="number"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 sm4 lg2>
                            <b-number-field append-icon="mdi-currency-eur"
                                            label="Netto-Summe"
                                            v-model="net"
                                            format="0.0000"
                            ></b-number-field>
                        </v-flex>
                        <v-flex xs12 sm2 lg1>
                            <v-text-field append-icon="mdi-percent"
                                          label="MwSt"
                                          v-model="lineValue.MWST_SATZ"
                                          type="number"
                                          step="1"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 sm4 lg2>
                            <b-number-field append-icon="mdi-currency-eur"
                                            label="Brutto-Summe"
                                            v-model="gross"
                                            format="0.0000"
                            ></b-number-field>
                        </v-flex>
                        <v-flex xs12 sm2 lg1>
                            <v-text-field append-icon="mdi-percent"
                                          label="Skonto"
                                          v-model="lineValue.SKONTO"
                                          step="0.01"
                                          type="number"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 sm4 lg2>
                            <b-number-field append-icon="mdi-currency-eur"
                                            label="Gesamt-Summe"
                                            v-model="total"
                                            format="0.0000"
                            ></b-number-field>
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
    export default class DetailView extends Vue {
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

        get net() {
            let net = this.price * Number(this.lineValue.MENGE) * ((100 - Number(this.lineValue.RABATT_SATZ)) / 100);
            net = net ? net : 0;
            if (this.lineValue) {
                this.lineValue.GESAMT_NETTO = net;
            }
            return net;
        }

        set net(val) {
            if (this.lineValue) {
                this.lineValue.GESAMT_NETTO = val;
            }
            this.price = val / ((100 - Number(this.lineValue.RABATT_SATZ)) / 100) / Number(this.lineValue.MENGE);
        }

        get gross() {
            let gross = this.net * ((100 + Number(this.lineValue.MWST_SATZ)) / 100);
            return gross ? gross : 0;
        }

        set gross(val) {
            this.net = val / ((100 + Number(this.lineValue.MWST_SATZ)) / 100);
        }

        get total() {
            let total = this.gross * ((100 - Number(this.lineValue.SKONTO)) / 100);
            return total ? total : 0;
        }

        set total(val) {
            this.gross = val / ((100 - Number(this.lineValue.SKONTO)) / 100);
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
                }
            }).catch(error => {
                this.saving = false;
                this.updateMessage('Fehler beim Hinzufügen der Position. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
            });
        }
    }
</script>