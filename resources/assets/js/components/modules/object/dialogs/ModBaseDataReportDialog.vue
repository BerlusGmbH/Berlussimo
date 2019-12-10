<template>
    <v-dialog :value="value" @input="$emit('input', $event)" max-width="340">
        <v-card>
            <v-card-title>
                <v-icon>mdi-file-excel</v-icon>
                <span class="headline">MOD Basisdaten</span>
            </v-card-title>
            <v-card-text class="secondary">
                <v-container fluid grid-list-md>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-date-picker locale="de-de" type="month" v-model="date"
                            ></v-date-picker>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn @click="$emit('input', false)" flat>Abbrechen</v-btn>
                <v-btn @click="$emit('input', false); open()"
                       color="primary"
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
    import moment from "moment";

    @Component
    export default class RevenueReportDialog extends Vue {
        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        object: Objekt;

        date: string = '';

        created() {
            this.date = moment().date(0).format("YYYY-MM-DD");
        }

        open() {
            window.open('/api/v1/reports/mod/' + this.object.OBJEKT_ID + '?date=' + this.date, 'blank');
        }
    }
</script>