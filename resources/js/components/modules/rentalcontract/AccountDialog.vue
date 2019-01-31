<template>
    <v-dialog :value="value"
              @input="$emit('input', $event)"
              max-width="300"
    >
        <v-card>
            <v-card-title>
                <v-icon>mdi-file-pdf</v-icon>
                <span class="headline">Mietkonto ab...</span>
            </v-card-title>
            <v-card-text>
                <v-container fluid grid-list-md>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-text-field :max="(new Date).toISOString().slice(0,7)"
                                          :min="new Date(contract.start).toISOString().slice(0,7)"
                                          type="month"
                                          v-model="month"
                            ></v-text-field>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn @click="$emit('input', false)" flat>Abbrechen</v-btn>
                <v-btn @click="showAccountPDF()">
                    Öffnen
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import EntitySelect from "../../common/EntitySelect.vue"
    import {RentalContract} from "../../../models";

    @Component({components: {'app-entity-select': EntitySelect}})
    export default class AccountDialog extends Vue {
        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        contract: RentalContract;

        month: string = (new Date).toISOString().slice(0, 7);
        max: string = '';

        showAccountPDF() {
            let monthArray = this.month.split('-');
            window.open('/mietkontenblatt?anzeigen=show_mkb2pdf'
                + '&mv_id=' + this.contract.id
                + '&monat=' + monthArray[1]
                + '&jahr=' + monthArray[0]
            );
            this.$emit('input', false);
        }
    }
</script>
