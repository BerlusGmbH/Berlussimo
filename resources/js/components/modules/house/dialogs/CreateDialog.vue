<template>
    <app-edit-dialog
        :loading="isLoading"
        :show="show"
        @close="onClose"
        @open="$emit('open')"
        @save="onSave"
        large
        lazy
    >
        <slot></slot>
        <v-text-field :disabled="isLoading"
                      :error-messages="errorMessages.for('input.streetName')"
                      label="Straße"
                      prepend-icon="mdi-alphabetical"
                      slot="input"
                      type="text"
                      v-model="value.streetName"
        ></v-text-field>
        <v-text-field :disabled="isLoading"
                      :error-messages="errorMessages.for('input.streetNumber')"
                      label="Nummer"
                      prepend-icon="mdi-alphabetical"
                      slot="input"
                      type="text"
                      v-model="value.streetNumber"
        ></v-text-field>
        <v-text-field :disabled="isLoading"
                      :error-messages="errorMessages.for('input.postalCode')"
                      label="Postleitzahl"
                      prepend-icon="mdi-numeric"
                      slot="input"
                      type="number"
                      v-model="value.postalCode"
        ></v-text-field>
        <v-text-field :disabled="isLoading"
                      :error-messages="errorMessages.for('input.city')"
                      label="Stadt"
                      prepend-icon="mdi-alphabetical"
                      slot="input"
                      type="text"
                      v-model="value.city"
        ></v-text-field>
        <b-entity-select :disabled="isLoading"
                         :entities="['Property']"
                         :error-messages="errorMessages.for('input.propertyId')"
                         append-icon=""
                         label="Objekt"
                         prepend-icon="mdi-city"
                         slot="input"
                         v-model="value.property"
        >
        </b-entity-select>
    </app-edit-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {House} from "../../../../models";
    import EntitySelect from "../../../common/EntitySelect.vue"
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../../mixins";
    import CreateMutation from "./CreateMutation.graphql";
    import {ApolloQueryResult} from "apollo-client";

    @Component({
        components: {'b-entity-select': EntitySelect},
        mixins: [DisplaysErrors, DisplaysMessages]
    })
    export default class CreateDialog extends Vue implements DisplaysErrorsContract, DisplaysMessagesContract {

        value: House = new House();

        @Prop()
        large: boolean;

        @Prop({type: Boolean})
        show;

        loading: boolean = false;

        errorMessages: ErrorMessages;
        clearErrorMessages: () => void;
        extractErrorMessages: (error: any) => void;
        showMessage: <R = any>(message: string) => Promise<ApolloQueryResult<R>>;

        onSave() {
            this.loading = true;
            this.clearErrorMessages();
            this.$apollo.mutate({
                mutation: CreateMutation,
                variables: {
                    input: {
                        streetName: this.value.streetName,
                        streetNumber: this.value.streetNumber,
                        postalCode: this.value.postalCode,
                        city: this.value.city,
                        propertyId: this.value.property ? this.value.property.id : null
                    }
                }
            }).then(() => {
                this.loading = false;
                this.value = new House();
                this.clearErrorMessages();
                this.$emit('save');
                this.showMessage('Haus erstellt.');
            }).catch((error) => {
                this.loading = false;
                this.extractErrorMessages(error);
                this.showMessage('Fehler beim Erstellen des Hauses. Nachricht: ' + error.message);
            })
        }

        onClose() {
            this.$emit('close');
            this.value = new House();
            this.clearErrorMessages();
        }

        get isLoading() {
            return this.$apollo.loading || this.loading;
        }
    }
</script>
