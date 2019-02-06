<template>
    <div>
        <b-input hide-details>
            <b-icon :tooltips="['Telefon']" class="identifier-icon" slot="prepend">mdi-phone</b-icon>
            <div @click="copyToClipboard(value.DETAIL_INHALT, 'Telefon')"
                 ref="detail" style="cursor: pointer">
                {{value.DETAIL_INHALT}}
            </div>
            <template slot="append">
                <v-menu :position-absolutely="true" offset-y style="vertical-align: top" v-model="show">
                    <v-icon slot="activator" style="font-size: 14px">mdi-arrow-down-drop-circle</v-icon>
                    <v-list>
                        <v-list-tile @click="call">
                            <v-list-tile-avatar>
                                <v-icon>mdi-phone</v-icon>
                            </v-list-tile-avatar>
                            <v-list-tile-title>Anrufen</v-list-tile-title>
                        </v-list-tile>
                        <v-list-tile @click="editDetail">
                            <v-list-tile-avatar>
                                <v-icon>edit</v-icon>
                            </v-list-tile-avatar>
                            <v-list-tile-title>Bearbeiten</v-list-tile-title>
                        </v-list-tile>
                        <v-list-tile @click="deleteDialog = true">
                            <v-list-tile-avatar>
                                <v-icon>mdi-delete</v-icon>
                            </v-list-tile-avatar>
                            <v-list-tile-title>Entfernen</v-list-tile-title>
                        </v-list-tile>
                    </v-list>
                </v-menu>
                <app-detail-edit-dialog :show="edit"
                                        :value="value"
                                        @input="$emit('input', $event); saveDetail($event)"
                                        @show="val => {edit = val}"
                                        prepend-icon="mdi-phone"
                >
                </app-detail-edit-dialog>
                <app-detail-delete-dialog :detail="value" @delete="deleteDetail" v-model="deleteDialog"
                ></app-detail-delete-dialog>
            </template>
        </b-input>
        <b-input hide-details v-if="value.DETAIL_BEMERKUNG">
            <b-icon :tooltips="['Bemerkung']" class="identifier-icon" slot="prepend">mdi-note</b-icon>
            <div @click="copyToClipboard(value.DETAIL_BEMERKUNG, 'Bemerkung')"
                 style="cursor: pointer"
            >
                {{value.DETAIL_BEMERKUNG}}
            </div>
        </b-input>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Detail} from "../../../../server/resources";
    import {namespace} from "vuex-class";
    import detailIdentifier from "./DetailIdentifier.vue"
    import axios from "../../../../libraries/axios"

    const Workplace = namespace('shared/workplace');

    @Component({
        extends: detailIdentifier
    })
    export default class PhoneIdentifier extends Vue {
        value: Detail;
        updateMessage: Function;

        @Workplace.State('hasPhone')
        workplaceHasPhone: boolean;

        show: boolean;
        edit: boolean;

        copyToClipboard: Function;
        editDetail: Function;
        saveDetail: Function;
        deleteDetail: Function;

        callDirect() {
            window.open('tel:' + this.value.DETAIL_INHALT);
        }

        callViaServer() {
            axios.get('/api/v1/pbx/call/' + this.value.DETAIL_ID).then(() => {
                this.updateMessage('Nummer wird gewählt.');
            }).catch((error) => {
                this.updateMessage('Fehler beim wählen. Code: ' + error.response.status + ' Message: ' + error.response.data);
            });
        }

        call() {
            if (this.workplaceHasPhone) {
                this.callViaServer();
            } else {
                this.callDirect();
            }
        }
    }
</script>