<template>
    <v-layout row wrap>
        <v-flex xs12 md6 lg3>
            <app-entity-select label="Kostenträger"
                               :entities="[
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
                               clearable
                               :error-messages="errorMessages.for('input.costBearerId')"
                               @input="selectCostBearer"
            >
            </app-entity-select>
        </v-flex>
        <v-flex xs12 md6 lg2>
            <app-entity-select label="Kontenrahmen"
                               :entities="['BankAccountStandardChart']"
                               v-model="standardChart"
                               prepend-icon="mdi-table"
                               clearable
            >
            </app-entity-select>
        </v-flex>
        <v-flex xs12 md6 lg2>
            <app-entity-select label="Buchungskonto"
                               :booking-account-in="standardChart ? standardChart.id : undefined"
                               :entities="['BookingAccount']"
                               prepend-icon="mdi-numeric"
                               @input="selectBookingAccount"
                               clearable
                               :error-messages="errorMessages.for('input.bookingAccountNumber')"
            >
            </app-entity-select>
        </v-flex>
        <v-flex xs12 md3 lg2>
            <v-select label="Weiter Verwenden"
                      :error-messages="errorMessages.for('input.reassign')"
                      prepend-icon="mdi-arrow-right-box"
                      :items="[
                                      {
                                          text: 'Ja',
                                          value: true
                                      },
                                      {
                                          text: 'Nein',
                                          value: false
                                      },
                                  ]"
                      clearable
                      v-model="attributes.reassign"
            >
            </v-select>
        </v-flex>
        <v-flex xs12 md3 lg2>
            <b-year-field
                    label="Verwendungsjahr"
                    :error-messages="errorMessages.for('input.yearOfReassignment')"
                    clearable
                    prepend-icon="mdi-timer-sand"
                    v-model="attributes.yearOfReassignment"
            ></b-year-field>
        </v-flex>
        <v-flex xs12 lg1 class="text-xs-right" style="align-self: center">
            <v-btn @click="onSave"
                   class="error"
                   :disabled="lines && lines.length === 0"
                   :loading="saving"
            >Ändern
            </v-btn>
        </v-flex>
    </v-layout>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {BankAccountStandardChart} from "../../../../models";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../../mixins";
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";
    import UpdateBatchMutation from "./UpdateBatchMutation.graphql";
    import {FetchResult} from "apollo-link";

    @Component({
        mixins: [
            DisplaysMessages,
            DisplaysErrors
        ]
    })
    export default class UpdateBatch extends Vue implements DisplaysMessagesContract, DisplaysErrorsContract{
        @Prop({type: Array, default: () => []})
        lines: string[];

        saving: boolean = false;

        reuse: boolean = false;

        standardChart: BankAccountStandardChart | null = null;

        attributes: {
            costBearerType: string | undefined,
            costBearerId: string | undefined,
            bookingAccountNumber: string | undefined,
            reassign: boolean | undefined,
            yearOfReassignment: string | undefined
        } = {
            costBearerType: undefined,
            costBearerId: undefined,
            bookingAccountNumber: undefined,
            reassign: undefined,
            yearOfReassignment: undefined
        };

        showMessage: <R = any>(message: string) => Promise<FetchResult<R>>;

        errorMessages: ErrorMessages;

        clearErrorMessages: () => void;

        extractErrorMessages: (error: any) => void;

        selectCostBearer(selected) {
            if (selected) {
                this.attributes.costBearerType = selected.__typename;
                this.attributes.costBearerId = selected.id;
            } else {
                this.attributes.costBearerType = undefined;
                this.attributes.costBearerId = undefined;
            }
        }

        selectBookingAccount(selected) {
            if (selected) {
                this.attributes.bookingAccountNumber = selected.number;
            } else {
                this.attributes.bookingAccountNumber = undefined;
            }
        }

        onSave() {
            this.saving = true;
            this.clearErrorMessages();
            this.$apollo.mutate({
                mutation: UpdateBatchMutation,
                variables: {
                    input: {
                        ids: this.lines,
                        costBearerType: this.attributes.costBearerType ? this.attributes.costBearerType : undefined,
                        costBearerId: this.attributes.costBearerId ? this.attributes.costBearerId : undefined,
                        bookingAccountNumber: this.attributes.bookingAccountNumber
                            ? this.attributes.bookingAccountNumber : undefined,
                        reassign: this.attributes.reassign !== null ? this.attributes.reassign : undefined,
                        yearOfReassignment: this.attributes.yearOfReassignment
                            ? this.attributes.yearOfReassignment : undefined
                    }
                }
            }).then(() => {
                this.saving = false;
                this.$emit('updated');
                this.showMessage('Zuweisungen geändert.');
            }).catch(error => {
                this.saving = false;
                this.extractErrorMessages(error);
                this.showMessage('Fehler beim Ändern der Zuweisungen. Message: ' + error.message);
            });
        }
    }
</script>
