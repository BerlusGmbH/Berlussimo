<template>
    <v-card>
        <v-card-title>
            <h3 class="headline">{{headline}} ({{jobs.length}})</h3>
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
                    :items="jobs"
                    :search="search"
                    :hide-actions="jobs.length <= 5"
                    class="elevation-1"
            >
                <template slot="headerCell" scope="props">
                    <span class="primary--text">{{props.header.text}}</span>
                </template>
                <template slot="items" scope="props">
                    <td>{{props.item.title.title}}</td>
                    <td>
                        <app-identifier style="width: 10em" :entity="props.item.employer"></app-identifier>
                    </td>
                    <td>
                        <v-edit-dialog
                                @open="props.item._join_date = props.item.join_date"
                                @cancel="props.item.join_date = props.item.join_date || props.item._join_date"
                                lazy
                        >
                            <span class="white--text" style="width: 5.3em">{{ props.item.join_date }}</span>
                            <v-text-field
                                    light
                                    slot="input"
                                    type="date"
                                    v-bind:value="props.item.join_date"
                                    @change.native="event => props.item.join_date = event.target.value"
                                    single-line
                                    hide-details
                            ></v-text-field>
                        </v-edit-dialog>
                    </td>
                    <td>
                        <v-edit-dialog lazy>
                            <span class="white--text" style="width: 5.3em">{{props.item.leave_date}}</span>
                            <v-text-field
                                    light
                                    slot="input"
                                    type="date"
                                    v-bind:value="props.item.leave_date"
                                    @change.native="event => props.item.leave_date = event.target.value"
                                    single-line
                                    hide-details
                            ></v-text-field>
                        </v-edit-dialog>
                    </td>
                    <td>{{props.item.hours_per_week}}</td>
                    <td>{{props.item.holidays}}</td>
                    <td>{{props.item.hourly_rate}}</td>
                </template>
                <template slot="pageText" scope="{ pageStart, pageStop }">
                    From {{ pageStart }} to {{ pageStop }}
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
    export default class JobsCard extends Vue {
        @Prop({type: Array})
        jobs: any;

        @Prop({type: String})
        headline: string;

        search: string = '';
        headers = [
            {text: 'Titel', value: 'title'},
            {text: 'Arbeitgeber', value: 'partner'},
            {text: 'Eintritt', value: 'join_date'},
            {text: 'Austritt', value: 'leave_date'},
            {text: 'Wochenstunden', value: 'hours_per_week'},
            {text: 'Urlaubstage', value: 'holidays'},
            {text: 'Stundensatz', value: 'hourly_rate'}
        ];
    }
</script>