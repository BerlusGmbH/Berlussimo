<template>
    <div style="display: flex">
        <div style="margin-right: 0.2rem">
            <div class="identifier">
                <b-icon :tooltips="['Telefon']" class="identifier-icon">mdi-phone</b-icon>
                <div @click="copyToClipboard(value.DETAIL_INHALT, 'Telefon')"
                     style="cursor: pointer" ref="detail">
                    {{value.DETAIL_INHALT}}
                </div>
            </div>
            <div class="identifier" v-if="value.DETAIL_BEMERKUNG">
                <b-icon :tooltips="['Bemerkung']" class="identifier-icon">mdi-note</b-icon>
                <div @click="copyToClipboard(value.DETAIL_BEMERKUNG, 'Bemerkung')"
                     style="cursor: pointer"
                >
                    {{value.DETAIL_BEMERKUNG}}
                </div>
            </div>
        </div>
        <v-menu offset-y v-model="show" :position-absolutely="true" style="vertical-align: top">
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
        <b-detail-edit-dialog :position-absolutely="true"
                              :show="edit"
                              @show="val => {edit = val}"
                              :position-x="x"
                              :position-y="y"
                              :value="value"
                              @input="$emit('input', $event); saveDetail($event)"
                              prepend-icon="mdi-phone"
        >
        </b-detail-edit-dialog>
        <b-detail-delete-dialog v-model="deleteDialog" :detail="value" @delete="deleteDetail"
        ></b-detail-delete-dialog>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Detail} from "../../../../server/resources";
    import {namespace} from "vuex-class";
    import detailIdentifier from "./DetailIdentifier.vue"
    import axios from "../../../../libraries/axios"

    const WorkplaceModule = namespace('shared/workplace');

    @Component({
        extends: detailIdentifier
    })
    export default class PhoneIdentifier extends Vue {
        value: Detail;
        updateMessage: Function;

        @WorkplaceModule.State('hasPhone')
        workplaceHasPhone: boolean;

        show: boolean;
        edit: boolean;

        x: Number;
        y: Number;

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