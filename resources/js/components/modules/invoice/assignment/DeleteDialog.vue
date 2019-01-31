<template>
    <v-dialog :value="value"
              @input="$emit('input', $event)"
              max-width="400"
              lazy
    >
        <v-card>
            <v-card-title>
                <v-icon>mdi-playlist-remove</v-icon>
                <span class="headline">Zuweisung entfernen</span>
            </v-card-title>
            <v-card-text>
                <v-container fluid grid-list-md>
                    <v-layout row wrap>
                        <v-flex xs12>
                            Möchten Sie die Zuweisung entfernen?
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
                    <v-icon>delete</v-icon>
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
    import EntitySelect from "../../../common/EntitySelect.vue"
    import DeleteMutation from "./DeleteMutation.graphql";
    import {InvoiceLineAssignment} from "../../../../models";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import {FetchResult} from "apollo-link";

    @Component({
        components: {'app-entity-select': EntitySelect},
        mixins: [DisplaysMessages]
    })
    export default class DeleteDialog extends Vue implements DisplaysMessages {
        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        assignment: InvoiceLineAssignment;

        showMessage: <R = any>(message: string) => Promise<FetchResult<R>>;

        deleting: boolean = false;

        onDelete() {
            this.deleting = true;
            this.$apollo.mutate({
                mutation: DeleteMutation,
                variables: {
                    id: this.assignment.id
                }
            }).then(() => {
                this.deleting = false;
                this.showMessage('Zuweisung entfernt.');
                this.$emit('input', false);
                this.$emit('deleted');
            }).catch(error => {
                this.deleting = false;
                this.showMessage('Fehler beim Entfernen der Zuweisung. Message: ' + error.message);
            });
        }
    }
</script>
