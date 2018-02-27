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
                <template slot="items" slot-scope="props">
                    <td>{{props.item.title.title}}</td>
                    <td>
                        <app-identifier style="width: 9em" v-model="props.item.employer"></app-identifier>
                    </td>
                    <td style="white-space: nowrap">{{props.item.join_date}}</td>
                    <td style="white-space: nowrap">{{props.item.leave_date}}</td>
                    <td>{{props.item.hours_per_week}}</td>
                    <td>{{props.item.holidays}}</td>
                    <td>{{props.item.hourly_rate}}</td>
                    <td class="text-xs-right">
                        <div style="display: flex">
                            <v-icon style="cursor: pointer" @click.stop="$set(models, props.index, true)">mdi-pencil
                            </v-icon>
                            <app-job-edit-dialog v-model="models[props.index]" :job="props.item"></app-job-edit-dialog>
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
    import jobEditDialog from "../../../components/common/dialogs/JobEditDialog.vue"

    @Component({
        'components': {
            'app-job-edit-dialog': jobEditDialog
        }
    })
    export default class JobsCard extends Vue {
        @Prop({type: Array})
        jobs: Array<any>;

        @Prop({type: String})
        headline: string;

        models: Array<Boolean> = [];

        search: string = '';

        headers = [
            {text: 'Titel', value: 'title'},
            {text: 'Arbeitgeber', value: 'partner'},
            {text: 'Eintritt', value: 'join_date'},
            {text: 'Austritt', value: 'leave_date'},
            {text: 'Wochenstunden', value: 'hours_per_week'},
            {text: 'Urlaubstage', value: 'holidays'},
            {text: 'Stundensatz', value: 'hourly_rate'},
            {text: '', value: '', sortable: false}
        ];
    }
</script>