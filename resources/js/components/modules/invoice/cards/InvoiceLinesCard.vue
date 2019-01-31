<template>
    <v-card>
        <v-card-title>
            <h3 class="headline" style="align-self: center">
                {{headline}}
                <v-chip color="primary">
                    {{lines.length}}
                </v-chip>
            </h3>
            <v-spacer></v-spacer>
            <v-btn @click="lineAdd = true">
                <v-icon>mdi-cart-arrow-down</v-icon>
            </v-btn>
            <v-btn-toggle multiple
                          v-model="toggleExpand"
            >
                <v-btn flat>
                    <v-icon>mdi-pencil</v-icon>
                </v-btn>
                <v-btn flat>
                    <v-icon>mdi-cards-variant</v-icon>
                </v-btn>
            </v-btn-toggle>
            <v-btn :disabled="selected.length === 0"
                   @click="lineDels = true"
                   flat
                   style="margin-right: 8px; min-width: auto;"
            >
                <v-icon>mdi-cart-remove</v-icon>
            </v-btn>
            <v-text-field
                append-icon="search"
                hide-details
                label="Search"
                single-line
                v-model="search"
            ></v-text-field>
        </v-card-title>
        <app-transition-collapse>
            <v-card-text v-if="toggleExpand.includes(0)">
                <b-invoice-line-update-batch :lines="selected.map(v => v.id)"
                ></b-invoice-line-update-batch>
            </v-card-text>
        </app-transition-collapse>
        <app-transition-collapse>
            <v-card-text v-if="toggleExpand.includes(1)">
                <b-invoice-line-assignment-update-batch :lines="selected.map(v => v.id)"
                                                        @updated="refetch"
                ></b-invoice-line-assignment-update-batch>
            </v-card-text>
        </app-transition-collapse>
        <v-data-table
            :headers="headers"
            :item-key="itemKey"
            :items="lines"
            :pagination.sync="pagination"
            :rows-per-page-items="[25,50,100]"
            :search="search"
            expand
            select-all
            v-model="selected"
        >
            <template v-slot:items="props">
                <tr>
                    <td>
                        <v-checkbox hide-details
                                    primary
                                    v-model="props.selected"
                        ></v-checkbox>
                    </td>
                    <td class="text-xs-right">{{props.item.position}}</td>
                    <td class="text-xs-right">{{props.item.itemNumber}}</td>
                    <td>{{props.item.description}}</td>
                    <td class="text-xs-right">{{props.item.quantity | nformat}}&nbsp;{{props.item.quantityUnit}}
                    </td>
                    <td class="text-xs-right">{{props.item.price | nformat('decimal-4')}}</td>
                    <td class="text-xs-right">{{props.item.rebate | nformat}}</td>
                    <td class="text-xs-right">{{props.item.netAmount | nformat('decimal-4')}}</td>
                    <td class="text-xs-right">{{props.item.VAT | nformat}}</td>
                    <td class="text-xs-right">{{props.item.discount | nformat}}</td>
                    <td class="text-xs-right" style="padding-left: 4px; padding-right: 4px">
                        <v-btn @click="onLineEdit(props.item)" class="mr-0"
                               icon>
                            <v-icon>edit</v-icon>
                        </v-btn>
                        <v-btn @click="onLineDelete(props.item)" class="ml-0"
                               icon
                        >
                            <v-icon>mdi-cart-remove</v-icon>
                        </v-btn>
                    </td>
                    <td class="text-xs-right" style="padding-left: 4px; padding-right: 4px">
                        <v-btn :disabled="isLineAmountEqualAssignmentsAmount(props.item)"
                               @click="onAssignmentAdd(props.item)"
                               class="mr-0"
                               icon
                        >
                            <v-icon>add</v-icon>
                        </v-btn>
                        <v-btn :disabled="props.item.assignments && props.item.assignments.length === 0"
                               @click="toggleExpanded(props.item[itemKey])"
                               class="ml-0"
                               icon
                        >
                            <v-icon>mdi-cards-variant</v-icon>
                        </v-btn>
                    </td>
                </tr>
                <tr>
                    <td class="pa-0" colspan="100%" style="height: 0">
                        <app-transition-collapse>
                            <v-card flat
                                    v-if="isExpanded(props.item)"
                            >
                                <table class="table" style="width: 90%; margin-left: auto; border-spacing: 0">
                                    <thead>
                                    <tr class="text-xs-right v-datatable__expand-row"
                                        style="height: unset; border-bottom-width: 1px !important; border-bottom-style: solid !important; !important; border-bottom-color: rgba(255, 255, 255, 0.12) !important; border-collapse: collapse"
                                    >
                                        <th>Menge</th>
                                        <th>Kostenträger</th>
                                        <th>Buchungskonto</th>
                                        <th>Weiter verwenden</th>
                                        <th>Verwendungsjahr</th>
                                        <th>Bearbeiten</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="text-xs-right" v-for="assignment in props.item.assignments">
                                        <td>{{assignment.quantity | nformat}}</td>
                                        <td>
                                            <app-identifier :value="assignment.costBearer">
                                            </app-identifier>
                                        </td>
                                        <td>{{assignment.bookingAccountNumber}}
                                        </td>
                                        <td>
                                            <v-icon>
                                                {{assignment.reassign
                                                ? 'mdi-checkbox-marked' : 'mdi-checkbox-blank-outline'
                                                }}
                                            </v-icon>
                                        </td>
                                        <td>{{assignment.yearOfReassignment}}
                                        </td>
                                        <td style="padding-left: 4px; padding-right: 4px">
                                            <v-btn @click="onAssignmentEdit(assignment)" class="mr-0"
                                                   icon>
                                                <v-icon>edit</v-icon>
                                            </v-btn>
                                            <v-btn @click="onAssignmentDelete(assignment)" class="ml-0"
                                                   icon>
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
        <b-invoice-line-create-dialog :invoice="invoice"
                                      v-model="lineAdd"
        ></b-invoice-line-create-dialog>
        <b-invoice-line-update-dialog :invoice="invoice"
                                      :line="line"
                                      v-model="lineEdit"
        ></b-invoice-line-update-dialog>
        <b-invoice-line-delete-dialog :line="line"
                                      @deleted="refetch"
                                      v-model="lineDel"
        ></b-invoice-line-delete-dialog>
        <b-invoice-lines-delete-dialog :lines="selected"
                                       @deleted="refetch"
                                       v-model="lineDels"
        ></b-invoice-lines-delete-dialog>
        <b-invoice-line-assignment-create-dialog :assignment="assignment"
                                                 :line="line"
                                                 @created="refetch"
                                                 v-model="assignmentAddOrEdit"
        ></b-invoice-line-assignment-create-dialog>
        <b-invoice-line-assignment-delete-dialog :assignment="assignment"
                                                 @deleted="refetch"
                                                 v-model="assignmentDelete"
        ></b-invoice-line-assignment-delete-dialog>
    </v-card>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import {BankAccountStandardChart, Invoice, InvoiceLine, InvoiceLineAssignment, Model} from "../../../../models";
    import InvoiceLineCreateDialog from "../line/CreateDialog.vue";
    import InvoiceLineEditDialog from "../line/UpdateDialog.vue";
    import InvoiceLineDeleteDialog from "../line/DeleteDialog.vue";
    import InvoiceLinesDeleteDialog from "../line/DeleteBatchDialog.vue";
    import InvoiceLineAssignmentCreateDialog from "../assignment/CreateDialog.vue";
    import InvoiceLineAssignmentDeleteDialog from "../assignment/DeleteDialog.vue";
    import InvoiceLineAssignmentUpdateBatch from "../assignment/UpdateBatch.vue";
    import InvoiceLineUpdateBatch from "../line/UpdateBatch.vue";
    import InvoiceLinesQuery from "./InvoiceLinesQuery.graphql"

    @Component({
        'components': {
            'b-invoice-line-create-dialog': InvoiceLineCreateDialog,
            'b-invoice-line-update-dialog': InvoiceLineEditDialog,
            'b-invoice-line-delete-dialog': InvoiceLineDeleteDialog,
            'b-invoice-lines-delete-dialog': InvoiceLinesDeleteDialog,
            'b-invoice-line-assignment-create-dialog': InvoiceLineAssignmentCreateDialog,
            'b-invoice-line-assignment-delete-dialog': InvoiceLineAssignmentDeleteDialog,
            'b-invoice-line-assignment-update-batch': InvoiceLineAssignmentUpdateBatch,
            'b-invoice-line-update-batch': InvoiceLineUpdateBatch
        },
        apollo: {
            lines: {
                query: InvoiceLinesQuery,
                skip(this: InvoiceLinesCard) {
                    return !(this.invoice && this.invoice.id)
                },
                variables() {
                    return {
                        id: this.invoice.id
                    }
                },
                update(data) {
                    return Model.applyPrototype(data.invoice.lines);
                }
            }
        }
    })
    export default class InvoiceLinesCard extends Vue {
        @Prop({type: Object})
        invoice: Invoice;

        @Prop({type: String})
        headline;

        models: boolean[] = [];
        search: string = '';
        headers = [
            {text: 'Position', value: 'position', sortable: false},
            {text: 'Atrikelnummer', value: 'itemNumber', sortable: false},
            {text: 'Beschreibung', value: 'description', sortable: false},
            {text: 'Menge', value: 'quantity', sortable: false},
            {text: 'Einkaufspreis', value: 'price', sortable: false},
            {text: 'Rabatt', value: 'rebate', sortable: false},
            {text: 'Preis', value: 'netAmount', sortable: false},
            {text: 'MwSt.', value: 'VAT', sortable: false},
            {text: 'Skonto', value: 'discount', sortable: false},
            {text: 'Bearbeiten', value: '', sortable: false},
            {text: 'Kontierung', value: '', sortable: false},
        ];
        toggleExpand: number[] = [];
        expanded = {};
        itemKey: string = 'id';
        selected: InvoiceLine[] = [];

        pagination: {
            sortBy: string,
            page: number,
            rowsPerPage: number,
            descending: boolean,
            totalItems: number
        } = {
            sortBy: 'position',
            page: 1,
            rowsPerPage: 100,
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
        lines: InvoiceLine[] = [];
        assignment: InvoiceLineAssignment | null = null;
        standardChart: BankAccountStandardChart | null = null;
        date: string = '';

        @Watch('toggleExpand')
        onToggleExpandChange(val) {
            val = val.includes(1);
            this.lines.forEach((v) => {
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

        refetch() {
            this.$apollo.queries.lines.refetch();
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

        isLineAmountEqualAssignmentsAmount(line: InvoiceLine) {
            if (line.assignments) {
                return line.quantity <= line.assignments.reduce((carry, assignment) => {
                    return carry + Number(assignment.quantity);
                }, 0);
            } else {
                return false;
            }

        }
    }
</script>
