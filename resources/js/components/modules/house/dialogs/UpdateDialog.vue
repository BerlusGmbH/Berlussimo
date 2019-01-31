<template>
    <app-edit-dialog
            lazy
            large
            :positionAbsolutley="positionAbsolutley"
            :positionX="positionX"
            :positionY="positionY"
            :show="show"
            :loading="isLoading"
            @show="$emit('show', $event)"
            @open="onOpen"
            @save="onSave"
            @close="onClose"
    >
        <slot></slot>
        <v-text-field
                slot="input"
                :disabled="isLoading"
                label="Straße"
                type="text"
                prepend-icon="mdi-alphabetical"
                :error-messages="errorMessages.for('input.streetName')"
                v-model="value.streetName"
        ></v-text-field>
        <v-text-field
                slot="input"
                :disabled="isLoading"
                label="Nummer"
                type="text"
                prepend-icon="mdi-alphabetical"
                :error-messages="errorMessages.for('input.streetNumber')"
                v-model="value.streetNumber"
        ></v-text-field>
        <v-text-field
                slot="input"
                :disabled="isLoading"
                label="Postleitzahl"
                type="number"
                prepend-icon="mdi-numeric"
                :error-messages="errorMessages.for('input.postalCode')"
                v-model="value.postalCode"
        ></v-text-field>
        <v-text-field
                slot="input"
                :disabled="isLoading"
                label="Stadt"
                type="text"
                prepend-icon="mdi-alphabetical"
                :error-messages="errorMessages.for('input.city')"
                v-model="value.city"
        ></v-text-field>
        <b-entity-select :disabled="isLoading"
                         :entities="['Property']"
                         :error-messages="errorMessages.for('input.propertyId')"
                         append-icon=""
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
    import _ from "lodash";
    import {House, Model} from "../../../../models";
    import EntitySelect from "../../../common/EntitySelect.vue"
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../../mixins";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";
    import UpdateQuery from "./UpdateQuery.graphql";
    import UpdateMutation from "./UpdateMutation.graphql";
    import {ApolloQueryResult} from "apollo-client";

    @Component({
        components: {'b-entity-select': EntitySelect},
        mixins: [DisplaysErrors, DisplaysMessages]
    })
    export default class UpdateDialog extends Vue implements DisplaysErrorsContract, DisplaysMessagesContract {

        @Prop({type: String})
        houseId: string;

        @Prop()
        large: boolean;

        @Prop()
        type: string;

        @Prop({type: Boolean})
        positionAbsolutley;

        @Prop({type: Number})
        positionX;

        @Prop({type: Number})
        positionY;

        @Prop({type: Boolean})
        show;

        value: House = new House();

        kinds: Array<string> = [];

        loading: boolean = false;

        errorMessages: ErrorMessages;
        clearErrorMessages: () => void;
        extractErrorMessages: (error: any) => void;
        showMessage: <R = any>(message: string) => Promise<ApolloQueryResult<R>>;

        onOpen() {
            this.loading = true;
            this.$apollo.query({
                query: UpdateQuery,
                variables: {
                    id: this.houseId
                },
                fetchPolicy: 'network-only'
            } as any).then(response => {
                if (response.data.house) {
                    this.value = _.cloneDeep(Model.applyPrototype(response.data.house));
                }
                this.loading = false;
            }).catch(() => {
                this.loading = false;
            });
        }

        onSave() {
            this.clearErrorMessages();
            if (this.value && this.value.property) {
                this.loading = true;
                this.$apollo.mutate({
                    mutation: UpdateMutation,
                    variables: {
                        input: {
                            id: this.value.id,
                            streetName: this.value.streetName,
                            streetNumber: this.value.streetNumber,
                            city: this.value.city,
                            postalCode: this.value.postalCode,
                            propertyId: this.value.property.id,
                        }
                    }
                }).then(() => {
                    this.loading = false;
                    this.showMessage('Haus geändert.');
                    this.$emit('close');
                }).catch((error) => {
                    this.loading = false;
                    this.extractErrorMessages(error);
                    this.showMessage('Fehler beim Ändern des Hauses. Nachricht: ' + error.message);
                });
            }
        }

        onClose() {
            this.$emit('close');
            this.clearErrorMessages();
        }

        get isLoading() {
            return this.$apollo.loading || this.loading;
        }
    }
</script>
