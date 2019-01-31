<template>
    <v-card>
        <v-card-title>
            <h3 class="headline">{{headline}}</h3>
            <v-chip color="primary">
                <h3>{{jobs.length}}</h3>
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
            :hide-actions="jobs.length <= 5"
            :items="jobs"
            :search="search"
            disable-initial-sort
        >
            <template slot="items" slot-scope="props">
                <td>{{props.item.title.name}}</td>
                <td>
                    <app-identifier style="width: 9em" v-model="props.item.employer"></app-identifier>
                </td>
                <td style="white-space: nowrap">{{props.item.joinDate}}</td>
                <td style="white-space: nowrap">{{props.item.leaveDate}}</td>
                <td>{{props.item.hoursPerWeek}}</td>
                <td>{{props.item.holidays}}</td>
                <td>{{props.item.hourlyRate}}</td>
                <td class="text-xs-right">
                    <div style="display: flex">
                        <v-icon @click.stop="$set(models, props.index, true)" style="cursor: pointer">mdi-pencil
                        </v-icon>
                        <b-update-job-dialog :jobId="props.item.id"
                                             v-model="models[props.index]"></b-update-job-dialog>
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
    import UpdateJobDialog from "../../modules/job/dialogs/UpdateDialog.vue"

    @Component({
        'components': {
            'b-update-job-dialog': UpdateJobDialog
        }
    })
    export default class JobsCard extends Vue {
        @Prop({type: Array})
        jobs: any[];

        @Prop({type: String})
        headline: string;

        models: boolean[] = [];

        search: string = '';

        headers = [
            {text: 'Titel', value: 'name'},
            {text: 'Arbeitgeber', value: 'partner'},
            {text: 'Eintritt', value: 'joinDate'},
            {text: 'Austritt', value: 'leaveDate'},
            {text: 'Wochenstunden', value: 'hoursPerWeek'},
            {text: 'Urlaubstage', value: 'holidays'},
            {text: 'Stundensatz', value: 'hourlyRate'},
            {text: '', value: '', sortable: false}
        ];
    }
</script>
