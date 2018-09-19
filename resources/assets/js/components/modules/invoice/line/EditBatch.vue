<template>
    <v-layout row wrap>
        <v-flex xs12 md5>
            <v-text-field prepend-icon="mdi-percent"
                          label="Rabatt"
                          type="number"
                          step="0.01"
                          v-model="attributes.RABATT_SATZ"
                          clearable
            >
            </v-text-field>
        </v-flex>
        <v-flex xs12 md5>
            <v-text-field prepend-icon="mdi-percent"
                          label="Skonto"
                          type="number"
                          step="0.01"
                          v-model="attributes.SKONTO"
                          clearable
            >
            </v-text-field>
        </v-flex>

        <v-flex xs12 md2 class="text-xs-right" style="align-self: center">
            <v-btn @click="onSave"
                   class="error"
                   :disabled="lines && lines.length === 0"
            >Ändern
            </v-btn>
        </v-flex>
    </v-layout>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {namespace} from "vuex-class";
    import {Prop, Watch} from "vue-property-decorator";
    import axios from '../../../../libraries/axios';

    const SnackbarModule = namespace('shared/snackbar');
    const RefreshModule = namespace('shared/refresh');

    @Component
    export default class EditBatch extends Vue {
        @Prop({type: Array})
        lines: Array<string>;

        @Watch('lines')
        onLinesChange(val) {
            if (val) {
                this.attributes.lines = val;
            }
        }

        mounted() {
            this.onLinesChange(this.lines);
        }

        @SnackbarModule.Mutation('updateMessage')
        updateMessage: Function;

        @RefreshModule.Mutation('requestRefresh')
        requestRefresh: Function;

        saving: boolean = false;

        attributes: {
            RABATT_SATZ: string,
            SKONTO: string,
            lines: Array<string>
        } = {
            RABATT_SATZ: '',
            SKONTO: '',
            lines: []
        };

        onSave() {
            this.saving = true;
            axios.put('/api/v1/invoice-lines/update-batch', this.attributes).then(() => {
                this.saving = false;
                this.updateMessage('Positionen geändert.');
                this.requestRefresh();
            }).catch(error => {
                this.saving = false;
                this.updateMessage('Fehler beim Ändern der Positionen. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
                this.requestRefresh();
            });
        }
    }
</script>