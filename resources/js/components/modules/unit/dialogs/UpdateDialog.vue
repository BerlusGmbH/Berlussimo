<template>
    <app-edit-dialog
            lazy
            large
            :positionAbsolutley="positionAbsolutley"
            :positionX="positionX"
            :positionY="positionY"
            :show="show"
            @show="$emit('show', $event)"
            @open="onOpen"
            @save="onSave"
            :loading="isLoading"
            @close="onClose"
    >
        <slot></slot>
        <v-text-field
                slot="input"
                :disabled="isLoading"
                label="Name"
                type="text"
                prepend-icon="mdi-alphabetical"
                :error-messages="errorMessages.for('input.name')"
                v-model="value.name"
        ></v-text-field>
        <v-text-field
                slot="input"
                :disabled="isLoading"
                label="Fläche"
                type="number"
                step="0.01"
                prepend-icon="mdi-numeric"
                suffix="m²"
                :error-messages="errorMessages.for('input.size')"
                v-model="value.size"
        ></v-text-field>
        <v-text-field
                slot="input"
                :disabled="isLoading"
                label="Lage"
                type="text"
                prepend-icon="mdi-alphabetical"
                :error-messages="errorMessages.for('input.location')"
                v-model="value.location"
        ></v-text-field>
        <v-select :disabled="isLoading"
                  :items="kinds"
                  :error-messages="errorMessages.for('input.unitType')"
                  label="Art"
                  slot="input"
                  :prepend-icon="value.getKindIcon()"
                  v-model="value.unitType"
        ></v-select>
        <b-entity-select :disabled="isLoading"
                         :entities="['House']"
                         :error-messages="errorMessages.for('input.houseId')"
                         append-icon=""
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
    import _ from "lodash";
    import {Model, Unit} from "../../../../models";
    import EntitySelect from "../../../common/EntitySelect.vue"
    import UpdateQuery from "./UpdateQuery.graphql";
    import UpdateMutation from "./UpdateMutation.graphql";
    import UnitTypesQuery from "./UnitTypesQuery.graphql";
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../../mixins";
    import {ApolloQueryResult} from "apollo-client";

    @Component({
        components: {'b-entity-select': EntitySelect},
        mixins: [DisplaysErrors, DisplaysMessages]
    })
    export default class UpdateDialog extends Vue implements DisplaysErrorsContract, DisplaysMessagesContract {

        @Prop({type: String})
        unitId: string;

        @Prop()
        large: boolean;

        @Prop()
        type: String;

        @Prop({type: Boolean})
        positionAbsolutley;

        @Prop({type: Number})
        positionX;

        @Prop({type: Number})
        positionY;

        @Prop({type: Boolean})
        show;

        value: Unit = new Unit();

        kinds: string[] = [];

        loading: boolean = false;

        errorMessages: ErrorMessages;
        clearErrorMessages: () => void;
        extractErrorMessages: (error: any) => void;
        showMessage: <R = any>(message: string) => Promise<ApolloQueryResult<R>>;

        onOpen() {
            this.loading = true;
            let p1 = this.getAvailableUnitTypes();
            let p2 = this.getUnit();
            Promise.all([p1, p2]).then(() => {
                this.loading = false;
            }).catch(() => {
                this.loading = false;
            })
        }

        onSave() {
            this.clearErrorMessages();
            if (this.value && this.value.house) {
                this.loading = true;
                this.$apollo.mutate({
                    mutation: UpdateMutation,
                    variables: {
                        input: {
                            id: this.value.id,
                            name: this.value.name,
                            size: this.value.size,
                            location: this.value.location,
                            unitType: this.value.unitType,
                            houseId: this.value.house.id,
                        }
                    }
                }).then(() => {
                    this.loading = false;
                    this.showMessage('Einheit geändert.');
                    this.$emit('close');
                }).catch((error) => {
                    this.loading = false;
                    this.extractErrorMessages(error);
                    this.showMessage('Fehler beim Ändern des Einheit. Nachricht: ' + error.message);
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

        getAvailableUnitTypes() {
            return this.$apollo.query({
                query: UnitTypesQuery,
                fetchPolicy: 'network-only'
            } as any).then(response => {
                if (response.data.__type) {
                    this.kinds = response.data.__type.enumValues.map(v => {
                        return {
                            value: v.name,
                            text: v.description
                        };
                    });
                }
            });
        }

        getUnit() {
            return this.$apollo.query({
                query: UpdateQuery,
                variables: {
                    id: this.unitId
                },
                fetchPolicy: 'network-only'
            } as any).then(response => {
                if (response.data.unit) {
                    this.value = _.cloneDeep(Model.applyPrototype(response.data.unit));
                }
            });
        }
    }
</script>
