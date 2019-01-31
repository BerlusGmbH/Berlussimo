<template>
    <app-edit-dialog
            :positionAbsolutley="positionAbsolutley"
            :positionX="positionX"
            :positionY="positionY"
            :show="show"
            :loading="isLoading"
            @open="onOpen"
            @save="onSave"
            @close="onClose"
            @show="$emit('show', $event)"
            large
            lazy
    >
        <slot></slot>
        <v-text-field
                label="Name"
                prepend-icon="mdi-alphabetical"
                slot="input"
                type="text"
                :disabled="isLoading"
                :error-messages="errorMessages.for('input.name')"
                v-model="value.name"
        ></v-text-field>
        <b-entity-select :disabled="isLoading"
                         :entities="['Partner']"
                         :error-messages="errorMessages.for('input.ownerId')"
                         append-icon=""
                         label="Eigentümer"
                         prepend-icon="mdi-account-multiple"
                         slot="input"
                         v-model="value.owner"
        >
        </b-entity-select>
    </app-edit-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import _ from "lodash";
    import {Model, Property} from "../../../../models";
    import EntitySelect from "../../../common/EntitySelect.vue"
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../../mixins";
    import UpdateQuery from "./UpdateQuery.graphql";
    import UpdateMutation from "./UpdateMutation.graphql";
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import {ApolloQueryResult} from "apollo-client";

    @Component({
        components: {'b-entity-select': EntitySelect},
        mixins: [DisplaysErrors, DisplaysMessages]
    })
    export default class ObjectEditDialog extends Vue implements DisplaysErrorsContract, DisplaysMessagesContract {

        @Prop({type: String})
        propertyId: string;

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

        value: Property = new Property();

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
                    id: this.propertyId
                },
                fetchPolicy: 'network-only'
            } as any).then(response => {
                if (response.data.property) {
                    this.value = _.cloneDeep(Model.applyPrototype(response.data.property));
                }
                this.loading = false;
            }).catch(() => {
                this.loading = false;
            });
        }

        onSave() {
            this.clearErrorMessages();
            if (this.value && this.value.owner) {
                this.loading = true;
                this.$apollo.mutate({
                    mutation: UpdateMutation,
                    variables: {
                        input: {
                            id: this.value.id,
                            name: this.value.name,
                            ownerId: this.value.owner.id,
                        }
                    }
                }).then(() => {
                    this.loading = false;
                    this.showMessage('Objekt geändert.');
                    this.$emit('close');
                }).catch((error) => {
                    this.loading = false;
                    this.extractErrorMessages(error);
                    this.showMessage('Fehler beim Ändern des Objekts. Nachricht: ' + error.message);
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
