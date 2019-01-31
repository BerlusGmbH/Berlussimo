<template>
    <v-dialog :value="value" @input="$emit('input', $event)" lazy width="480">
        <v-card>
            <v-card-title class="headline">
                <v-icon>mdi-content-copy</v-icon>
                <v-icon>mdi-city</v-icon>
                &nbsp;Objekt kopieren
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <app-entity-select :entities="['Property']"
                                           :error-messages="errorMessages.for('input.id')"
                                           :value="property"
                                           @input="val => input.id = val.id"
                                           disabled
                                           label="Quellobjekt"
                                           prepend-icon="mdi-city"
                        >
                        </app-entity-select>
                    </v-flex>
                    <v-flex xs12>
                        <v-text-field :error-messages="errorMessages.for('input.name')"
                                      label="Neuer Name"
                                      prepend-icon="mdi-alphabetical"
                                      v-model="input.name"
                        >
                        </v-text-field>
                    </v-flex>
                    <v-flex xs12>
                        <v-text-field :error-messages="errorMessages.for('input.prefix')"
                                      label="Präfix für Einheiten"
                                      prepend-icon="mdi-alphabetical"
                                      v-model="input.prefix"
                        >
                        </v-text-field>
                    </v-flex>
                    <v-flex xs12>
                        <app-entity-select :entities="['Partner']"
                                           :error-messages="errorMessages.for('input.ownerId')"
                                           @input="val => input.ownerId = val.id"
                                           label="Neuer Eigentümer"
                                           prepend-icon="mdi-account-multiple"
                                           v-model="owner"
                        >
                        </app-entity-select>
                    </v-flex>
                    <v-flex xs12>
                        <v-text-field :error-messages="errorMessages.for('input.openingBalanceDate')"
                                      label="Datum für Saldovortrag Vorverwaltung"
                                      prepend-icon="mdi-calendar"
                                      type="date"
                                      v-model="input.openingBalanceDate"
                        >
                        </v-text-field>
                    </v-flex>
                    <v-flex xs12>
                        <v-switch :error-messages="errorMessages.for('input.openingBalance')"
                                  label="Saldo übernehmen"
                                  v-model="input.openingBalance"
                        >
                        </v-switch>
                    </v-flex>
                </v-layout>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn @click.native="$emit('input', false)" flat="flat">Abbrechen</v-btn>
                <v-btn :loading="loading"
                       @click.native="copy()"
                       class="red"
                >Kopieren
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import {Partner, Property} from "../../../../models";
    import CopyPropertyMutation from "./CopyMutation.graphql";
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../../mixins";
    import {ApolloQueryResult} from "apollo-client";

    @Component({
        mixins: [DisplaysErrors, DisplaysMessages]
    })
    export default class CopyPropertyDialog extends Vue implements DisplaysErrorsContract, DisplaysMessagesContract {

        @Prop({type: Object})
        property: Property;

        @Prop({type: Boolean})
        value: boolean;

        owner: Partner | null = null;

        loading: boolean = false;

        errorMessages: ErrorMessages;
        clearErrorMessages: () => void;
        extractErrorMessages: (error: any) => void;
        showMessage: <R = any>(message: string) => Promise<ApolloQueryResult<R>>;

        @Watch('value')
        onValueChange(val) {
            if (val && this.property) {
                this.input.id = this.property.id;
            }
        }

        input: {
            id: number | null;
            openingBalance: boolean;
            openingBalanceDate: string | null;
            name: string | null;
            prefix: string | null;
            ownerId: number | null;
        } = {
            id: null,
            openingBalance: false,
            openingBalanceDate: null,
            name: null,
            prefix: null,
            ownerId: null
        };

        copy() {
            if (this.property) {
                this.loading = true;
                this.clearErrorMessages();
                this.$apollo.mutate({
                    mutation: CopyPropertyMutation,
                    variables: {
                        input: this.input
                    }
                }).then(() => {
                    this.loading = false;
                    this.$emit('input', false);
                    this.showMessage('Objekt wird kopiert.');
                }).catch(error => {
                    this.loading = false;
                    this.extractErrorMessages(error);
                    this.showMessage('Fehler beim Kopieren des Objekts. Nachricht: ' + error.message);
                });
            }

        }
    }
</script>
