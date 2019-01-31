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
                        {{value.livingSpaceSize}} m²
                    </v-flex>
                    <v-flex xs12 sm6>
                        <v-icon class="detail">mdi-store</v-icon>
                        {{value.commercialSpaceSize}} m²
                    </v-flex>
                    <v-flex v-if="value.owner" xs12>
                        <v-icon class="detail">mdi-key</v-icon>
                        <app-identifier :value="value.owner" style="width: calc(100% - 18px)"></app-identifier>
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
    import {Property} from "../../../models";

    @Component
    export default class PropertyCard extends Vue {
        @Prop({type: Object})
        value: Property;
    }
</script>

<style>
    .detail {
        font-size: inherit;
        vertical-align: baseline;
    }
</style>
