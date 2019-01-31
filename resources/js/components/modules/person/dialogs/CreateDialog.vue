<template>
    <app-edit-dialog
            lazy
            large
            :loading="isLoading"
            :show="show"
            @close="onClose"
            @open="$emit('open')"
            @save="onSave"
    >
        <slot></slot>
        <v-text-field
                slot="input"
                :disabled="isLoading"
                :error-messages="errorMessages.lastName"
                label="Nachname"
                v-model="value.lastName"
                type="text"
                prepend-icon="mdi-alphabetical"
        ></v-text-field>
        <v-text-field
                slot="input"
                :disabled="isLoading"
                :error-messages="errorMessages.firstName"
                v-model="value.firstName"
                label="Vorname"
                type="text"
                prepend-icon="mdi-alphabetical"
        ></v-text-field>
        <v-text-field
                slot="input"
                v-model="value.birthday"
                :disabled="isLoading"
                :error-messages="errorMessages.birthday"
                label="Geburtstag"
                type="date"
                prepend-icon="mdi-cake"
        ></v-text-field>
        <v-select :disabled="isLoading"
                  v-model="value.gender"
                  :items="gender"
                  prepend-icon="mdi-alphabetical"
                  label="Geschlecht"
                  slot="input"
        ></v-select>
    </app-edit-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Person} from "../../../../models";
    import CreateMutation from "./CreateMutation.graphql";
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../../mixins";
    import {ApolloQueryResult} from "apollo-client";

    @Component({
        mixins: [DisplaysErrors, DisplaysMessages]
    })
    export default class CreateDialog extends Vue implements DisplaysErrorsContract, DisplaysMessagesContract {

        value: Person = new Person();

        @Prop()
        large: boolean;

        @Prop({type: Boolean})
        show;

        gender: Object[] = [
            {value: null, text: 'unbekannt'},
            {value: 'MALE', text: 'männlich'},
            {value: 'FEMALE', text: 'weiblich'}
        ];

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
                    firstName: this.value.firstName,
                    lastName: this.value.lastName,
                    birthday: this.value.birthday,
                    gender: this.value.gender
                }
            }).then(() => {
                this.loading = false;
                this.value = new Person();
                this.clearErrorMessages();
                this.$emit('save');
                this.showMessage('Person erstellt.');
            }).catch((error) => {
                this.loading = false;
                this.extractErrorMessages(error);
                this.showMessage('Fehler beim Erstellen der Person. Nachricht: ' + error.message);
            })
        }

        onClose() {
            this.$emit('close');
            this.value = new Person();
            this.clearErrorMessages();
        }

        get isLoading() {
            return this.$apollo.loading || this.loading;
        }
    }
</script>
