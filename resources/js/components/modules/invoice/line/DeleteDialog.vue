<template>
    <v-dialog :value="value"
              @input="$emit('input', $event)"
              max-width="400"
              lazy
    >
        <v-card>
            <v-card-title>
                <v-icon>mdi-cart-remove</v-icon>
                <span class="headline">Position entfernen</span>
            </v-card-title>
            <v-card-text>
                <v-container fluid grid-list-md>
                    <v-layout row wrap>
                        <v-flex xs12>
                            Möchten Sie Position {{line ? line.position : ''}} entfernen?
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn flat @click="$emit('input', false)">Abbrechen</v-btn>
                <v-btn color="error"
                       :loading="deleting"
                       @click="onDelete"
                >
                    <v-icon>mdi-cart-remove</v-icon>
                    Entfernen
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Invoice, InvoiceLine} from "../../../../models";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import {DisplaysMessagesContract} from "../../../../mixins";
    import {FetchResult} from "apollo-link";
    import DeleteMutation from "./DeleteMutation.graphql";

    @Component({
        mixins: [DisplaysMessages]
    })
    export default class DetailView extends Vue implements DisplaysMessagesContract {
        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        invoice: Invoice;

        @Prop({type: Object})
        line: InvoiceLine;

        deleting: boolean = false;

        showMessage: <R = any>(message: string) => Promise<FetchResult<R>>;

        onDelete() {
            this.deleting = true;
            this.$apollo.mutate({
                mutation: DeleteMutation,
                variables: {
                    id: this.line.id
                }
            }).then(() => {
                this.deleting = false;
                this.showMessage('Position entfernt.');
                this.$emit('input', false);
                this.$emit('deleted');
            }).catch(error => {
                this.deleting = false;
                this.showMessage('Fehler beim Entfernen der Position. Nachricht: ' + error.message);
            });
        }
    }
</script>
