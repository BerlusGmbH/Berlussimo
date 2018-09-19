<template>
    <v-card>
        <v-card-title>
            <h3 class="headline">{{headline}} ({{details.length}})</h3>
            <v-spacer></v-spacer>
            <v-text-field
                    append-icon="search"
                    label="Search"
                    single-line
                    hide-details
                    v-model="search"
            ></v-text-field>
        </v-card-title>
        <v-card-text>
            <v-data-table
                    :headers="headers"
                    :items="details"
                    :search="search"
                    :hide-actions="details.length <= 5"
                    class="elevation-1"
            >
                <template slot="items" slot-scope="props">
                    <td>{{props.item.DETAIL_INHALT}}</td>
                    <td>{{props.item.DETAIL_BEMERKUNG}}</td>
                    <td class="text-xs-right">
                        <div style="display: flex">
                            <app-detail-edit-dialog :value="props.item"
                                                    @input="$emit('input', $event); saveDetail($event)"
                                                    prepend-icon="mdi-table"
                                                    :parent="parent"
                                                    large
                            >
                                <v-icon style="cursor: pointer">mdi-pencil</v-icon>
                            </app-detail-edit-dialog>
                            <v-icon style="cursor: pointer" @click.stop="$set(models, props.index, true)">mdi-delete
                            </v-icon>
                            <app-detail-delete-dialog v-model="models[props.index]" @delete="deleteDetail(props.item)"
                                                      :detail="props.item"
                            ></app-detail-delete-dialog>
                        </div>
                    </td>
                </template>
                <template slot="pageText" slot-scope="{ pageStart, pageStop }">
                    Von {{ pageStart }} bis {{ pageStop }}
                </template>
            </v-data-table>
        </v-card-text>
    </v-card>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import detailDeleteDialog from "../../common/dialogs/DetailDeleteDialog.vue";
    import detailEditDialog from "../../common/dialogs/DetailEditDialog.vue";
    import {namespace} from "vuex-class";
    import {Detail, Einheit, Person} from "../../../server/resources";

    const SnackbarModule = namespace('shared/snackbar');
    const RefreshModule = namespace('shared/refresh');

    @Component({
        'components': {
            'app-detail-delete-dialog': detailDeleteDialog,
            'app-detail-edit-dialog': detailEditDialog
        }
    })
    export default class NotesCard extends Vue {
        @Prop({type: Array})
        details: any;

        @Prop({type: String})
        headline;

        @Prop({type: Object})
        parent: Person | Einheit;

        @SnackbarModule.Mutation('updateMessage')
        updateMessage: Function;

        @RefreshModule.Mutation('requestRefresh')
        requestRefresh: Function;

        models: Array<boolean> = [];
        search: string = '';
        headers = [
            {text: 'Eintrag', value: 'DETAIL_INHALT'},
            {text: 'Bemerkung', value: 'DETAIL_BEMERKUNG'},
            {text: '', value: '', sortable: false}
        ];

        deleteDetail(detail) {
            this.$emit('delete', detail);
            detail.delete().then(() => {
                this.updateMessage('Detail entfernt.');
                this.requestRefresh();
            }).catch((error) => {
                this.updateMessage('Fehler beim Entfernen des Details. Code: ' + error.response.status + ' Message: ' + error.response.data);
            });
        }

        saveDetail(detail) {
            if (detail instanceof Detail) {
                detail.save().then(() => {
                    this.updateMessage('Detail geändert.');
                    this.requestRefresh();
                }).catch((error) => {
                    this.updateMessage('Fehler beim Ändern des Details. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
                });
            }
        }
    }
</script>