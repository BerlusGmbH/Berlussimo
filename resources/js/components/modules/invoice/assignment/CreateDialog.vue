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
                        <v-flex lg1 md6 xs12>
                            <v-text-field label="Menge"
                                          prepend-icon="mdi-numeric"
                                          step="0.01"
                                          type="number"
                                          :error-messages="errorMessages.for('input.quantity')"
                                          v-model="assignmentValue.quantity"
                            ></v-text-field>
                        </v-flex>
                        <v-flex lg3 md6 xs12>
                            <app-entity-select :entities="[
                                                   'Property',
                                                   'House',
                                                   'Unit',
                                                   'Partner',
                                                   'Person',
                                                   'RentalContract',
                                                   'PurchaseContract',
                                                   'ConstructionSite',
                                                   'AccountingEntity'
                                               ]"
                                               :error-messages="errorMessages.for('input.costBearerId')"
                                               @input="selectCostUnit"
                                               label="Kostenträger"
                                               v-model="assignmentValue.costBearer"
                            >
                            </app-entity-select>
                        </v-flex>
                        <v-flex lg2 md6 xs12>
                            <app-entity-select :entities="['BankAccountStandardChart']"
                                               clearable
                                               label="Kontenrahmen"
                                               prepend-icon="mdi-table"
                                               v-model="standardChart"
                            >
                            </app-entity-select>
                        </v-flex>
                        <v-flex lg2 md6 xs12>
                            <app-entity-select :booking-account-in="standardChart ? standardChart.id : undefined"
                                               :entities="['BookingAccount']"
                                               :error-messages="errorMessages.for('input.bookingAccountNumber')"
                                               label="Buchungskonto"
                                               prepend-icon="mdi-numeric"
                                               v-model="assignmentValue.bookingAccount"
                            >
                            </app-entity-select>
                        </v-flex>
                        <v-flex lg2 md6 xs12>
                            <v-switch label="Weiter verwenden"
                                      style="padding-top: 18px"
                                      :error-messages="errorMessages.for('input.reassign')"
                                      v-model="assignmentValue.reassign"
                            ></v-switch>
                        </v-flex>
                        <v-flex lg2 md6 xs12>
                            <b-year-field
                                :error-messages="errorMessages.for('input.yearOfReassignment')"
                                label="Verwendungsjahr"
                                prepend-icon="mdi-timer-sand"
                                v-model="assignmentValue.yearOfReassignment"
                            ></b-year-field>
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
                    <template v-if="line">
                        <v-icon>add</v-icon>
                        Hinzufügen
                    </template>
                    <template v-else>
                        <v-icon>mdi-pencil</v-icon>
                        Ändern
                    </template>
                </v-btn>
                <v-btn :loading="saving"
                       @click="onSave(false)"
                       color="error"
                       v-if="line"
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
    import {BankAccountStandardChart, BookingAccount, InvoiceLine, InvoiceLineAssignment} from "../../../../models";
    import _ from 'lodash';
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../../mixins";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import {FetchResult} from "apollo-link";
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";
    import CreateMutation from "./CreateMutation.graphql";
    import UpdateMutation from "./UpdateMutation.graphql";

    @Component({
        mixins: [
            DisplaysMessages,
            DisplaysErrors
        ]
    })
    export default class CreateDialog extends Vue implements DisplaysMessagesContract, DisplaysErrorsContract {
        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        line: InvoiceLine;

        @Prop({type: Object})
        assignment: InvoiceLineAssignment;

        @Watch('value')
        onLineChange(val) {
            if (val) {
                this.fillAssignment();
                this.initBookingAccount();
            }
        }

        showMessage: <R = any>(message: string) => Promise<FetchResult<R>>;

        errorMessages: ErrorMessages;

        clearErrorMessages: () => void;

        extractErrorMessages: (error: any) => void;

        saving: boolean = false;

        assignmentValue: InvoiceLineAssignment = new InvoiceLineAssignment();

        standardChart: BankAccountStandardChart | null = null;

        selectCostUnit(selected) {
            if (selected) {
                this.assignmentValue.costBearerType = selected.__typename;
                this.assignmentValue.costBearerId = selected.id;
            }
        }

        selectBookingAccount(selected) {
            if (selected) {
                this.assignmentValue.bookingAccountNumber = selected.number;
            }
        }

        onSave(close: boolean = true) {
            this.saving = true;
            this.clearErrorMessages();
            if (this.line) {
                this.$apollo.mutate({
                    mutation: CreateMutation,
                    variables: {
                        input: {
                            lineId: this.line.id,
                            quantity: this.assignmentValue.quantity,
                            costBearerType: this.assignmentValue.costBearerType,
                            costBearerId: this.assignmentValue.costBearerId,
                            bookingAccountNumber: this.assignmentValue.bookingAccount.number,
                            reassign: this.assignmentValue.reassign,
                            yearOfReassignment: this.assignmentValue.yearOfReassignment
                        }
                    }
                }).then(() => {
                    this.saving = false;
                    this.showMessage('Zuweisung hinzugefügt.');
                    if (close) {
                        this.$emit('input', false);
                    }
                    this.$emit('created');
                }).catch(error => {
                    this.saving = false;
                    this.extractErrorMessages(error);
                    this.showMessage('Fehler beim Hinzufügen der Zuweisung. Message: ' + error.message);
                });
            } else {
                this.$apollo.mutate({
                    mutation: UpdateMutation,
                    variables: {
                        input: {
                            id: this.assignmentValue.id,
                            quantity: this.assignmentValue.quantity,
                            costBearerType: this.assignmentValue.costBearerType,
                            costBearerId: this.assignmentValue.costBearerId,
                            bookingAccountNumber: this.assignmentValue.bookingAccount.number,
                            reassign: this.assignmentValue.reassign,
                            yearOfReassignment: this.assignmentValue.yearOfReassignment
                        }
                    }
                }).then(() => {
                    this.saving = false;
                    this.showMessage('Zuweisung geändert.');
                    this.$emit('input', false);
                }).catch(error => {
                    this.saving = false;
                    this.extractErrorMessages(error);
                    this.showMessage('Fehler beim Ändern der Zuweisung. Message: ' + error.message);
                });
            }
        }

        fillAssignment() {
            if (this.line) {
                this.assignmentValue = new InvoiceLineAssignment();
                this.assignmentValue.yearOfReassignment = String(new Date().getFullYear());
                this.assignmentValue.fill(this.line)
            }
            if (this.assignment) {
                this.assignmentValue = _.cloneDeep(this.assignment);
            }
        }

        get costUnit() {
            return this.assignment ? this.assignment.costBearer : null;
        }

        initBookingAccount() {
            if (!this.assignmentValue.bookingAccount && this.assignment) {
                let account = new BookingAccount();
                account.number = this.assignment.bookingAccountNumber;
                account.id = 0;
                account.__typename = BookingAccount.__typename;
                this.assignmentValue.bookingAccount = account;
            }
        }
    }
</script>
