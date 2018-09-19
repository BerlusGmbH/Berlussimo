<template>
    <v-dialog :value="value"
              @input="$emit('input', $event)"
              max-width="400"
    >
        <v-card>
            <v-card-title>
                <v-icon>mdi-cart-plus</v-icon>
                <span class="headline">Positionen entfernen</span>
            </v-card-title>
            <v-card-text>
                <v-container fluid grid-list-md>
                    <v-layout row wrap>
                        <v-flex xs12 v-if="!deleting">
                            Möchten Sie {{lines.length}} Positionen entfernen?
                        </v-flex>
                        <template v-else>
                            <v-flex xs12>
                                <v-progress-linear v-model="progress"
                                ></v-progress-linear>
                            </v-flex>
                            <v-flex xs12 class="error--text">
                                Fehlgeschlagen: {{lines.length}}
                            </v-flex>
                        </template>
                    </v-layout>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn flat
                       @click="$emit('input', false)"
                       :disabled="deleting"
                >Abbrechen
                </v-btn>
                <v-btn color="error"
                       :loading="deleting"
                       @click="onDelete"
                >
                    <v-icon>add</v-icon>
                    Entfernen
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {namespace} from "vuex-class";
    import {Prop} from "vue-property-decorator";
    import EntitySelect from "../../../common/EntitySelect.vue"
    import {Invoice, InvoiceLine} from "../../../../server/resources";
    import {AxiosResponse} from "axios";

    const SnackbarModule = namespace('shared/snackbar');
    const RefreshModule = namespace('shared/refresh');

    @Component({components: {'app-entity-select': EntitySelect}})
    export default class DetailView extends Vue {
        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        invoice: Invoice;

        @Prop({type: Array})
        lines: Array<InvoiceLine>;

        @SnackbarModule.Mutation('updateMessage')
        updateMessage: Function;

        @RefreshModule.Mutation('requestRefresh')
        requestRefresh: Function;

        deleting: boolean = false;
        progress: number = 0;
        errors: number = 0;

        onDelete() {
            if (this.lines.length) {
                this.deleting = true;

                this.progress = 0;
                let l: number = this.lines.length;
                let linesCopy: Array<InvoiceLine> = [];
                linesCopy[0] = (this.lines.shift() as InvoiceLine);
                let chain: Promise<AxiosResponse> = linesCopy[0].delete();
                let error: Error | null = null;

                for (let i = 1; i < l; i++) {
                    linesCopy[i] = (this.lines.shift() as InvoiceLine);
                    chain = chain.then(() => {
                        this.progress = ((i + 1) / l) * 100;
                        return linesCopy[i].delete();
                    }, err => {
                        this.progress = ((i + 1) / l) * 100;
                        this.lines.push(linesCopy[i - 1]);
                        error = err;
                        return linesCopy[i].delete();
                    });
                }

                chain = chain.then(response => {
                    this.progress = 100;
                    if (error) {
                        throw error;
                    }
                    return new Promise<AxiosResponse>((resolve) => {
                        resolve(response);
                    });
                }, error => {
                    this.progress = 100;
                    this.lines.push(linesCopy[l - 1]);
                    throw error;
                });

                chain.then(() => {
                    this.deleting = false;
                    this.updateMessage('Positionen entfernt.');
                    this.requestRefresh();
                    this.$emit('input', false);
                }, error => {
                    this.deleting = false;
                    this.requestRefresh();
                    this.updateMessage('Fehler beim Entfernen der Positionen. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
                });
            }
        }
    }
</script>