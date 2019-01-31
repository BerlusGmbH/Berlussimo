<template>
    <v-dialog :value="value"
              @input="$emit('input', $event)"
              lazy
              width="1200"
    >
        <v-card>
            <v-card-title class="headline">
                <v-icon>add</v-icon>
                <v-icon>mdi-clipboard</v-icon>
                &nbsp;Auftrag erstellen
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <app-entity-select label="Von"
                                           :entities="['Person']"
                                           :error-messages="errorMessages.for('input.authorId')"
                                           v-model="assignmentInput.author"
                                           prepend-icon="mdi-account"
                        >
                        </app-entity-select>
                    </v-flex>
                    <v-flex xs12>
                        <app-entity-select label="An"
                                           :entities="['Person', 'Partner']"
                                           :error-messages="errorMessages.for('input.assignedToId')"
                                           v-model="assignmentInput.assignedTo"
                                           prepend-icon="mdi-account"
                        >
                        </app-entity-select>
                    </v-flex>
                    <v-flex xs12>
                        <app-entity-select label="Kostenträger"
                                           :entities="[
                                               'Property',
                                               'House',
                                               'Unit',
                                               'Partner',
                                               'Person',
                                               'RentalContract',
                                               'PurchaseContract',
                                               'ConstructionSite',
                                               'AccountingEntity'
                                           ]"
                                           :error-messages="errorMessages.for('input.costBearerId')"
                                           v-model="assignmentInput.costBearer"
                                           prepend-icon="mdi-account"
                        >
                        </app-entity-select>
                    </v-flex>
                    <v-flex xs12>
                        <v-textarea label="Text"
                                    prepend-icon="mdi-alphabetical"
                                    :error-messages="errorMessages.for('input.description')"
                                    v-model="assignmentInput.description"
                                    auto-grow
                        >
                        </v-textarea>
                    </v-flex>
                    <v-flex xs12>
                        <v-switch label="Akut"
                                  :error-messages="errorMessages.for('input.highPriority')"
                                  v-model="assignmentInput.highPriority"
                        >
                        </v-switch>
                    </v-flex>
                </v-layout>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn @click.native="onClose(); $emit('input', false)" flat="flat">Abbrechen</v-btn>
                <v-btn @click.native="onSave(); $emit('input', false)" class="red">Erstellen</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Assignment, Model, Person} from "../../../../models";
    import EntitySelect from "../../../common/EntitySelect.vue";
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../../mixins";
    import UserQuery from "../../../auth/UserQuery.graphql";
    import CreateMutation from "./CreateMutation.graphql"
    import {ApolloQueryResult} from "apollo-client";

    @Component({
        components: {'b-entity-select': EntitySelect},
        mixins: [DisplaysErrors, DisplaysMessages],
        apollo: {
            user: {
                query: UserQuery,
                update(this: CreateDialog, data) {
                    if (data.state && data.state.user) {
                        const user = Model.applyPrototype(data.state.user);
                        this.$set(this.assignmentInput, 'author', user);
                        return user;
                    }
                    return null;
                }
            }
        }
    })
    export default class CreateDialog extends Vue implements DisplaysErrorsContract, DisplaysMessagesContract {

        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        costBearer: any;

        user: Person;

        assignmentInput: Assignment = new Assignment();

        loading: boolean = false;

        errorMessages: ErrorMessages;
        clearErrorMessages: () => void;
        extractErrorMessages: (error: any) => void;
        showMessage: <R = any>(message: string) => Promise<ApolloQueryResult<R>>;

        mounted() {
            this.initAssignment()
        }

        initAssignment() {
            this.assignmentInput = new Assignment();
            if (this.user) {
                this.$set(this.assignmentInput, 'author', this.user);
            }
            if (this.costBearer) {
                this.$set(this.assignmentInput, 'costBearer', this.costBearer);
            }
        }

        onSave() {
            this.loading = true;
            this.clearErrorMessages();
            this.$apollo.mutate({
                mutation: CreateMutation,
                variables: {
                    input: {
                        authorId: this.assignmentInput.author ? this.assignmentInput.author.id : null,
                        assignedToId: this.assignmentInput.assignedTo ? this.assignmentInput.assignedTo.id : null,
                        assignedToType: this.assignmentInput.assignedTo ? this.assignmentInput.assignedTo.__typename : null,
                        costBearerId: this.assignmentInput.costBearer ? this.assignmentInput.costBearer.id : null,
                        costBearerType: this.assignmentInput.costBearer ? this.assignmentInput.costBearer.__typename : null,
                        description: this.assignmentInput.description,
                        highPriority: this.assignmentInput.highPriority,
                    }
                }
            }).then(() => {
                this.loading = false;
                this.initAssignment();
                this.clearErrorMessages();
                this.$emit('save');
                this.showMessage('Auftrag erstellt.');
            }).catch((error) => {
                this.loading = false;
                this.extractErrorMessages(error);
                this.showMessage('Fehler beim Erstellen des Auftrags. Nachricht: ' + error.message);
            })
        }

        onClose() {
            this.$emit('close');
            this.initAssignment();
            this.clearErrorMessages();
        }

        get isLoading() {
            return this.$apollo.loading || this.loading;
        }
    }
</script>
