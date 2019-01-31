<template>
    <v-dialog :value="value"
              @input="$emit('input', $event)"
              max-width="300"
    >
        <v-card>
            <v-card-title>
                <v-icon>mdi-file-pdf</v-icon>
                <span class="headline">Hausgeldkonto für...</span>
            </v-card-title>
            <v-card-text>
                <v-container fluid grid-list-md>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-autocomplete v-model="year"
                                            :items="years"
                            ></v-autocomplete>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn flat @click="$emit('input', false)">Abbrechen</v-btn>
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
    import {namespace} from "vuex-class";
    import {Prop} from "vue-property-decorator";
    import EntitySelect from "../../common/EntitySelect.vue"
    import {PurchaseContract} from "../../../server/resources";

    const Snackbar = namespace('shared/snackbar');
    const Refresh = namespace('shared/refresh');

    @Component({components: {'app-entity-select': EntitySelect}})
    export default class AccountDialog extends Vue {
        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        contract: PurchaseContract;

        @Snackbar.Mutation('updateMessage')
        updateMessage: Function;

        @Refresh.Mutation('requestRefresh')
        requestRefresh: Function;

        end: number = new Date().getFullYear();
        year: number = this.end;

        showAccountPDF() {
            window.open('/weg?option=hg_kontoauszug'
                + '&eigentuemer_id=' + this.contract.getID()
                + '&jahr=' + this.year
            );
            this.$emit('input', false);
        }

        get start(): number {
            let year = new Date(this.contract.VON).getFullYear();
            if (!year) {
                return new Date().getFullYear();
            }
            return year;
        }

        get years(): number[] {
            let length = this.end - this.start + 1;
            return Array(length).fill(0, 0, length).map((_item, index) => this.start + index);
        }
    }
</script>