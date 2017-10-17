<template>
    <v-dialog :value="value" @input="$emit('input', $event)" lazy width="480">
        <v-card>
            <v-card-title class="headline">Objekt kopieren</v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <app-entity-select label="Quellobjekt"
                        >
                        </app-entity-select>
                    </v-flex>
                    <v-flex xs12>
                        <v-text-field label="Neuer Name"
                        >
                        </v-text-field>
                    </v-flex>
                    <v-flex xs12>
                        <v-text-field label="Präfix für Einheiten"
                        >
                        </v-text-field>
                    </v-flex>
                    <v-flex xs12>
                        <app-entity-select label="Neuer Eigentümer"
                        >
                        </app-entity-select>
                    </v-flex>
                    <v-flex xs12>
                        <v-text-field label="Datum für Saldovortrag Vorverwaltung"
                        >
                        </v-text-field>
                    </v-flex>
                    <v-flex xs12>
                        <v-switch label="Saldo übernehmen"
                        >
                        </v-switch>
                    </v-flex>
                </v-layout>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn flat="flat" @click.native="$emit('input', false)">Abbrechen</v-btn>
                <v-btn class="red" @click.native="copy(); $emit('input', false)">Kopieren</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Objekt} from "../../../server/resources/models";
    import axios from "libraries/axios";

    @Component
    export default class ObjectCopyDialog extends Vue {

        @Prop({type: Boolean})
        value: boolean;

        object: Objekt | null = null;

        parameters: {
            object: number | null;
            opening_balance: boolean;
            opening_balance_date: string | null;
            name: string | null;
            prefix: string | null;
            owner: number | null;
        } = {
            object: null,
            opening_balance: false,
            opening_balance_date: null,
            name: null,
            prefix: null,
            owner: null
        };

        copy() {
            if (this.object) {
                axios.get(this.object.getApiBaseUrl() + '/' + this.object.OBJEKT_ID + '/copy', {data: this.parameters})
            }

        }
    }
</script>
