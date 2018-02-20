<template>
    <v-card>
        <v-card-title>
            <app-identifier class="headline"
                            :value="value"
            ></app-identifier>
        </v-card-title>
        <v-card-text>
            <v-layout row wrap>
                <v-flex xs12 md6>
                    <v-layout row wrap>
                        <v-flex xs12 v-if="value.from">
                            Von:
                            <app-identifier style="width: calc(100% - 40px)" :value="value.from"
                            ></app-identifier>
                        </v-flex>
                        <v-flex xs12 v-if="value.to">
                            An:
                            <app-identifier style="width: calc(100% - 40px)" :value="value.to"
                            ></app-identifier>
                        </v-flex>
                        <v-flex xs12>
                            <v-icon style="font-size: inherit">mdi-note</v-icon>
                            {{value.KURZBESCHREIBUNG}}
                        </v-flex>
                        <v-flex xs12 md6 v-if="value.bank_account">
                            <app-identifier :value="value.bank_account"></app-identifier>
                        </v-flex>
                    </v-layout>
                </v-flex>
                <v-flex xs12 md6>
                    <v-layout row wrap>
                        <v-flex xs4>
                            <v-layout column>
                                <v-flex>
                                    <v-icon style="font-size: inherit">mdi-calendar-blank</v-icon>
                                    {{value.RECHNUNGSDATUM}}
                                </v-flex>
                                <v-flex>
                                    <v-icon style="font-size: inherit">mdi-calendar</v-icon>
                                    {{value.EINGANGSDATUM}}
                                </v-flex>
                                <v-flex>
                                    <v-icon style="font-size: inherit">mdi-calendar-clock</v-icon>
                                    {{value.FAELLIG_AM}}
                                </v-flex>
                                <v-flex>
                                    <v-icon style="font-size: inherit">mdi-calendar-check</v-icon>
                                    {{value.BEZAHLT_AM}}
                                </v-flex>
                            </v-layout>
                        </v-flex>
                        <v-flex xs3 sm3>
                            <v-layout column>
                                <v-flex>
                                    <v-icon style="font-size: inherit">mdi-call-received</v-icon>
                                    {{value.EMPFAENGER_EINGANGS_RNR}}
                                </v-flex>
                                <v-flex>
                                    <v-icon style="font-size: inherit">mdi-call-made</v-icon>
                                    {{value.AUSTELLER_AUSGANGS_RNR}}
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
                                    Skonto:
                                </v-flex>
                            </v-layout>
                        </v-flex>
                        <v-flex xs3 sm3>
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

    @Component
    export default class InvoiceCard extends Vue {
        @Prop()
        value;
    }
</script>