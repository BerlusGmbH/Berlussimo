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
                        <v-flex xs12 md6>
                            <v-text-field label="Rechnungsnummer"
                                          prepend-icon="mdi-numeric"
                                          v-model="invoiceValue.RECHNUNGSNUMMER"
                                          type="text"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 md6>
                            <v-select label="Rechnungstyp"
                                      prepend-icon="mdi-shape"
                                      :items="types"
                                      v-model="invoiceValue.RECHNUNGSTYP"
                                      type="text"
                            ></v-select>
                        </v-flex>
                        <v-flex xs12 md6 lg4>
                            <v-text-field label="Rechnungsdatum"
                                          prepend-icon="mdi-calendar-blank"
                                          v-model="invoiceValue.RECHNUNGSDATUM"
                                          type="date"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 md6 lg4>
                            <v-text-field label="Eingangsdatum"
                                          prepend-icon="mdi-calendar"
                                          v-model="invoiceValue.EINGANGSDATUM"
                                          type="date"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 md6 lg4>
                            <v-text-field label="Fällig am"
                                          prepend-icon="mdi-calendar-clock"
                                          v-model="invoiceValue.FAELLIG_AM"
                                          type="date"
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
    import {namespace, Mutation} from "vuex-class";
    import {Prop, Watch} from "vue-property-decorator";
    import {
        Invoice
    } from "../../../../server/resources";
    import _ from 'lodash';
    import axios from '../../../../libraries/axios';

    const SnackbarMutation = namespace('shared/snackbar', Mutation);
    const RefreshMutation = namespace('shared/refresh', Mutation);

    @Component
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

        @SnackbarMutation('updateMessage')
        updateMessage: Function;

        @RefreshMutation('requestRefresh')
        requestRefresh: Function;

        saving: boolean = false;

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
                this.requestRefresh();
            });
        }

        updateTypes() {
            axios.get('/api/v1/invoices/types')
                .then(response => {
                    this.types = response.data;
                });
        }
    }
</script>