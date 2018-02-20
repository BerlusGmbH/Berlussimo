<template>
    <v-card>
        <v-card-title>
            <h3 class="headline" style="align-self: center">{{headline}} ({{invoice.lines.length}})</h3>
            <v-spacer></v-spacer>
            <v-btn @click="lineAdd = true">
                <v-icon>mdi-cart-plus</v-icon>
            </v-btn>
            <v-btn-toggle v-model="toggleExpand"
                          multiple
            >
                <v-btn flat>
                    <v-icon>mdi-pencil</v-icon>
                </v-btn>
                <v-btn flat>
                    <v-icon>mdi-cards-variant</v-icon>
                </v-btn>
            </v-btn-toggle>
            <v-btn flat
                   style="margin-right: 8px; min-width: auto;"
                   @click="lineDels = true"
                   :disabled="selected.length === 0"
            >
                <v-icon>delete</v-icon>
            </v-btn>
            <v-text-field
                    append-icon="search"
                    label="Search"
                    single-line
                    hide-details
                    v-model="search"
            ></v-text-field>
        </v-card-title>
        <app-transition-collapse>
            <v-card-text v-if="toggleExpand.includes(0)">
                <app-invoice-line-edit-batch :lines="selected.map(v => v.RECHNUNGEN_POS_ID)"
                ></app-invoice-line-edit-batch>
            </v-card-text>
        </app-transition-collapse>
        <app-transition-collapse>
            <v-card-text v-if="toggleExpand.includes(1)">
                <app-invoice-line-assignment-edit-batch :lines="selected.map(v => v.RECHNUNGEN_POS_ID)"
                ></app-invoice-line-assignment-edit-batch>
            </v-card-text>
        </app-transition-collapse>
        <v-card-text>
            <v-data-table
                    :headers="headers"
                    :items="invoice.lines"
                    :search="search"
                    class="elevation-1"
                    :item-key="itemKey"
                    expand
                    :pagination.sync="pagination"
                    v-model="selected"
                    select-all
                    :rows-per-page-items="[50]"
            >
                <template slot="headerCell" slot-scope="props">
                    {{ props.header.text }}
                </template>
                <template slot="items" slot-scope="props">
                    <tr>
                        <td>
                            <v-checkbox v-model="props.selected"
                                        primary
                            ></v-checkbox>
                        </td>
                        <td class="text-xs-right">{{props.item.POSITION}}</td>
                        <td class="text-xs-right">{{props.item.ARTIKEL_NR}}</td>
                        <td>{{props.item.BEZEICHNUNG}}</td>
                        <td class="text-xs-right">{{props.item.PREIS | nformat('decimal-4')}}</td>
                        <td class="text-xs-right">{{props.item.MENGE | nformat}}&nbsp;{{props.item.EINHEIT}}</td>
                        <td class="text-xs-right">{{props.item.RABATT_SATZ | nformat}}</td>
                        <td class="text-xs-right">{{props.item.GESAMT_NETTO | nformat('decimal-4')}}</td>
                        <td class="text-xs-right">{{props.item.MWST_SATZ | nformat}}</td>
                        <td class="text-xs-right">{{props.item.SKONTO | nformat}}</td>
                        <td class="text-xs-right" style="padding-left: 4px; padding-right: 4px">
                            <v-btn class="mr-0" icon
                                   @click="onLineEdit(props.item)">
                                <v-icon>edit</v-icon>
                            </v-btn>
                            <v-btn class="ml-0" icon
                                   @click="onLineDelete(props.item)"
                            >
                                <v-icon>delete</v-icon>
                            </v-btn>
                        </td>
                        <td class="text-xs-right" style="padding-left: 4px; padding-right: 4px">
                            <v-btn icon class="mr-0"
                                   @click="onAssignmentAdd(props.item)"
                                   :disabled="isLineAmountEqualAssignmentsAmmount(props.item)"
                            >
                                <v-icon>add</v-icon>
                            </v-btn>
                            <v-btn icon
                                   class="ml-0"
                                   @click="toggleExpanded(props.item[itemKey])"
                                   :disabled="props.item.assignments && props.item.assignments.length === 0"
                            >
                                <v-icon>mdi-cards-variant</v-icon>
                            </v-btn>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="100%" class="pa-0" style="height: 0">
                            <app-transition-collapse>
                                <v-card flat
                                        style="border-bottom: 1px solid rgba(255,255,255,0.12)"
                                        v-if="isExpanded(props.item)"
                                >
                                    <table class="table" style="width: 90%; margin-left: auto">
                                        <thead>
                                        <tr class="text-xs-right">
                                            <th>Menge</th>
                                            <th>Kostenträger</th>
                                            <th>Buchungskonto</th>
                                            <th>Weiter verwenden</th>
                                            <th>Verwendungsjahr</th>
                                            <th>Bearbeiten</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="assignment in props.item.assignments" class="text-xs-right">
                                            <td>{{assignment.MENGE | nformat}}</td>
                                            <td>
                                                <app-identifier :value="assignment.cost_unit">
                                                </app-identifier>
                                            </td>
                                            <td>{{assignment.KONTENRAHMEN_KONTO}}
                                            </td>
                                            <td>
                                                <v-icon>
                                                    {{assignment.WEITER_VERWENDEN === '1'
                                                    ? 'mdi-checkbox-marked' : 'mdi-checkbox-blank-outline'
                                                    }}
                                                </v-icon>
                                            </td>
                                            <td>{{assignment.VERWENDUNGS_JAHR}}
                                            </td>
                                            <td style="padding-left: 4px; padding-right: 4px">
                                                <v-btn class="mr-0" icon
                                                       @click="onAssignmentEdit(assignment)">
                                                    <v-icon>edit</v-icon>
                                                </v-btn>
                                                <v-btn class="ml-0" icon
                                                       @click="onAssignmentDelete(assignment)">
                                                    <v-icon>delete</v-icon>
                                                </v-btn>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </v-card>
                            </app-transition-collapse>
                        </td>
                    </tr>
                </template>
            </v-data-table>
        </v-card-text>
        <app-invoice-line-add-dialog v-model="lineAdd" :invoice="invoice"></app-invoice-line-add-dialog>
        <app-invoice-line-edit-dialog v-model="lineEdit" :invoice="invoice" :line="line"></app-invoice-line-edit-dialog>
        <app-invoice-line-delete-dialog v-model="lineDel" :line="line"
                                        @deleted="onDeleted"></app-invoice-line-delete-dialog>
        <app-invoice-lines-delete-dialog v-model="lineDels" :lines="selected"></app-invoice-lines-delete-dialog>
        <app-invoice-line-assignment-add-dialog v-model="assignmentAddOrEdit"
                                                :line="line"
                                                :assignment="assignment"
        ></app-invoice-line-assignment-add-dialog>
        <app-invoice-line-assignment-delete-dialog v-model="assignmentDelete"
                                                   :assignment="assignment"
        ></app-invoice-line-assignment-delete-dialog>
    </v-card>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import {Mutation, namespace} from "vuex-class";
    import {
        Detail,
        Invoice,
        InvoiceLine,
        InvoiceLineAssignment,
        BankAccountStandardChart
    } from "../../../../server/resources";
    import InvoiceLineAddDialog from "../line/AddDialog.vue";
    import InvoiceLineEditDialog from "../line/EditDialog.vue";
    import InvoiceLineDeleteDialog from "../line/DeleteDialog.vue";
    import InvoiceLinesDeleteDialog from "../line/DeleteMultipleDialog.vue";
    import InvoiceLineAssignmentAddDialog from "../assignment/AddDialog.vue";
    import InvoiceLineAssignmentDeleteDialog from "../assignment/DeleteDialog.vue";
    import InvoiceLineAssignmentEditBatch from "../assignment/EditBatch.vue";
    import InvoiceLineEditBatch from "../line/EditBatch.vue";

    const SnackbarMutation = namespace('shared/snackbar', Mutation);
    const RefreshMutation = namespace('shared/refresh', Mutation);

    @Component({
        'components': {
            'app-invoice-line-add-dialog': InvoiceLineAddDialog,
            'app-invoice-line-edit-dialog': InvoiceLineEditDialog,
            'app-invoice-line-delete-dialog': InvoiceLineDeleteDialog,
            'app-invoice-lines-delete-dialog': InvoiceLinesDeleteDialog,
            'app-invoice-line-assignment-add-dialog': InvoiceLineAssignmentAddDialog,
            'app-invoice-line-assignment-delete-dialog': InvoiceLineAssignmentDeleteDialog,
            'app-invoice-line-assignment-edit-batch': InvoiceLineAssignmentEditBatch,
            'app-invoice-line-edit-batch': InvoiceLineEditBatch
        }
    })
    export default class InvoiceLinesCard extends Vue {
        @Prop({type: Object})
        invoice: Invoice;

        @Prop({type: String})
        headline;

        @SnackbarMutation('updateMessage')
        updateMessage: Function;

        @RefreshMutation('requestRefresh')
        requestRefresh: Function;

        models: Array<boolean> = [];
        search: string = '';
        headers = [
            {text: 'Position', value: 'POSITION', sortable: false},
            {text: 'Atrikelnummer', value: '', sortable: false},
            {text: 'Beschreibung', value: '', sortable: false},
            {text: 'Einzelpreis', value: '', sortable: false},
            {text: 'Menge', value: '', sortable: false},
            {text: 'Rabatt', value: '', sortable: false},
            {text: 'Preis', value: '', sortable: false},
            {text: 'MwSt.', value: '', sortable: false},
            {text: 'Skonto', value: '', sortable: false},
            {text: 'Bearbeiten', value: '', sortable: false},
            {text: 'Kontierung', value: '', sortable: false},
        ];
        toggleExpand: Array<number> = [];
        expanded = {};
        itemKey: string = 'RECHNUNGEN_POS_ID';
        selected: Array<InvoiceLine> = [];

        pagination: {
            sortBy: string,
            page: number,
            rowsPerPage: number,
            descending: boolean,
            totalItems: number
        } = {
            sortBy: 'POSITION',
            page: 1,
            rowsPerPage: 50,
            descending: false,
            totalItems: 0
        };

        lineAdd: boolean = false;
        lineEdit: boolean = false;
        lineDel: boolean = false;
        lineDels: boolean = false;
        assignmentAddOrEdit: boolean = false;
        assignmentDelete: boolean = false;
        line: InvoiceLine | null = null;
        assignment: InvoiceLineAssignment | null = null;
        standardChart: BankAccountStandardChart | null = null;
        date: string = '';

        @Watch('toggleExpand')
        onToggleExpandChange(val) {
            val = val.includes(1);
            this.invoice.lines.forEach((v) => {
                this.$set(this.expanded, v[this.itemKey], val);
            });
        }

        onLineEdit(line) {
            this.lineEdit = true;
            this.line = line;
        }

        onLineDelete(line) {
            this.lineDel = true;
            this.line = line;
        }

        onAssignmentAdd(line) {
            this.assignmentAddOrEdit = true;
            this.line = line;
            this.assignment = null;
        }

        onAssignmentEdit(assignment) {
            this.assignmentAddOrEdit = true;
            this.assignment = assignment;
            this.line = null;
        }

        onAssignmentDelete(assignment) {
            this.assignmentDelete = true;
            this.assignment = assignment;
        }

        onDeleted(line: InvoiceLine) {
            let index: number = this.selected.indexOf(line);
            if (index !== -1) {
                this.selected.splice(index, 1);
            }
        }

        isExpanded(v) {
            if (!this.hasAssignment(v)) {
                return false;
            }
            return this.expanded[v[this.itemKey]];
        }

        hasAssignment(v) {
            return v.assignments.length > 0;
        }

        toggleExpanded(key) {
            return this.$set(this.expanded, key, !this.expanded[key]);
        }

        get bookingAccountEntity() {
            return this.standardChart ? ['buchungskonto:' + this.standardChart.KONTENRAHMEN_ID] : ['buchungskonto'];
        }

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

        isLineAmountEqualAssignmentsAmmount(line: InvoiceLine) {
            if (line.assignments) {
                return line.MENGE <= line.assignments.reduce((carry, assignment) => {
                    return carry + Number(assignment.MENGE);
                }, 0);
            } else {
                return false;
            }

        }
    }
</script>