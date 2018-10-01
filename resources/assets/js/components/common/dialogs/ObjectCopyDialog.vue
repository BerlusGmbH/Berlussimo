<template>
    <v-dialog :value="value" @input="$emit('input', $event)" lazy width="480">
        <v-card>
            <v-card-title class="headline">
                <v-icon>mdi-content-copy</v-icon>
                <v-icon>mdi-city</v-icon>
                &nbsp;Objekt kopieren
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <b-entity-select label="Quellobjekt"
                                         :value="object"
                                         @input="val => parameters.object = val.OBJEKT_ID"
                                         :entities="['objekt']"
                                         disabled
                                         prepend-icon="mdi-city"
                        >
                        </b-entity-select>
                    </v-flex>
                    <v-flex xs12>
                        <v-text-field label="Neuer Name"
                                      prepend-icon="mdi-alphabetical"
                                      v-model="parameters.name"
                        >
                        </v-text-field>
                    </v-flex>
                    <v-flex xs12>
                        <v-text-field label="Präfix für Einheiten"
                                      prepend-icon="mdi-alphabetical"
                                      v-model="parameters.prefix"
                        >
                        </v-text-field>
                    </v-flex>
                    <v-flex xs12>
                        <b-entity-select label="Neuer Eigentümer"
                                         prepend-icon="mdi-account-multiple"
                                         :entities="['partner']"
                                         @input="val => parameters.owner = val.PARTNER_ID"
                        >
                        </b-entity-select>
                    </v-flex>
                    <v-flex xs12>
                        <v-text-field label="Datum für Saldovortrag Vorverwaltung"
                                      type="date"
                                      prepend-icon="mdi-calendar"
                                      v-model="parameters.opening_balance_date"
                        >
                        </v-text-field>
                    </v-flex>
                    <v-flex xs12>
                        <v-switch label="Saldo übernehmen"
                                  v-model="parameters.opening_balance"
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
    import {Prop, Watch} from "vue-property-decorator";
    import {Objekt} from "../../../server/resources/models";
    import axios from "../../../libraries/axios";

    @Component
    export default class ObjectCopyDialog extends Vue {

        @Prop({type: Object})
        object: Objekt;

        @Prop({type: Boolean})
        value: boolean;

        @Watch('value')
        onvalueChange(val) {
            if (val && this.object) {
                this.parameters.object = this.object.OBJEKT_ID;
            }
        }

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
                axios.get(this.object.getApiBaseUrl() + '/' + this.object.OBJEKT_ID + '/copy', {params: this.parameters})
            }

        }
    }
</script>
