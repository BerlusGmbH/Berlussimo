<template>
    <v-card>
        <v-card-title>
            <v-layout row wrap>
                <v-flex sm4 style="display:flex; align-items: center" xs8>
                    <template v-if="query">
                        <router-link
                            :to="{name: 'web.assignments.index', query: query}"
                        >
                            <h3 class="headline">{{headline}}</h3>
                        </router-link>
                        <v-chip color="primary">
                            <h3>{{assignments.length}}</h3>
                        </v-chip>
                    </template>
                    <template v-else>
                        <h3 class="headline">{{headline}}</h3>
                        <v-chip color="primary">
                            <h3>{{assignments.length}}</h3>
                        </v-chip>
                    </template>
                </v-flex>
                <v-flex xs4 sm2 class="text-xs-right">
                    <v-btn @click.native="add = true">
                        <v-icon v-if="hasNotes" color="error">mdi-alert</v-icon>
                        <v-icon>add</v-icon>
                        <v-icon>mdi-clipboard</v-icon>
                    </v-btn>
                    <app-assignment-add-dialog :cost-bearer="costBearer" @save="$emit('save')"
                                               v-model="add"></app-assignment-add-dialog>
                </v-flex>
                <v-flex xs12 sm6>
                    <v-text-field
                        append-icon="search"
                        hide-details
                        label="Search"
                        single-line
                        v-model="search"
                    ></v-text-field>
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-data-table
            :headers="headers"
            :hide-actions="assignments.length <= 5"
            :items="assignments"
            :pagination.sync="pagination"
            :search="search"
            class="elevation-1"
        >
            <template slot="items" slot-scope="props">
                <td style="white-space: nowrap">
                    <app-identifier :value="props.item"></app-identifier>
                </td>
                <td style="white-space: nowrap">{{props.item.createdAt}}</td>
                <td>{{props.item.description}}</td>
                <td>
                    <app-identifier :value="props.item.author"></app-identifier>
                </td>
                <td>
                    <app-identifier :value="props.item.assignedTo"></app-identifier>
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
    import {House, ModelBase, Property, Unit} from "../../../models";
    import CreateAssignmentDialog from "../../modules/assignment/dialogs/CreateDialog.vue";

    @Component({
        'components': {
            'app-assignment-add-dialog': CreateAssignmentDialog
        }
    })
    export default class AssignmentsCard extends Vue {
        @Prop({type: Array})
        assignments: any;

        @Prop({type: String})
        headline: string;

        @Prop({type: Object, default: () => {}})
        query: string;

        @Prop({type: Object})
        costBearer: ModelBase;

        pagination: {
            sortBy: string,
            descending: boolean
        } = {
            sortBy: 'createdAt',
            descending: true
        };

        search: string = '';
        headers = [
            {text: 'ID', value: 'id'},
            {text: 'Erstellt', value: 'createdAt'},
            {text: 'Beschreibung', value: 'description'},
            {text: 'Von', value: 'author'},
            {text: 'An', value: 'assignedTo'},
        ];
        add: boolean = false;

        get hasNotes() {
            if (!this.costBearer) {
                return false;
            }
            switch (this.costBearer.__typename) {
                case Property.__typename:
                    return (this.costBearer as Property).hasNotes();
                case House.__typename:
                    return (this.costBearer as House).hasNotes();
                case Unit.__typename:
                    return (this.costBearer as Unit).hasNotes();
            }
            return false;
        }
    }
</script>
