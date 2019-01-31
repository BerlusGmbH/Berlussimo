<template>
    <v-layout row wrap>
        <v-flex xs12 md5>
            <v-text-field prepend-icon="mdi-percent"
                          label="Rabatt"
                          type="number"
                          step="0.01"
                          :error-messages="errorMessages.for('input.rebate')"
                          v-model="attributes.rebate"
                          clearable
            >
            </v-text-field>
        </v-flex>
        <v-flex xs12 md5>
            <v-text-field prepend-icon="mdi-percent"
                          label="Skonto"
                          type="number"
                          step="0.01"
                          :error-messages="errorMessages.for('input.discount')"
                          v-model="attributes.discount"
                          clearable
            >
            </v-text-field>
        </v-flex>

        <v-flex xs12 md2 class="text-xs-right" style="align-self: center">
            <v-btn @click="onSave"
                   class="error"
                   :disabled="lines.length === 0"
                   :loading="saving"
            >Ändern
            </v-btn>
        </v-flex>
    </v-layout>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../../mixins";
    import {FetchResult} from "apollo-link";
    import UpdateBatchMutation from "./UpdateBatchMutation.graphql";
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";

    @Component({
        mixins: [
            DisplaysMessages,
            DisplaysErrors
        ]
    })
    export default class EditBatch extends Vue implements DisplaysMessagesContract, DisplaysErrorsContract {
        @Prop({type: Array, default: () => []})
        lines: string[];

        showMessage: <R = any>(message: string) => Promise<FetchResult<R>>;

        saving: boolean = false;

        attributes: {
            rebate: string,
            discount: string
        } = {
            rebate: '',
            discount: ''
        };

        errorMessages: ErrorMessages;

        clearErrorMessages: () => void;

        extractErrorMessages: (error: any) => void;

        onSave() {
            this.saving = true;
            this.clearErrorMessages();
            this.$apollo.mutate({
                mutation: UpdateBatchMutation,
                variables: {
                    input: {
                        ids: this.lines,
                        rebate: this.attributes.rebate ? this.attributes.rebate : undefined,
                        discount: this.attributes.discount ? this.attributes.discount : undefined
                    }
                }
            }).then(() => {
                this.saving = false;
                this.$emit('changed');
                this.showMessage('Positionen geändert.');
            }).catch(error => {
                this.saving = false;
                this.$emit('changed');
                this.extractErrorMessages(error);
                this.showMessage('Fehler beim Ändern der Positionen. Message: ' + error.message);
            });
        }
    }
</script>
