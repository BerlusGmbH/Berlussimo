<template>
    <v-layout row wrap>
        <v-flex xs12 md6 lg3>
            <app-entity-select label="Kostenträger"
                               @input="selectCostUnit"
                               clearable
                               :entities="[
                                                   'objekt',
                                                   'haus',
                                                   'einheit',
                                                   'partner',
                                                   'person',
                                                   'mietvertrag',
                                                   'kaufvertrag',
                                                   'baustelle',
                                                   'wirtschaftseinheit'
                                               ]"
            >
            </app-entity-select>
        </v-flex>
        <v-flex xs12 md6 lg2>
            <app-entity-select label="Kontenrahmen"
                               :entities="['kontenrahmen']"
                               v-model="standardChart"
                               prepend-icon="mdi-table"
                               clearable
            >
            </app-entity-select>
        </v-flex>
        <v-flex xs12 md6 lg2>
            <app-entity-select label="Buchungskonto"
                               :entities="bookingAccountEntity"
                               prepend-icon="mdi-numeric"
                               @input="selectBookingAccount"
                               clearable
            >
            </app-entity-select>
        </v-flex>
        <v-flex xs12 md3 lg2>
            <v-select label="Weiter Verwenden"
                      :items="[
                                      {
                                          text: 'Ja',
                                          value: '1'
                                      },
                                      {
                                          text: 'Nein',
                                          value: '0'
                                      },
                                  ]"
                      prepend-icon="mdi-arrow-right-box"
                      v-model="attributes.WEITER_VERWENDEN"
                      clearable
            >
            </v-select>
        </v-flex>
        <v-flex xs12 md3 lg2>
            <b-year-field
                    label="Verwendungsjahr"
                    v-model="attributes.VERWENDUNGS_JAHR"
                    clearable
                    prepend-icon="mdi-timer-sand"
            ></b-year-field>
        </v-flex>
        <v-flex xs12 lg1 class="text-xs-right" style="align-self: center">
            <v-btn @click="onSave"
                   class="error"
                   :disabled="lines && lines.length === 0"
            >Ändern
            </v-btn>
        </v-flex>
    </v-layout>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {namespace, Mutation} from "vuex-class";
    import {Prop, Watch} from "vue-property-decorator";
    import {
        BankAccountStandardChart
    } from "../../../../server/resources";
    import axios from '../../../../libraries/axios';

    const SnackbarMutation = namespace('shared/snackbar', Mutation);
    const RefreshMutation = namespace('shared/refresh', Mutation);

    @Component
    export default class EditBatch extends Vue {
        @Prop({type: Array})
        lines: Array<string>;

        @Watch('lines')
        onLinesChange(val) {
            if (val) {
                this.attributes.lines = val;
            }
        }

        mounted() {
            this.onLinesChange(this.lines);
        }

        @SnackbarMutation('updateMessage')
        updateMessage: Function;

        @RefreshMutation('requestRefresh')
        requestRefresh: Function;

        saving: boolean = false;

        reuse: boolean = false;

        standardChart: BankAccountStandardChart | null = null;

        attributes: {
            KOSTENTRAEGER_TYP: string,
            KOSTENTRAEGER_ID: string,
            KONTENRAHMEN_KONTO: string,
            WEITER_VERWENDEN: string,
            VERWENDUNGS_JAHR: string,
            lines: Array<string>
        } = {
            KOSTENTRAEGER_TYP: '',
            KOSTENTRAEGER_ID: '',
            KONTENRAHMEN_KONTO: '',
            WEITER_VERWENDEN: '',
            VERWENDUNGS_JAHR: '',
            lines: []
        };

        selectCostUnit(selected) {
            if (selected) {
                this.attributes.KOSTENTRAEGER_TYP = selected.getMorphName();
                this.attributes.KOSTENTRAEGER_ID = selected.getID();
            }
        }

        selectBookingAccount(selected) {
            if (selected) {
                this.attributes.KONTENRAHMEN_KONTO = selected.KONTO;
            }
        }

        onSave() {
            this.saving = true;
            axios.put('/api/v1/invoice-line-assignments/update-batch', this.attributes).then(() => {
                this.saving = false;
                this.updateMessage('Zuweisungen geändert.');
                this.requestRefresh();
            }).catch(error => {
                this.saving = false;
                this.updateMessage('Fehler beim Ändern der Zuweisungen. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
                this.requestRefresh();
            });
        }

        get bookingAccountEntity() {
            return this.standardChart ? ['buchungskonto:' + this.standardChart.KONTENRAHMEN_ID] : ['buchungskonto'];
        }
    }
</script>