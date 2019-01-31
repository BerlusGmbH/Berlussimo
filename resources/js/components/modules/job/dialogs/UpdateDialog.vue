<template>
    <v-dialog :loading="loading"
              :value="value"
              @input="$emit('input', $event)"
              lazy
              width="540"
    >
        <v-card>
            <v-card-title class="headline">Anstellung ändern</v-card-title>
            <v-container grid-list-sm>
                <v-layout row wrap>
                    <v-flex xs12>
                        <div style="margin-left: 35px">Arbeitnehmer</div>
                        <app-identifier style="font-size: 24px" v-if="job.employee"
                                        v-model="job.employee"></app-identifier>
                    </v-flex>
                    <v-flex xs12>
                        <div style="margin-left: 35px">Arbeitgeber</div>
                        <app-identifier style="font-size: 24px" v-if="job.employer"
                                        v-model="job.employer"></app-identifier>
                    </v-flex>
                    <v-flex xs12>
                        <v-autocomplete :disabled="loading"
                                        :error-messages="errorMessages.for('input.title.connect')"
                                        :items="titles"
                                        item-text="name"
                                        item-value="id"
                                        label="Anstellungsbezeichnung"
                                        prepend-icon="mdi-book-open-page-variant"
                                        return-object
                                        v-model="job.title"
                        ></v-autocomplete>
                    </v-flex>
                    <v-flex sm6 xs12>
                        <v-text-field :disabled="loading"
                                      :error-messages="errorMessages.for('input.joinDate')"
                                      label="Eintritt"
                                      prepend-icon="mdi-calendar-today"
                                      type="date"
                                      v-model="job.joinDate"
                        ></v-text-field>
                    </v-flex>
                    <v-flex sm6 xs12>
                        <v-text-field :disabled="loading"
                                      :error-messages="errorMessages.for('input.leaveDate')"
                                      label="Austritt"
                                      prepend-icon="mdi-calendar-range"
                                      type="date"
                                      v-model="job.leaveDate"
                        ></v-text-field>
                    </v-flex>
                    <v-flex sm4 xs12>
                        <v-text-field :disabled="loading"
                                      :error-messages="errorMessages.for('input.hoursPerWeek')"
                                      label="Wochenstunden"
                                      min="0"
                                      prepend-icon="mdi-clock"
                                      step="0.5"
                                      type="number"
                                      v-model="job.hoursPerWeek"
                        ></v-text-field>
                    </v-flex>
                    <v-flex sm4 xs12>
                        <v-text-field :disabled="loading"
                                      :error-messages="errorMessages.for('input.holidays')"
                                      label="Urlaubstage"
                                      min="0"
                                      prepend-icon="mdi-white-balance-sunny"
                                      step="0.5"
                                      type="number"
                                      v-model="job.holidays"
                        ></v-text-field>
                    </v-flex>
                    <v-flex sm4 xs12>
                        <v-text-field :disabled="loading"
                                      :error-messages="errorMessages.for('input.hourlyRate')"
                                      label="Stundensatz"
                                      min="0"
                                      prepend-icon="mdi-currency-eur"
                                      step="0.01"
                                      type="number"
                                      v-model="job.hourlyRate"
                        ></v-text-field>
                    </v-flex>
                </v-layout>
            </v-container>
            <div class="ml-4 mr-4">
                <v-progress-linear :active="loading"
                                   indeterminate
                ></v-progress-linear>
            </div>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn @click.native="show = false; $emit('input', false)"
                       flat="flat"
                >Abbrechen
                </v-btn>
                <v-btn :disabled="loading"
                       @click.native="editJob"
                       class="red"
                >Ändern
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import {Job, Model} from "../../../../models";
    import UpdateQuery from "./UpdateQuery.graphql";
    import UpdateMutation from "./UpdateMutation.graphql";
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../../mixins";
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import {ApolloQueryResult} from "apollo-client";

    @Component({
        mixins: [DisplaysErrors, DisplaysMessages]
    })
    export default class UpdateDialog extends Vue implements DisplaysErrorsContract, DisplaysMessagesContract {

        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: String})
        jobId: string;

        show: boolean = false;
        job: Job = new Job();
        titles: Object[] = [];

        loading: boolean = false;

        errorMessages: ErrorMessages;
        clearErrorMessages: () => void;
        extractErrorMessages: (error: any) => void;
        showMessage: <R = any>(message: string) => Promise<ApolloQueryResult<R>>;

        @Watch('value')
        onValueChange(val) {
            if (val) {
                this.loading = true;
                this.$apollo.query({
                    query: UpdateQuery,
                    variables: {
                        id: this.jobId
                    }
                }).then(result => {
                    this.loading = false;
                    if (result.data.job) {
                        this.job = Model.applyPrototype(result.data.job);
                        this.titles = this.job.employer.availableJobTitles;
                    }
                }).catch(() => {
                    this.loading = false;
                });
            }
        }

        editJob() {
            if (this.job) {
                this.loading = true;
                this.$apollo.mutate({
                    mutation: UpdateMutation,
                    variables: {
                        input: {
                            id: this.job.id,
                            employer: {connect: this.job.employer.id},
                            employee: {connect: this.job.employee.id},
                            title: {connect: this.job.title.id},
                            joinDate: this.job.joinDate,
                            leaveDate: this.job.leaveDate,
                            hourlyRate: this.job.hourlyRate,
                            hoursPerWeek: this.job.hoursPerWeek,
                            holidays: this.job.holidays
                        }
                    }
                }).then(() => {
                    this.loading = false;
                    this.$emit('input', false);
                    this.showMessage('Anstellung geändert.');
                }).catch(error => {
                    this.loading = false;
                    this.extractErrorMessages(error);
                    this.showMessage('Fehler beim Ändern der Anstellung. Nachricht: ' + error.message);
                })
            }
        }
    }
</script>
