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
        </v-card-text>
    </v-card>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Partner} from "../../../server/resources/models";

    @Component
    export default class PartnerCard extends Vue {
        @Prop()
        value: Partner;
    }
</script>