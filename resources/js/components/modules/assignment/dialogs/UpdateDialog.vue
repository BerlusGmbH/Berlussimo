<template>
    <v-dialog :value="value" @input="$emit('input', $event)" lazy width="1200">
        <v-card>
            <v-card-title class="headline">
                <v-icon>mdi-pencil</v-icon>
                <v-icon>mdi-clipboard</v-icon>
                &nbsp;Auftrag bearbeiten
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <app-entity-select label="Von"
                                           :entities="['Person']"
                                           v-model="assignmentInput.author"
                                           prepend-icon="mdi-account"
                        >
                        </app-entity-select>
                    </v-flex>
                    <v-flex xs12>
                        <app-entity-select label="An"
                                           :entities="['Person', 'Partner']"
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
                                           v-model="assignmentInput.costBearer"
                                           prepend-icon="mdi-account"
                        >
                        </app-entity-select>
                    </v-flex>
                    <v-flex xs12>
                        <v-textarea label="Text"
                                    prepend-icon="mdi-alphabetical"
                                    v-model="assignmentInput.description"
                                    auto-grow
                        >
                        </v-textarea>
                    </v-flex>
                    <v-flex xs12>
                        <v-switch label="Akut"
                                  v-model="assignmentInput.highPriority"
                        >
                        </v-switch>
                    </v-flex>
                </v-layout>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn @click.native="onClose(); $emit('input', false)" flat="flat">Abbrechen</v-btn>
                <v-btn @click.native="onSave(); $emit('input', false)" class="red">Bearbeiten</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import {Assignment} from "../../../../models";
    import _ from "lodash";
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../../mixins";
    import UpdateMutation from "./UpdateMutation.graphql";
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import {ApolloQueryResult} from "apollo-client";

    @Component({
        mixins: [DisplaysErrors, DisplaysMessages]
    })
    export default class UpdateDialog extends Vue implements DisplaysErrorsContract, DisplaysMessagesContract {

        @Prop({type: Object})
        assignment: Assignment;

        @Prop({type: Boolean})
        value: boolean;

        errorMessages: ErrorMessages;
        clearErrorMessages: () => void;
        extractErrorMessages: (error: any) => void;
        showMessage: <R = any>(message: string) => Promise<ApolloQueryResult<R>>;

        assignmentInput: Assignment = new Assignment();

        loading: boolean = false;

        @Watch('value')
        onValueChange(val) {
            if (val && this.assignment) {
                this.assignmentInput = _.cloneDeep(this.assignment);
            }
        }

        onSave() {
            this.loading = true;
            this.clearErrorMessages();
            this.$apollo.mutate({
                mutation: UpdateMutation,
                variables: {
                    input: {
                        id: this.assignmentInput.id,
                        authorId: this.assignmentInput.author ? this.assignmentInput.author.id : null,
                        assignedToId: this.assignmentInput.assignedTo ? this.assignmentInput.assignedTo.id : null,
                        assignedToType: this.assignmentInput.assignedTo ? this.assignmentInput.assignedTo.__typename : null,
                        costBearerId: this.assignmentInput.costBearer ? this.assignmentInput.costBearer.id : null,
                        costBearerType: this.assignmentInput.costBearer ? this.assignmentInput.costBearer.__typename : null,
                        description: this.assignmentInput.description,
                        highPriority: this.assignmentInput.highPriority
                    }
                }
            }).then(() => {
                this.loading = false;
                this.clearErrorMessages();
                this.$emit('save');
                this.showMessage('Auftrag geändert.');
            }).catch((error) => {
                this.loading = false;
                this.extractErrorMessages(error);
                this.showMessage('Fehler beim Ändern des Auftrags. Nachricht: ' + error.message);
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
