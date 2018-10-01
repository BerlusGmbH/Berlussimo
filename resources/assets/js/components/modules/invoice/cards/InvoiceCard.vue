<template>
    <v-card>
        <v-card-title>
            <b-identifier class="headline"
                          :value="value"
            ></b-identifier>
        </v-card-title>
        <v-card-text>
            <v-layout row wrap>
                <v-flex xs12 md6>
                    <v-layout row wrap>
                        <v-flex xs9 v-if="value.from">
                            Von:
                            <b-identifier style="width: calc(100% - 40px)" :value="value.from"
                            ></b-identifier>
                        </v-flex>
                        <v-flex xs3>
                            <v-tooltip bottom>
                                <v-icon slot='activator' style="font-size: inherit">mdi-call-made</v-icon>
                                <span>Warenausgangsnummer</span>
                            </v-tooltip>
                            {{value.AUSTELLER_AUSGANGS_RNR}}
                        </v-flex>
                        <v-flex xs9 v-if="value.to">
                            An:
                            <b-identifier style="width: calc(100% - 40px)" :value="value.to"
                            ></b-identifier>
                        </v-flex>
                        <v-flex xs3>
                            <v-tooltip bottom>
                                <v-icon slot='activator' style="font-size: inherit">mdi-call-received</v-icon>
                                <span>Wareneingangsnummer</span>
                            </v-tooltip>
                            {{value.EMPFAENGER_EINGANGS_RNR}}
                        </v-flex>
                        <v-flex xs12>
                            <b-icon :tooltips="['Kurzbeschreibung']">mdi-note</b-icon>
                            {{value.KURZBESCHREIBUNG}}
                        </v-flex>
                        <v-flex xs12 v-if="value.bank_account">
                            <b-identifier :value="value.bank_account"></b-identifier>
                        </v-flex>
                    </v-layout>
                </v-flex>
                <v-flex xs12 md6>
                    <v-layout row wrap>
                        <v-flex xs6>
                            <v-layout column>
                                <v-flex>
                                    <v-tooltip bottom>
                                        <v-icon slot='activator' style="font-size: inherit">mdi-calendar-blank</v-icon>
                                        <span>Rechnungsdatum</span>
                                    </v-tooltip>
                                    {{value.RECHNUNGSDATUM|dformat}}
                                </v-flex>
                                <v-flex>
                                    <v-tooltip bottom>
                                        <v-icon slot='activator' style="font-size: inherit">mdi-calendar</v-icon>
                                        <span>Eingangsdatum</span>
                                    </v-tooltip>
                                    {{value.EINGANGSDATUM|dformat}}
                                </v-flex>
                                <v-flex>
                                    <v-tooltip bottom>
                                        <v-icon slot='activator' style="font-size: inherit">mdi-calendar-clock</v-icon>
                                        <span>Fälligkeitsdatum</span>
                                    </v-tooltip>
                                    {{value.FAELLIG_AM|dformat}}
                                </v-flex>
                                <v-flex v-if="paydateIsValid">
                                    <v-tooltip bottom>
                                        <v-icon slot='activator' style="font-size: inherit">mdi-calendar-check</v-icon>
                                        <span>Geldeingangsdatum</span>
                                    </v-tooltip>
                                    {{value.BEZAHLT_AM|dformat}}
                                </v-flex>
                                <v-flex v-if="value.servicetime_from">
                                    <v-tooltip bottom>
                                        <v-icon slot='activator' style="font-size: inherit">mdi-calendar-range</v-icon>
                                        <span v-if="value.servicetime_to">Leistungsdatum</span>
                                        <span v-else>Leistungszeitraum</span>
                                    </v-tooltip>
                                    {{value.servicetime_from|dformat}}
                                    <template v-if="value.servicetime_to">
                                        - {{value.servicetime_to|dformat}}
                                    </template>
                                </v-flex>
                            </v-layout>
                        </v-flex>
                        <v-flex xs2 sm2>
                            <v-layout column class="text-xs-right">
                                <v-flex>
                                    Netto:
                                </v-flex>
                                <v-flex>
                                    Brutto:
                                </v-flex>
                                <v-flex>
                                    Skontiert:
                                </v-flex>
                            </v-layout>
                        </v-flex>
                        <v-flex xs4 sm4>
                            <v-layout column class="text-xs-right">
                                <v-flex>
                                    {{value.NETTO | nformat}} €
                                </v-flex>
                                <v-flex>
                                    {{value.BRUTTO | nformat}} €
                                </v-flex>
                                <v-flex>
                                    {{value.SKONTOBETRAG | nformat}} €
                                </v-flex>
                            </v-layout>
                        </v-flex>
                    </v-layout>
                </v-flex>
            </v-layout>
        </v-card-text>
    </v-card>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import moment from 'moment';

    @Component
    export default class InvoiceCard extends Vue {
        @Prop()
        value;

        get paydateIsValid() {
            return moment(this.value.BEZAHLT_AM).isValid();
        }
    }
</script>