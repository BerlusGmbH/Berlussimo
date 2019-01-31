<template>
    <v-dialog :value="value"
              @input="$emit('input', $event)"
              max-width="400"
    >
        <v-card>
            <v-card-title>
                <v-icon>mdi-cart-remove</v-icon>
                <span class="headline">Positionen entfernen</span>
            </v-card-title>
            <v-card-text>
                <v-container fluid grid-list-md>
                    <v-layout row wrap>
                        <v-flex v-if="!deleting" xs12>
                            Möchten Sie {{lines.length}} Positionen entfernen?
                        </v-flex>
                        <template v-else>
                            <v-flex xs12>
                                <v-progress-linear v-model="progress"
                                ></v-progress-linear>
                            </v-flex>
                            <v-flex class="error--text" xs12>
                                Fehlgeschlagen: {{errors}}
                            </v-flex>
                        </template>
                    </v-layout>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn :disabled="deleting"
                       @click="$emit('input', false)"
                       flat
                >Abbrechen
                </v-btn>
                <v-btn :loading="deleting"
                       @click="onDelete"
                       color="error"
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
    import EntitySelect from "../../../common/EntitySelect.vue"
    import {Invoice, InvoiceLine} from "../../../../models";
    import {FetchResult} from "apollo-link";
    import DeleteMutation from "./DeleteMutation.graphql";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import {DisplaysMessagesContract} from "../../../../mixins";

    @Component({
        components: {'app-entity-select': EntitySelect},
        mixins: [DisplaysMessages]
    })
    export default class DeleteBatchDialog extends Vue implements DisplaysMessagesContract {
        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        invoice: Invoice;

        @Prop({type: Array, default: () => []})
        lines: InvoiceLine[];

        showMessage: <R = any>(message: string) => Promise<FetchResult<R>>;

        deleting: boolean = false;
        progress: number = 0;
        errors: number = 0;

        onDelete() {
            if (this.lines.length > 0) {
                this.deleting = true;

                this.progress = 0;
                this.errors = 0;
                let lineIds: number[] = this.lines.map(v => v.id);
                let error: Error | null = null;

                let chain: Promise <FetchResult> = this.$apollo.mutate({
                    mutation: DeleteMutation,
                    variables: {
                        id: lineIds[0]
                    }
                });



                for (let i = 1; i < lineIds.length; i++) {
                    chain = (chain as Promise<FetchResult>).then(() => {
                        this.progress = (i / lineIds.length) * 100;
                        return this.$apollo.mutate({
                            mutation: DeleteMutation,
                            variables: {
                                id: lineIds[i]
                            }
                        });
                    }, err => {
                        this.progress = (i / lineIds.length) * 100;
                        error = err;
                        this.errors++;
                        return this.$apollo.mutate({
                            mutation: DeleteMutation,
                            variables: {
                                id: lineIds[i]
                            }
                        });
                    });
                }

                chain = chain.then(response => {
                    this.progress = 100;
                    if (error) {
                        throw error;
                    }
                    return new Promise<FetchResult>((resolve) => {
                        resolve(response);
                    });
                }, error => {
                    this.progress = 100;
                    this.errors++;
                    throw error;
                });

                chain.then(() => {
                    this.deleting = false;
                    this.showMessage('Positionen entfernt.');
                    this.$emit('deleted');
                    this.$emit('input', false);
                }, error => {
                    this.deleting = false;
                    this.$emit('deleted');
                    this.showMessage('Fehler beim Entfernen der Positionen. Nachricht: ' + error.message);
                });
            }
        }
    }
</script>
