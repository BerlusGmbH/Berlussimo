<template>
    <v-dialog :value="value" @input="$emit('input', $event)" lazy width="540">
        <v-card>
            <v-card-title class="headline">Anstellung hinzufügen</v-card-title>
            <v-card-text>
                <v-container grid-list-sm>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <app-entity-select v-model="employer"
                                               label="Arbeitgeber"
                                               prepend-icon="mdi-account-multiple"
                                               :entities="['partner']"
                            ></app-entity-select>
                        </v-flex>
                        <v-flex xs12>
                            <v-autocomplete v-model="title"
                                            label="Titel"
                                            prepend-icon="mdi-book-open-page-variant"
                                            :items="titles"
                                            slot="input"
                                            item-text="title"
                                            item-value="id"
                                            return-object
                                            :disabled="titleDisabled"
                            ></v-autocomplete>
                        </v-flex>
                        <v-flex xs12 sm6>
                            <v-text-field v-model="join_date"
                                          type="date"
                                          label="Eintritt"
                                          prepend-icon="mdi-calendar-today"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 sm6>
                            <v-text-field v-model="leave_date"
                                          type="date"
                                          label="Austritt"
                                          prepend-icon="mdi-calendar-range"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 sm4>
                            <v-text-field v-model="hours_per_week"
                                          type="number"
                                          label="Wochenstunden"
                                          prepend-icon="mdi-clock"
                                          min="0"
                                          step="0.5"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 sm4>
                            <v-text-field v-model="holidays"
                                          type="number"
                                          label="Urlaubstage"
                                          prepend-icon="mdi-white-balance-sunny"
                                          min="0"
                                          step="0.5"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12 sm4>
                            <v-text-field v-model="hourly_rate"
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
                <v-btn flat="flat" @click.native="$emit('input', false)">Abbrechen</v-btn>
                <v-btn class="red" @click.native="addJob">Hinzufügen</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import EntitySelect from '../../common/EntitySelect.vue';
    import {Partner, Person} from "../../../server/resources";
    import axios from "../../../libraries/axios";
    import {namespace} from "vuex-class";

    const Snackbar = namespace('shared/snackbar');
    const Refresh = namespace('shared/refresh');

    @Component({
        components: {
            'app-entity-select': EntitySelect
        }
    })
    export default class JobAddDialog extends Vue {

        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        employee: Person;

        @Snackbar.Mutation('updateMessage')
        updateMessage: Function;

        @Refresh.Mutation('requestRefresh')
        requestRefresh: Function;

        employer: Partner | null = null;
        title: Object | null = null;
        titles: Array<Object> = [];
        join_date: Date | null = null;
        leave_date: Date | null = null;
        hourly_rate: Number | null = null;
        hours_per_week: Number | null = null;
        holidays: Number | null = null;

        get titleDisabled() {
            return !this.employer;
        }

        @Watch('employer')
        onEmployerChange(val) {
            if (val) {
                this.loadCategories();
            }
        }

        loadCategories() {
            if (this.employer) {
                axios.get('/api/v1/partners/' + this.employer.PARTNER_ID + '/available-job-titles').then((response) => {
                    this.titles = response.data;
                });
            }
        }

        addJob() {
            if (this.employee && this.employer && this.title) {
                axios.post('/api/v1/jobs', {
                    'employer_id': this.employer.PARTNER_ID,
                    'employee_id': this.employee.id,
                    'job_title_id': this.title['id'],
                    'join_date': this.join_date,
                    'leave_date': this.leave_date,
                    'hourly_rate': this.hourly_rate,
                    'hours_per_week': this.hours_per_week,
                    'holidays': this.holidays
                }).then(() => {
                    this.$emit('input', false);
                    this.updateMessage('Anstellung hinzugefügt.');
                    this.requestRefresh();
                }).catch((error) => {
                    this.updateMessage('Fehler beim Hinzufügen der Anstellung. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
                });
            }
        }
    }
</script>
