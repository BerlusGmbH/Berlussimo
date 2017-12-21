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
                        <router-link
                                :to="{name: 'web.units.index', query: { q: '!einheit(objekt(id=' + value.OBJEKT_ID + ') (typ=Wohnraum or typ=Wohneigentum))'}}">
                            {{value.wohnflaeche}}
                        </router-link>
                        m²
                    </v-flex>
                    <v-flex xs12 sm6>
                        <v-icon class="detail">mdi-store</v-icon>
                        <router-link
                                :to="{name: 'web.units.index', query: { q: '!einheit(objekt(id=' + value.OBJEKT_ID + ') typ=Gewerbe)'}}">
                            {{value.gewerbeflaeche}}
                        </router-link>
                        m²
                    </v-flex>
                    <v-flex xs12 v-if="value.eigentuemer">
                        <v-icon class="detail">mdi-key</v-icon>
                        <app-identifier style="width: calc(100% - 18px)" :value="value.eigentuemer"></app-identifier>
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