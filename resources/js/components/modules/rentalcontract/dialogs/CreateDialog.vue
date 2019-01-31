<template>
    <v-dialog :value="value"
              @input="onClose()"
              lazy
              width="80%"
    >
        <v-card>
            <v-card-title class="headline">
                <v-icon>add</v-icon>
                <v-icon>mdi-circle</v-icon>
                &nbsp;Mietvertrag erstellen
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex sm6 xs12>
                        <app-entity-select :entities="['Person']"
                                           :disabled="isLoading"
                                           label="Mieter"
                                           multiple
                                           prepend-icon="mdi-account"
                                           v-model="rentalContractInput.tenants"
                                           :error-messages="errorMessages.for('input.tenants')"
                        >
                        </app-entity-select>
                    </v-flex>
                    <v-flex sm6 xs12>
                        <app-entity-select :entities="['Unit']"
                                           :error-messages="errorMessages.for('input.unitId')"
                                           label="Einheit"
                                           prepend-icon="mdi-cube-outline"
                                           unit-for-rent
                                           v-model="rentalContractInput.unit"
                                           :disabled="isLoading"
                        >
                        </app-entity-select>
                    </v-flex>
                    <v-flex sm6 xs12>
                        <v-text-field :error-messages="errorMessages.for('input.start')"
                                      label="Einzug am"
                                      prepend-icon="mdi-calendar-today"
                                      type="date"
                                      v-model="rentalContractInput.start"
                                      :disabled="isLoading"
                        >
                        </v-text-field>
                    </v-flex>
                    <v-flex sm6 xs12>
                        <v-text-field :error-messages="errorMessages.for('input.end')"
                                      label="Auszug am"
                                      prepend-icon="mdi-calendar-range"
                                      type="date"
                                      v-model="rentalContractInput.end"
                                      :disabled="isLoading"
                        >
                        </v-text-field>
                    </v-flex>
                    <v-flex sm6 xs12>
                        <v-text-field :error-messages="errorMessages.for('input.baseRent')"
                                      label="Kaltmiete"
                                      min="0"
                                      prepend-icon="mdi-currency-eur"
                                      step="0.01"
                                      type="number"
                                      v-model="baseRent"
                                      :disabled="isLoading"
                        >
                        </v-text-field>
                    </v-flex>
                    <v-flex sm6 xs12>
                        <v-text-field :error-messages="errorMessages.for('input.deposit')"
                                      label="Sollkaution"
                                      min="0"
                                      prepend-icon="mdi-shield-home"
                                      step="0.01"
                                      type="number"
                                      v-model="deposit"
                                      :disabled="isLoading"
                        >
                        </v-text-field>
                    </v-flex>
                    <v-flex sm6 xs12>
                        <v-text-field :error-messages="errorMessages.for('input.operatingCostAdvance')"
                                      label="Nebenkosten Vorauszahlung"
                                      min="0"
                                      prepend-icon="mdi-delete"
                                      step="0.01"
                                      type="number"
                                      v-model="operatingCostAdvance"
                                      :disabled="isLoading"
                        >
                        </v-text-field>
                    </v-flex>
                    <v-flex sm6 xs12>
                        <v-text-field :error-messages="errorMessages.for('input.heatingCostAdvance')"
                                      label="Heizkosten Vorauszahlung"
                                      min="0"
                                      prepend-icon="mdi-radiator"
                                      step="0.01"
                                      type="number"
                                      v-model="heatingCostAdvance"
                                      :disabled="isLoading"
                        >
                        </v-text-field>
                    </v-flex>
                </v-layout>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn @click.native="onClose()"
                       flat
                >Abbrechen
                </v-btn>
                <v-btn :loading="isLoading"
                       @click.native="onSave()"
                       class="red"
                >Erstellen
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {RentalContract} from "../../../../models";
    import EntitySelect from "../../../common/EntitySelect.vue";
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../../mixins";
    import CreateMutation from "./CreateMutation.graphql"
    import {ApolloQueryResult} from "apollo-client";

    @Component({
        components: {'b-entity-select': EntitySelect},
        mixins: [DisplaysErrors, DisplaysMessages]
    })
    export default class CreateDialog extends Vue implements DisplaysErrorsContract, DisplaysMessagesContract {

        @Prop({type: Boolean})
        value: boolean;

        rentalContractInput: RentalContract = new RentalContract();

        loading: boolean = false;

        errorMessages: ErrorMessages;
        clearErrorMessages: () => void;
        extractErrorMessages: (error: any) => void;
        showMessage: <R = any>(message: string) => Promise<ApolloQueryResult<R>>;

        operatingCostAdvance: string = '';
        heatingCostAdvance: string = '';
        baseRent: string = '';
        deposit: string = '';

        onSave() {
            this.loading = true;
            this.clearErrorMessages();
            this.$apollo.mutate({
                mutation: CreateMutation,
                variables: {
                    input: {
                        start: this.rentalContractInput.start,
                        end: this.rentalContractInput.end,
                        unitId: this.rentalContractInput.unit ? this.rentalContractInput.unit.id : null,
                        tenants: this.tenantIds,
                        baseRent: this.baseRent,
                        operatingCostAdvance: this.operatingCostAdvance,
                        heatingCostAdvance: this.heatingCostAdvance,
                        deposit: this.deposit
                    }
                }
            }).then(() => {
                this.loading = false;
                this.clearInput();
                this.clearErrorMessages();
                this.$emit('save');
                this.showMessage('Mietvertrag erstellt.');
            }).catch((error) => {
                this.loading = false;
                this.extractErrorMessages(error);
                this.showMessage('Fehler beim Erstellen des Mietvertrags. Nachricht: ' + error.message);
            })
        }

        clearInput() {
            this.rentalContractInput = new RentalContract();
            this.operatingCostAdvance = '';
            this.heatingCostAdvance = '';
            this.baseRent = '';
            this.deposit = '';
        }

        onClose() {
            this.clearInput();
            this.$emit('close');
            this.clearErrorMessages();
        }

        get isLoading() {
            return this.$apollo.loading || this.loading;
        }

        get tenantIds() {
            if(this.rentalContractInput.tenants) {
                return this.rentalContractInput.tenants.map(p => p.id);
            }
            return [];
        }
    }
</script>
