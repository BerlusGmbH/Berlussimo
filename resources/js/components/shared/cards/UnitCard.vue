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
                        <span style="margin-right: 8px">
                            <app-identifier :value="value.house"></app-identifier>
                        </span>
                        <app-identifier :value="value.house.property"></app-identifier>
                    </div>
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text>
            <v-container fluid grid-list-sm>
                <v-layout row wrap>
                    <v-flex xs12 sm6>
                        <v-icon style="font-size: inherit; vertical-align: baseline;">mdi-compass</v-icon>
                        {{ value.location }}
                    </v-flex>
                    <v-flex xs12 sm6>
                        <v-icon style="font-size: inherit; vertical-align: baseline;">mdi-arrow-expand-all</v-icon>
                        {{ value.size }}
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
    import {Unit} from "../../../models";

    @Component
    export default class UnitCard extends Vue {
        @Prop()
        value: Unit;
    }
</script>
