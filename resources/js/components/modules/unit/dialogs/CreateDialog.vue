<template>
    <app-edit-dialog
        :loading="isLoading"
        :show="show"
        @close="onClose"
        @open="onOpen"
        @save="onSave"
        large
        lazy
    >
        <slot></slot>
        <v-text-field
            :disabled="isLoading"
            :error-messages="errorMessages.for('input.name')"
            label="Name"
            prepend-icon="mdi-alphabetical"
            slot="input"
            type="text"
            v-model="value.name"
        ></v-text-field>
        <v-text-field
            :disabled="isLoading"
            :error-messages="errorMessages.for('input.size')"
            label="Fläche"
            prepend-icon="mdi-numeric"
            slot="input"
            step="0.01"
            suffix="m²"
            type="number"
            v-model="value.size"
        ></v-text-field>
        <v-text-field
            :disabled="isLoading"
            :error-messages="errorMessages.for('input.location')"
            label="Lage"
            prepend-icon="mdi-alphabetical"
            slot="input"
            type="text"
            v-model="value.location"
        ></v-text-field>
        <v-select :disabled="isLoading"
                  :error-messages="errorMessages.for('input.unitType')"
                  :items="availableUnitTypes"
                  :prepend-icon="value.getKindIcon()"
                  label="Typ"
                  slot="input"
                  v-model="value.unitType"
        ></v-select>
        <b-entity-select :disabled="isLoading"
                         :entities="['House']"
                         :error-messages="errorMessages.for('input.houseId')"
                         append-icon=""
                         label="Haus"
                         prepend-icon="mdi-domain"
                         slot="input"
                         v-model="value.house"
        >
        </b-entity-select>
    </app-edit-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Unit} from "../../../../models";
    import EntitySelect from "../../../common/EntitySelect.vue"
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import UnitTypesQuery from "./UnitTypesQuery.graphql";
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../../mixins";
    import CreateMutation from "./CreateMutation.graphql";
    import {ApolloQueryResult} from "apollo-client";

    @Component({
        components: {'b-entity-select': EntitySelect},
        mixins: [DisplaysErrors, DisplaysMessages]
    })
    export default class CreateDialog extends Vue implements DisplaysErrorsContract, DisplaysMessagesContract {

        value: Unit = new Unit();

        @Prop()
        large: boolean;

        @Prop({type: Boolean})
        show;

        loading: boolean = false;

        availableUnitTypes = null;

        errorMessages: ErrorMessages;
        clearErrorMessages: () => void;
        extractErrorMessages: (error: any) => void;
        showMessage: <R = any>(message: string) => Promise<ApolloQueryResult<R>>;

        onOpen() {
            this.$emit('open');
            this.loading = true;
            this.getAvailableUnitTypes().then(() => {
                this.loading = false;
            }).catch(() => {
                this.loading = false;
            });
        }

        onSave() {
            this.loading = true;
            this.clearErrorMessages();
            this.$apollo.mutate({
                mutation: CreateMutation,
                variables: {
                    input: {
                        name: this.value.name,
                        size: this.value.size,
                        location: this.value.location,
                        unitType: this.value.unitType,
                        houseId: this.value.house ? this.value.house.id : null
                    }
                }
            }).then(() => {
                this.loading = false;
                this.value = new Unit();
                this.clearErrorMessages();
                this.$emit('save');
                this.showMessage('Einheit erstellt.');
            }).catch((error) => {
                this.loading = false;
                this.extractErrorMessages(error);
                this.showMessage('Fehler beim Erstellen der Einheit. Nachricht: ' + error.message);
            })
        }

        onClose() {
            this.$emit('close');
            this.value = new Unit();
            this.clearErrorMessages();
        }

        get isLoading() {
            return this.$apollo.loading || this.loading;
        }

        getAvailableUnitTypes() {
            return this.$apollo.query({
                query: UnitTypesQuery,
                fetchPolicy: 'network-only'
            } as any).then(response => {
                if (response.data.__type) {
                    this.availableUnitTypes = response.data.__type.enumValues.map(v => {
                        return {
                            value: v.name,
                            text: v.description
                        };
                    });
                }
            });
        }
    }
</script>
