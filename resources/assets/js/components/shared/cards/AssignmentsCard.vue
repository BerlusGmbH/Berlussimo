<template>
    <v-card>
        <v-card-title>
            <h3 class="headline">{{headline}} ({{assignments.length}})</h3>
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
                    :items="assignments"
                    :search="search"
                    :hide-actions="assignments.length <= 5"
                    class="elevation-1"
            >
                <template slot="items" scope="props">
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
                <template slot="pageText" scope="{ pageStart, pageStop }">
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

    @Component
    export default class AssignmentsCard extends Vue {
        @Prop({type: Array})
        assignments: any;

        @Prop({type: String})
        headline: string;

        search: string = '';
        headers = [
            {text: 'ID', value: ''},
            {text: 'Erstellt', value: ''},
            {text: 'Von', value: ''},
            {text: 'An', value: ''},
            {text: 'Auftrag', value: ''},
        ];
    }
</script>