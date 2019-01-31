<template>
    <v-dialog :value="value" @input="$emit('input', $event)" lazy width="540">
        <v-card>
            <v-card-title class="headline">Anstellung hinzufügen</v-card-title>
            <v-card-text>
                <v-container grid-list-sm>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <b-entity-select :entities="['Partner']"
                                             :error-messages="errorMessages.for('input.employer.connect')"
                                             label="Arbeitgeber"
                                             prepend-icon="mdi-account-multiple"
                                             v-model="employer"
                            ></b-entity-select>
                        </v-flex>
                        <v-flex xs12>
                            <v-autocomplete :disabled="titleDisabled"
                                            :error-messages="errorMessages.for('input.title')"
                                            :items="titles"
                                            :loading="$apollo.queries.titles.loading"
                                            item-text="name"
                                            item-value="id"
                                            label="Anstellungsbezeichnung"
                                            prepend-icon="mdi-book-open-page-variant"
                                            return-object
                                            slot="input"
                                            v-model="title"
                            ></v-autocomplete>
                        </v-flex>
                        <v-flex sm6 xs12>
                            <v-text-field :error-messages="errorMessages.for('input.joinDate')"
                                          label="Eintritt"
                                          prepend-icon="mdi-calendar-today"
                                          type="date"
                                          v-model="joinDate"
                            ></v-text-field>
                        </v-flex>
                        <v-flex sm6 xs12>
                            <v-text-field :error-messages="errorMessages.for('input.leaveDate')"
                                          label="Austritt"
                                          prepend-icon="mdi-calendar-range"
                                          type="date"
                                          v-model="leaveDate"
                            ></v-text-field>
                        </v-flex>
                        <v-flex sm4 xs12>
                            <v-text-field :error-messages="errorMessages.for('input.hoursPerWeek')"
                                          label="Wochenstunden"
                                          min="0"
                                          prepend-icon="mdi-clock"
                                          step="0.5"
                                          type="number"
                                          v-model="hoursPerWeek"
                            ></v-text-field>
                        </v-flex>
                        <v-flex sm4 xs12>
                            <v-text-field :error-messages="errorMessages.for('input.holidays')"
                                          label="Urlaubstage"
                                          min="0"
                                          prepend-icon="mdi-white-balance-sunny"
                                          step="0.5"
                                          type="number"
                                          v-model="holidays"
                            ></v-text-field>
                        </v-flex>
                        <v-flex sm4 xs12>
                            <v-text-field :error-messages="errorMessages.for('input.hourlyRate')"
                                          label="Stundensatz"
                                          min="0"
                                          prepend-icon="mdi-currency-eur"
                                          step="0.01"
                                          type="number"
                                          v-model="hourlyRate"
                            ></v-text-field>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-progress-linear :active="loading"
                                           indeterminate
                        ></v-progress-linear>
                    </v-flex>
                    <v-flex class="text-xs-right" xs12>
                        <v-btn @click.native="$emit('input', false)"
                               flat="flat"
                        >Abbrechen
                        </v-btn>
                        <v-btn :disabled="!this.externalDependenciesFilled"
                               :loading="loading"
                               @click.native="addJob"
                               class="red"
                        >Hinzufügen
                        </v-btn>
                    </v-flex>
                </v-layout>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import EntitySelect from '../../../common/EntitySelect.vue';
    import {Partner, Person} from "../../../../models";
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../../mixins";
    import AvailableJobTitlesQuery from "./AvailableJobTitlesQuery.graphql";
    import CreateMutation from "./CreateMutation.graphql";
    import PersonJobsQuery from "./PersonJobsQuery.graphql";
    import {ApolloQueryResult} from "apollo-client";

    @Component({
        components: {
            'b-entity-select': EntitySelect
        },
        mixins: [DisplaysErrors, DisplaysMessages],
        apollo: {
            titles: {
                query: AvailableJobTitlesQuery,
                variables(this: CreateDialog) {
                    this.titles = [];
                    return {
                        employerId: this.employer ? this.employer.id : null
                    };
                },
                update(data) {
                    return data.partner.availableJobTitles;
                },
                fetchPolicy: 'cache-and-network',
                skip(this: CreateDialog) {
                    return !this.employer;
                }
            }
        }
    })
    export default class CreateDialog extends Vue implements DisplaysErrorsContract, DisplaysMessagesContract {

        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        employee: Person;

        employer: Partner | null = null;
        title: any | null = null;
        titles: Object[] = [];
        joinDate: Date | null = null;
        leaveDate: Date | null = null;
        hourlyRate: number | null = null;
        hoursPerWeek: number | null = null;
        holidays: number | null = null;

        loading: boolean = false;

        errorMessages: ErrorMessages;
        clearErrorMessages: () => void;
        extractErrorMessages: (error: any) => void;
        showMessage: <R = any>(message: string) => Promise<ApolloQueryResult<R>>;

        get titleDisabled() {
            return this.titles.length === 0;
        }

        addJob() {
            if (this.externalDependenciesFilled) {
                this.loading = true;
                this.clearErrorMessages();
                this.$apollo.mutate({
                    mutation: CreateMutation,
                    variables: {
                        input: {
                            employer: {connect: (this.employer as Partner).id},
                            employee: {connect: this.employee.id},
                            title: {connect: this.title.id},
                            joinDate: this.joinDate,
                            leaveDate: this.leaveDate,
                            hourlyRate: this.hourlyRate,
                            hoursPerWeek: this.hoursPerWeek,
                            holidays: this.holidays
                        }
                    },
                    update(store, {data: {createJob}}) {
                        if (createJob) {
                            const data = store.readQuery({
                                query: PersonJobsQuery,
                                variables: {
                                    id: createJob.employee.id
                                }
                            });

                            if (data.person) {
                                data.person.jobs.push(createJob);
                                store.writeQuery({query: PersonJobsQuery, data})
                            }
                            return createJob;
                        }
                    }
                } as any).then(() => {
                    this.loading = false;
                    this.$emit('input', false);
                    this.showMessage('Anstellung hinzugefügt.');
                }).catch((error) => {
                    this.loading = false;
                    this.extractErrorMessages(error);
                    this.showMessage('Fehler beim Hinzufügen der Anstellung. Nachricht: ' + error.message);
                });
            }
        }

        get externalDependenciesFilled() {
            return this.employee && this.employer && this.title;
        }
    }
</script>
