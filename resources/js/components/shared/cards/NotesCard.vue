<template>
    <v-card>
        <v-card-title>
            <h3 class="headline">{{headline}}</h3>
            <v-chip color="primary">
                <h3>{{details.length}}</h3>
            </v-chip>
            <v-spacer></v-spacer>
            <v-text-field
                append-icon="search"
                hide-details
                label="Search"
                single-line
                v-model="search"
            ></v-text-field>
        </v-card-title>
        <v-data-table
            :headers="headers"
            :hide-actions="details.length <= 5"
            :items="details"
            :search="search"
            class="elevation-1"
        >
            <template slot="items" slot-scope="props">
                <td>{{props.item.value}}</td>
                <td>{{props.item.comment}}</td>
                <td class="text-xs-right">
                    <div style="display: flex">
                        <b-detail-edit-dialog :error-messages="errorMessages"
                                              :loading.sync="loading"
                                              :parent="parent"
                                              :show.sync="edit[props.index]"
                                              :value="props.item"
                                              @input="saveDetail($event, props.index)"
                                              large
                                              prepend-icon="mdi-table"
                        >
                            <v-icon @click.stop="$set(edit, props.index, true)" style="cursor: pointer">mdi-pencil
                            </v-icon>
                        </b-detail-edit-dialog>
                        <v-icon @click.stop="$set(models, props.index, true)" style="cursor: pointer">mdi-delete
                        </v-icon>
                        <b-detail-delete-dialog :detail="props.item" @delete="deleteDetail(props.item)"
                                                v-model="models[props.index]"
                        ></b-detail-delete-dialog>
                    </div>
                </td>
            </template>
            <template v-slot:pageText="props">
                {{ props.pageStart }} - {{ props.pageStop }} von {{ props.itemsLength }}
            </template>
        </v-data-table>
    </v-card>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import DetailDeleteDialog from "../../common/dialogs/DetailDeleteDialog.vue";
    import DetailEditDialog from "../../common/dialogs/DetailEditDialog.vue";
    import {Person, Unit} from "../../../models";
    import {removeObjectFromCache} from "../../common/identifiers/RemoveObjectFromCache";
    import DeleteDetailMutation from "../../common/identifiers/DeleteDetailMutation.graphql";
    import UpdateDetailMutation from "../../common/identifiers/UpdateDetailMutation.graphql";
    import DisplaysErrors from "../../../mixins/DisplaysErrors.vue";
    import DisplaysMessages from "../../../mixins/DisplaysMessages.vue";
    import {ErrorMessages} from "../../../mixins";
    import {ApolloQueryResult} from "apollo-client";

    @Component({
        components: {
            'b-detail-delete-dialog': DetailDeleteDialog,
            'b-detail-edit-dialog': DetailEditDialog
        },
        mixins: [
            DisplaysErrors,
            DisplaysMessages
        ]
    })
    export default class NotesCard extends Vue {
        @Prop({type: Array})
        details: any;

        @Prop({type: String})
        headline;

        @Prop({type: Object})
        parent: Person | Unit;

        models: boolean[] = [];
        edit: boolean[] = [];
        search: string = '';
        headers = [
            {text: 'Eintrag', value: 'value'},
            {text: 'Bemerkung', value: 'comment'},
            {text: '', value: '', sortable: false}
        ];

        showMessage: <R = any>(message: string) => Promise<ApolloQueryResult<R>>;

        errorMessages: ErrorMessages;

        clearErrorMessages: () => void;

        extractErrorMessages: (error: any) => void;

        loading: boolean = false;

        deleteDetail(detail) {
            this.$emit('delete', detail);
            this.clearErrorMessages();
            this.loading = true;
            this.$apollo.mutate({
                mutation: DeleteDetailMutation,
                variables: {
                    id: detail.id
                },
                update(cache) {
                    removeObjectFromCache(cache.data.data, detail.__typename + ':' + detail.id);
                    return cache.data.delete(detail.__typename + ':' + detail.id);
                }
            } as any).then(() => {
                this.loading = false;
                this.showMessage('Detail entfernt.');
            }).catch(error => {
                this.loading = false;
                this.extractErrorMessages(error);
                this.showMessage('Fehler beim Entfernen des Details. Nachricht: ' + error.message);
            })
        }

        saveDetail(detail, index) {
            if (detail && ['Note', 'Detail'].includes(detail.__typename)) {
                this.clearErrorMessages();
                this.loading = true;
                this.$apollo.mutate({
                    mutation: UpdateDetailMutation,
                    variables: {
                        input: {
                            id: detail.id,
                            value: detail.value,
                            comment: detail.comment
                        }
                    }
                }).then(() => {
                    this.loading = false;
                    this.edit[index] = false;
                    this.showMessage('Detail geändert.');
                }).catch(error => {
                    this.loading = false;
                    this.extractErrorMessages(error);
                    this.showMessage('Fehler beim Ändern des Details. Nachricht: ' + error.message);
                })
            }
        }
    }
</script>
