<template>
    <v-card>
        <v-card-title>
            <v-layout row wrap>
                <v-flex xs12>
                    <app-identifier class="headline"
                                    :value="value"
                    ></app-identifier>
                </v-flex>
                <v-flex xs12>
                    <div style="font-size: small; line-height: 24px; margin-left: 6px">
                        <app-identifier :value="value.objekt"></app-identifier>
                    </div>
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text>
            <v-container fluid grid-list-sm>
                <v-layout row wrap>
                    <v-flex xs12 sm6>
                        <v-icon class="detail">mdi-email</v-icon>
                        {{value.HAUS_PLZ}} {{value.HAUS_STADT}}
                    </v-flex>
                    <v-flex xs12 sm6>
                        <v-icon class="detail">mdi-home</v-icon>
                        <a :href="'/einheiten?q=!einheit(haus(id=' + value.HAUS_ID + ') (typ=Wohnraum or typ=Wohneigentum))'">{{value.wohnflaeche}}</a>
                        m²
                    </v-flex>
                    <v-flex xs12 sm6>
                        <v-icon class="detail">mdi-store</v-icon>
                        <a :href="'/einheiten?q=!einheit(haus(id=' + value.HAUS_ID + ') typ=Gewerbe)'">{{value.gewerbeflaeche}}</a>
                        m²
                    </v-flex>
                </v-layout>
            </v-container>
        </v-card-text>
    </v-card>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Haus} from "../../../server/resources/models";

    @Component
    export default class HouseCard extends Vue {
        @Prop({type: Object})
        value: Haus;
    }
</script>

<style>
    .detail {
        font-size: inherit;
        vertical-align: baseline;
    }
</style>