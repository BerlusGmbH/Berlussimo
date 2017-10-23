<template>
    <v-card>
        <v-card-title>
            <v-layout row wrap>
                <v-flex xs6>
                    <a v-if="href" :href="href"><h3 class="headline">{{headline}} ({{assignments.length}})</h3></a>
                    <h3 v-else class="headline">{{headline}} ({{assignments.length}})</h3>
                </v-flex>
                <v-flex xs6>
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

    @Component
    export default class AssignmentsCard extends Vue {
        @Prop({type: Array})
        assignments: any;

        @Prop({type: String})
        headline: string;

        @Prop({type: String, default: ''})
        href: string;

        search: string = '';
        headers = [
            {text: 'ID', value: 'T_ID'},
            {text: 'Erstellt', value: 'ERSTELLT'},
            {text: 'Von', value: 'von'},
            {text: 'An', value: 'an'},
            {text: 'Auftrag', value: 'TEXT'},
        ];
    }
</script>