<template>
    <v-dialog :value="value" @input="$emit('input', $event)" max-width="340">
        <v-card>
            <v-card-title>
                <v-icon>mdi-file-excel</v-icon>
                <span class="headline">Umsatzübersicht</span>
            </v-card-title>
            <v-card-text class="secondary">
                <v-container fluid grid-list-md>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-radio-group v-model="period" row>
                                <v-radio label="Monat" value="month"></v-radio>
                                <v-radio label="Quartal" value="quarter"></v-radio>
                                <v-radio label="Jahr" value="year"></v-radio>
                            </v-radio-group>
                        </v-flex>
                        <v-flex xs12>
                            <v-date-picker locale="de-de" v-model="date" type="month"
                                           :allowed-dates="allowedDates"></v-date-picker>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn flat @click="$emit('input', false)">Abbrechen</v-btn>
                <v-btn color="primary"
                       @click="$emit('input', false); open()"
                >
                    <v-icon>mdi-file-excel</v-icon>
                    Anzeigen
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Objekt} from "../../../../server/resources";

    @Component
    export default class RevenueReportDialog extends Vue {
        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        object: Objekt;

        period: string = 'year';
        date: string = '';

        mounted() {
            let date = new Date();

            this.date = (date.getFullYear() - 1) + '-01';
        }

        allowedDates(val) {
            let dateArray = val.split('-');
            let month = parseInt(dateArray[1], 10);
            switch (this.period) {
                case 'quarter':
                    return (month + 2) % 3 === 0;
                case 'year':
                    return month === 1;
                default:
                    return true;
            }
        }

        open() {
            window.open('/api/v1/reports/revenue/' + this.object.OBJEKT_ID + '?period=' + this.period + '&date=' + this.date, 'blank');
        }
    }
</script>