<template>
    <v-card>
        <v-card-title>
            <v-layout row wrap>
                <v-flex xs12>
                    <app-identifier class="headline"
                                    :value="value"
                    ></app-identifier>
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text>
            <v-container fluid grid-list-sm>
                <v-layout row wrap>
                    <v-flex xs12 sm6>
                        <v-icon class="detail">mdi-home</v-icon>
                        {{value.wohnflaeche}} m²
                    </v-flex>
                    <v-flex xs12 sm6>
                        <v-icon class="detail">mdi-store</v-icon>
                        {{value.gewerbeflaeche}} m²
                    </v-flex>
                    <v-flex xs12 sm6 v-if="value.eigentuemer">
                        <v-icon class="detail">mdi-key</v-icon>
                        <app-identifier :value="value.eigentuemer"></app-identifier>
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
    import {Objekt} from "../../../server/resources/models";

    @Component
    export default class ObjectCard extends Vue {
        @Prop({type: Object})
        value: Objekt;
    }
</script>

<style>
    .detail {
        font-size: inherit;
        vertical-align: baseline;
    }
</style>