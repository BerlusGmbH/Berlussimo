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
                                          v-model="inputLine.ARTIKEL_NR"
                                          type="text"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 md2>
                            <v-select label="Einheit"
                                      v-model="inputLine.EINHEIT"
                                      :items="units"
                                      item-value="V_EINHEIT"
                                      item-text="BEZEICHNUNG"
                                      autocomplete
                            ></v-select>
                        </v-flex>
                        <v-flex xs12>
                            <v-text-field label="Beschreibung"
                                          v-model="inputLine.BEZEICHNUNG"
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
                                          v-model="inputLine.MENGE"
                                          step="0.01"
                                          type="number"
                                          :suffix="inputLine.EINHEIT"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 sm2 lg1>
                            <v-text-field append-icon="mdi-percent"
                                          label="Rabatt"
                                          v-model="inputLine.RABATT_SATZ"
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
                                          v-model="inputLine.MWST_SATZ"
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
                                          v-model="inputLine.SKONTO"
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
    import {namespace, Mutation} from "vuex-class";
    import {Prop, Watch} from "vue-property-decorator";
    import EntitySelect from "../../../common/EntitySelect.vue"
    import {Invoice, InvoiceLine} from "../../../../server/resources/models";
    import axios from "../../../../libraries/axios";
    import _ from 'lodash';

    const SnackbarMutation = namespace('shared/snackbar', Mutation);
    const RefreshMutation = namespace('shared/refresh', Mutation);

    @Component({components: {'app-entity-select': EntitySelect}})
    export default class DetailView extends Vue {
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
                    this.inputLine = _.cloneDeep(this.line);
                } else {
                    this.inputLine = new InvoiceLine();
                }
            }
        }

        inputLine: InvoiceLine = new InvoiceLine();

        selected: Array<any> = [];

        units: Array<string> = [];

        @SnackbarMutation('updateMessage')
        updateMessage: Function;

        @RefreshMutation('requestRefresh')
        requestRefresh: Function;

        saving: boolean = false;

        get price() {
            return Number(this.inputLine.PREIS);
        }

        set price(val) {
            this.inputLine.PREIS = String(val);
        }

        get net() {
            let net = this.price * Number(this.inputLine.MENGE) * ((100 - Number(this.inputLine.RABATT_SATZ)) / 100);
            net = net ? net : 0;
            if (this.inputLine) {
                this.inputLine.GESAMT_NETTO = net;
            }
            return net;
        }

        set net(val) {
            if (this.inputLine) {
                this.inputLine.GESAMT_NETTO = val;
            }
            this.price = val / ((100 - Number(this.inputLine.RABATT_SATZ)) / 100) / this.inputLine.MENGE;
        }

        get gross() {
            let gross = this.net * ((100 + Number(this.inputLine.MWST_SATZ)) / 100);
            return gross ? gross : 0;
        }

        set gross(val) {
            this.net = val / ((100 + Number(this.inputLine.MWST_SATZ)) / 100);
        }

        get total() {
            let total = this.gross * ((100 - Number(this.inputLine.SKONTO)) / 100);
            return total ? total : 0;
        }

        set total(val) {
            this.gross = val / ((100 - Number(this.inputLine.SKONTO)) / 100);
        }

        get entities() {
            return this.invoice ? ['artikel:' + this.invoice.from.PARTNER_ID] : ['artikel'];
        }

        select(selected) {
            if (selected) {
                this.inputLine.PREIS = selected.LISTENPREIS;
                this.inputLine.SKONTO = selected.SKONTO;
                this.inputLine.MWST_SATZ = selected.MWST_SATZ;
                this.inputLine.RABATT_SATZ = selected.RABATT_SATZ;
                this.inputLine.ART_LIEFERANT = selected.ART_LIEFERANT;
                this.inputLine.ARTIKEL_NR = selected.ARTIKEL_NR;
                this.inputLine.EINHEIT = selected.EINHEIT;
                this.inputLine.BEZEICHNUNG = selected.BEZEICHNUNG;
            }
        }

        updateUnits() {
            axios.get('/api/v1/invoices/units')
                .then(response => {
                    this.units = response.data;
                });
        }

        onSave() {
            this.inputLine.BELEG_NR = this.invoice.BELEG_NR;
            this.saving = true;
            this.inputLine.save().then(() => {
                this.saving = false;
                this.updateMessage('Position geändert.');
                this.requestRefresh();
                this.$emit('input', false);
            }).catch(error => {
                this.saving = false;
                this.updateMessage('Fehler beim Ändern der Position. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
                this.requestRefresh();
            });
        }
    }
</script>