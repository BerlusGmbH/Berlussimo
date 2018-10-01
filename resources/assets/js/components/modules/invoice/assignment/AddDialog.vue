<template>
    <v-dialog :value="value" @input="$emit('input', $event)">
        <v-card>
            <v-card-title>
                <span class="headline">
                    <template v-if="line">
                        <v-icon>add</v-icon>
                        Zuweisung hinzufügen
                    </template>
                    <template v-else>
                        <v-icon>mdi-pencil</v-icon>
                        Zuweisung ändern
                    </template>
                </span>
            </v-card-title>
            <v-card-text>
                <v-container fluid grid-list-md>
                    <v-layout row wrap>
                        <v-flex xs12 md6 lg1>
                            <v-text-field label="Menge"
                                          v-model="assignmentValue.MENGE"
                                          prepend-icon="mdi-numeric"
                                          type="number"
                                          step="0.01"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 md6 lg3>
                            <b-entity-select label="Kostenträger"
                                             @input="selectCostUnit"
                                             :value="costUnit"
                                             :entities="[
                                                   'objekt',
                                                   'haus',
                                                   'einheit',
                                                   'partner',
                                                   'person',
                                                   'mietvertrag',
                                                   'kaufvertrag',
                                                   'baustelle',
                                                   'wirtschaftseinheit'
                                               ]"
                            >
                            </b-entity-select>
                        </v-flex>
                        <v-flex xs12 md6 lg2>
                            <b-entity-select label="Kontenrahmen"
                                             :entities="['kontenrahmen']"
                                             v-model="standardChart"
                                             prepend-icon="mdi-table"
                                             clearable
                            >
                            </b-entity-select>
                        </v-flex>
                        <v-flex xs12 md6 lg2>
                            <b-entity-select label="Buchungskonto"
                                             :entities="bookingAccountEntity"
                                             prepend-icon="mdi-numeric"
                                             @input="selectBookingAccount"
                                             :value="bookingAccount"
                            >
                            </b-entity-select>
                        </v-flex>
                        <v-flex xs12 md6 lg2>
                            <v-switch label="Weiter verwenden"
                                      style="padding-top: 18px"
                                      v-model="reuse"
                            ></v-switch>
                        </v-flex>
                        <v-flex xs12 md6 lg2>
                            <b-year-field
                                    label="Verwendungsjahr"
                                    v-model="assignmentValue.VERWENDUNGS_JAHR"
                                    prepend-icon="mdi-timer-sand"
                            ></b-year-field>
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
                    <template v-if="line">
                        <v-icon>add</v-icon>
                        Hinzufügen
                    </template>
                    <template v-else>
                        <v-icon>mdi-pencil</v-icon>
                        Ändern
                    </template>
                </v-btn>
                <v-btn v-if="line"
                       color="error"
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
    import {namespace} from "vuex-class";
    import {Prop, Watch} from "vue-property-decorator";
    import {
        BankAccountStandardChart,
        BookingAccount,
        InvoiceLine,
        InvoiceLineAssignment
    } from "../../../../server/resources";
    import _ from 'lodash';

    const SnackbarModule = namespace('shared/snackbar');
    const RefreshModule = namespace('shared/refresh');

    @Component
    export default class AddDialog extends Vue {
        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        line: InvoiceLine;

        @Prop({type: Object})
        assignment: InvoiceLineAssignment;

        @Watch('line')
        onLineChange(val) {
            if (val) {
                this.fillAssignment();
            }
        }

        @Watch('assignment')
        onAssignmentChange(val) {
            if (val) {
                this.fillAssignment();
            }
        }

        @Watch('reuse')
        onReuseChange(val) {
            this.assignmentValue.WEITER_VERWENDEN = val ? '1' : '0';
        }

        mounted() {
            this.assignmentValue.VERWENDUNGS_JAHR = String(new Date().getFullYear());
            this.fillAssignment();
        }

        @SnackbarModule.Mutation('updateMessage')
        updateMessage: Function;

        @RefreshModule.Mutation('requestRefresh')
        requestRefresh: Function;

        saving: boolean = false;

        assignmentValue: InvoiceLineAssignment = new InvoiceLineAssignment();

        reuse: boolean = false;

        standardChart: BankAccountStandardChart | null = null;

        selectCostUnit(selected) {
            if (selected) {
                this.assignmentValue.KOSTENTRAEGER_TYP = selected.getMorphName();
                this.assignmentValue.KOSTENTRAEGER_ID = selected.getID();
            }
        }

        selectBookingAccount(selected) {
            if (selected) {
                this.assignmentValue.KONTENRAHMEN_KONTO = selected.KONTO;
            }
        }

        onSave(close: boolean = true) {
            this.saving = true;
            if (this.line) {
                this.assignmentValue.create().then(() => {
                    this.saving = false;
                    this.updateMessage('Zuweisung hinzugefügt.');
                    this.requestRefresh();
                    if (close) {
                        this.$emit('input', false);
                    }
                }).catch(error => {
                    this.saving = false;
                    this.updateMessage('Fehler beim Hinzufügen der Zuweisung. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
                });
            } else {
                this.assignmentValue.save().then(() => {
                    this.saving = false;
                    this.updateMessage('Zuweisung geändert.');
                    this.requestRefresh();
                    this.$emit('input', false);
                }).catch(error => {
                    this.saving = false;
                    this.updateMessage('Fehler beim Ändern der Zuweisung. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
                    this.requestRefresh();
                });
            }
        }

        fillAssignment() {
            if (this.line) {
                this.assignmentValue.fill(this.line)
            }
            if (this.assignment) {
                this.assignmentValue = _.cloneDeep(this.assignment);
                this.reuse = this.assignment.WEITER_VERWENDEN === '1';
            }
        }

        get bookingAccountEntity() {
            return this.standardChart ? ['buchungskonto:' + this.standardChart.KONTENRAHMEN_ID] : ['buchungskonto'];
        }

        get costUnit() {
            return this.assignment ? this.assignment.cost_unit : null;
        }

        get bookingAccount() {
            let account: BookingAccount | null = null;
            if (this.assignment) {
                account = new BookingAccount();
                account.KONTO = this.assignment.KONTENRAHMEN_KONTO;
                account.KONTENRAHMEN_KONTO_ID = 0;
            }
            return account;
        }
    }
</script>