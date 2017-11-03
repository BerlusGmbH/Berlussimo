<template>
    <v-card>
        <v-card-title>
            <v-layout row wrap>
                <v-flex xs8 sm4>
                    <router-link v-if="filter"
                                 :to="{name: 'web.assignments.index', query: {q: filter}}"
                    >
                        <h3 class="headline">{{headline}} ({{assignments.length}})</h3>
                    </router-link>
                    <h3 v-else class="headline">{{headline}} ({{assignments.length}})</h3>
                </v-flex>
                <v-flex xs4 sm2 class="text-xs-right">
                    <v-btn @click.native="add = true">
                        <v-icon v-if="hasNotes" color="error">mdi-alert</v-icon>
                        <v-icon>add</v-icon>
                        <v-icon>mdi-clipboard</v-icon>
                    </v-btn>
                    <app-assignment-add-dialog v-model="add" :cost-unit="costUnit"></app-assignment-add-dialog>
                </v-flex>
                <v-flex xs12 sm6>
                    <v-text-field
                            append-icon="search"
                            label="Search"
                            single-line
                            hide-details
                            v-model="search"
                    ></v-text-field>
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text>
            <v-data-table
                    :headers="headers"
                    :items="assignments"
                    :search="search"
                    :hide-actions="assignments.length <= 5"
                    class="elevation-1"
            >
                <template slot="items" slot-scope="props">
                    <td style="white-space: nowrap">
                        <app-identifier :value="props.item"></app-identifier>
                    </td>
                    <td style="white-space: nowrap">{{props.item.ERSTELLT}}</td>
                    <td>
                        <app-identifier :value="props.item.von"></app-identifier>
                    </td>
                    <td>
                        <app-identifier :value="props.item.an"></app-identifier>
                    </td>
                    <td>{{props.item.TEXT}}</td>
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
    import {Haus, Model, Objekt, Einheit} from "../../../server/resources/models";
    import assignmentAddDialog from "../../../components/common/dialogs/AssignmentAddDialog.vue";

    @Component({
        'components': {
            'app-assignment-add-dialog': assignmentAddDialog
        }
    })
    export default class AssignmentsCard extends Vue {
        @Prop({type: Array})
        assignments: any;

        @Prop({type: String})
        headline: string;

        @Prop({type: String, default: ''})
        filter: string;

        @Prop({type: Object})
        costUnit: Model;

        search: string = '';
        headers = [
            {text: 'ID', value: 'T_ID'},
            {text: 'Erstellt', value: 'ERSTELLT'},
            {text: 'Von', value: 'von'},
            {text: 'An', value: 'an'},
            {text: 'Auftrag', value: 'TEXT'},
        ];
        add: boolean = false;

        get hasNotes() {
            if (!this.costUnit) {
                return false;
            }
            switch (this.costUnit.type) {
                case Objekt.type:
                    return (this.costUnit as Objekt).hasNotes();
                case Haus.type:
                    return (this.costUnit as Haus).hasNotes();
                case Einheit.type:
                    return (this.costUnit as Einheit).hasNotes();
            }
            return false;
        }
    }
</script>