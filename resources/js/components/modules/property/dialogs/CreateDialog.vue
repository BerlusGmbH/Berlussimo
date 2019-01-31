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
        <v-text-field
                slot="input"
                :disabled="isLoading"
                :error-messages="errorMessages.for('input.name')"
                v-model="value.name"
                label="Name"
                type="text"
                prepend-icon="mdi-alphabetical"
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
    import {Property} from "../../../../models";
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

        value: Property = new Property();

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
                        name: this.value.name,
                        ownerId: this.value.owner ? this.value.owner.id : null
                    }
                }
            }).then(() => {
                this.loading = false;
                this.value = new Property();
                this.clearErrorMessages();
                this.$emit('save');
                this.showMessage('Objekt erstellt.');
            }).catch((error) => {
                this.loading = false;
                this.extractErrorMessages(error);
                this.showMessage('Fehler beim Erstellen des Objekts. Nachricht: ' + error.message);
            })
        }

        onClose() {
            this.$emit('close');
            this.value = new Property();
            this.clearErrorMessages();
        }

        get isLoading() {
            return this.$apollo.loading || this.loading;
        }
    }
</script>
