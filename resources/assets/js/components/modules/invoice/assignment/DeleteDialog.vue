<template>
    <v-dialog :value="value"
              @input="$emit('input', $event)"
              max-width="400"
              lazy
    >
        <v-card>
            <v-card-title>
                <v-icon>mdi-cart-plus</v-icon>
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
    import {InvoiceLineAssignment} from "../../../../server/resources";

    const Snackbar = namespace('shared/snackbar');
    const Refresh = namespace('shared/refresh');

    @Component({components: {'app-entity-select': EntitySelect}})
    export default class DeleteDialog extends Vue {
        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        assignment: InvoiceLineAssignment;

        @Snackbar.Mutation('updateMessage')
        updateMessage: Function;

        @Refresh.Mutation('requestRefresh')
        requestRefresh: Function;

        deleting: boolean = false;

        onDelete() {
            this.deleting = true;
            this.assignment.delete().then(() => {
                this.deleting = false;
                this.updateMessage('Zuweisung entfernt.');
                this.requestRefresh();
                this.$emit('input', false);
            }).catch(error => {
                this.deleting = false;
                this.updateMessage('Fehler beim Entfernen der Zuweisung. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
            });
        }
    }
</script>