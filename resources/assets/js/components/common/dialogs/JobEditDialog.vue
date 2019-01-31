<template>
    <v-dialog v-model="show" @input="$emit('input', $event)" lazy width="540">
        <v-card>
            <v-card-title class="headline">Anstellung ändern</v-card-title>
            <v-card-text>
                <v-container grid-list-sm>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <div style="margin-left: 35px">Arbeitnehmer</div>
                            <app-identifier style="font-size: 24px" v-model="jobValue.employee"></app-identifier>
                        </v-flex>
                        <v-flex xs12>
                            <div style="margin-left: 35px">Arbeitgeber</div>
                            <app-identifier style="font-size: 24px" v-model="jobValue.employer"></app-identifier>
                        </v-flex>
                        <v-flex xs12>
                            <v-autocomplete v-model="jobValue.job_title_id"
                                            label="Titel"
                                            prepend-icon="mdi-book-open-page-variant"
                                            :items="titles"
                                            item-text="title"
                                            item-value="id"
                            ></v-autocomplete>
                        </v-flex>
                        <v-flex xs12 sm6>
                            <v-text-field v-model="jobValue.join_date"
                                          type="date"
                                          label="Eintritt"
                                          prepend-icon="mdi-calendar-today"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 sm6>
                            <v-text-field v-model="jobValue.leave_date"
                                          type="date"
                                          label="Austritt"
                                          prepend-icon="mdi-calendar-range"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 sm4>
                            <v-text-field v-model="jobValue.hours_per_week"
                                          type="number"
                                          label="Wochenstunden"
                                          prepend-icon="mdi-clock"
                                          min="0"
                                          step="0.5"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 sm4>
                            <v-text-field v-model="jobValue.holidays"
                                          type="number"
                                          label="Urlaubstage"
                                          prepend-icon="mdi-white-balance-sunny"
                                          min="0"
                                          step="0.5"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 sm4>
                            <v-text-field v-model="jobValue.hourly_rate"
                                          type="number"
                                          label="Stundensatz"
                                          prepend-icon="mdi-currency-eur"
                                          min="0"
                                          step="0.01"
                            ></v-text-field>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn flat="flat" @click.native="show = false; $emit('input', false)">Abbrechen</v-btn>
                <v-btn class="red" @click.native="editJob">Ändern</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import _ from "lodash";
    import EntitySelect from '../../common/EntitySelect.vue';
    import {Job} from "../../../server/resources";
    import axios from "../../../libraries/axios";
    import {namespace} from "vuex-class";

    const Snackbar = namespace('shared/snackbar');
    const Refresh = namespace('shared/refresh');

    @Component({
        components: {
            'app-entity-select': EntitySelect
        }
    })
    export default class JobEditDialog extends Vue {

        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        job: Job;

        @Snackbar.Mutation('updateMessage')
        updateMessage: Function;

        @Refresh.Mutation('requestRefresh')
        requestRefresh: Function;

        show: boolean = false;
        jobValue: Job = new Job();
        titles: Array<Object> = [];

        @Watch('value')
        onValueChange(val) {
            if (val) {
                this.loadCategories();
                this.jobValue = _.cloneDeep(this.job);
            }
        }

        loadCategories() {
            if (this.jobValue) {
                axios.get('/api/v1/partners/' + this.job.employer_id + '/available-job-titles').then((response) => {
                    this.titles = response.data;
                    this.show = true;
                });
            }
        }

        editJob() {
            if (this.jobValue) {
                axios.put('/api/v1/jobs/' + this.jobValue.id, this.jobValue).then(() => {
                    this.$emit('input', false);
                    this.show = false;
                    this.updateMessage('Anstellung geändert.');
                    this.requestRefresh();
                }).catch((error) => {
                    this.updateMessage('Fehler beim Ändern der Anstellung. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
                });
            }
        }
    }
</script>
