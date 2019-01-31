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
        <v-form ref="form"
                slot="input"
        >
            <v-text-field
                    :disabled="isLoading"
                    :error-messages="errorMessages.lastName"
                    label="Name"
                    prepend-icon="mdi-alphabetical"
                    type="text"
                    v-model="value.lastName"
            ></v-text-field>
            <v-text-field
                    :disabled="isLoading"
                    :error-messages="errorMessages.firstName"
                    label="Vorname"
                    prepend-icon="mdi-alphabetical"
                    type="text"
                    v-model="value.firstName"
            ></v-text-field>
            <v-text-field
                    :disabled="isLoading"
                    :error-messages="errorMessages.birthday"
                    label="Geburtstag"
                    prepend-icon="mdi-cake"
                    type="date"
                    v-model="value.birthday"
            ></v-text-field>
            <v-select :disabled="isLoading"
                      :items="gender"
                      label="Geschlecht"
                      prepend-icon="mdi-alphabetical"
                      v-model="value.gender"
            ></v-select>
        </v-form>
    </app-edit-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import {Model, Person} from "../../../../models";
    import UpdateQuery from "./UpdateQuery.graphql";
    import UpdateMutation from "./UpdateMutation.graphql";
    import _ from "lodash";
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../../mixins";
    import {ApolloQueryResult} from "apollo-client";

    @Component({
        mixins: [DisplaysErrors, DisplaysMessages]
    })
    export default class UpdateDialog extends Vue implements DisplaysErrorsContract, DisplaysMessagesContract {
        @Prop()
        personId: string;

        @Prop()
        large: boolean;

        @Prop()
        type: String;

        @Prop({type: Boolean})
        show;

        value: Person = new Person();

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

        @Watch('show')
        onShowChange(v) {
            if (v) {
                this.loading = true;
                this.$apollo.query({
                    query: UpdateQuery,
                    variables: {
                        id: this.personId
                    },
                    fetchPolicy: 'network-only'
                } as any).then(response => {
                    if (response.data.person) {
                        this.value = _.cloneDeep(Model.applyPrototype(response.data.person));
                    }
                    this.loading = false;
                }).catch(() => {
                    this.loading = false;
                });
            }
        }

        onSave() {
            this.loading = true;
            this.clearErrorMessages();
            this.$apollo.mutate({
                mutation: UpdateMutation,
                variables: {
                    id: this.value.id,
                    firstName: this.value.firstName,
                    lastName: this.value.lastName,
                    birthday: this.value.birthday,
                    gender: this.value.gender
                }
            }).then(() => {
                this.loading = false;
                this.showMessage('Person geändert.');
                this.$emit('close');
            }).catch((error) => {
                this.loading = false;
                this.extractErrorMessages(error);
                this.showMessage('Fehler beim Ändern der Person. Nachricht: ' + error.message);
            })
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
