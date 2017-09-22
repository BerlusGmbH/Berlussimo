<template>
    <div style="display: flex">
        <div style="margin-right: 0.2rem; display: flex; flex-direction: column">
            <div class="identifier">
                <v-icon class="identifier-icon">mdi-phone</v-icon>
                <div @click="copyToClipboard(value.DETAIL_INHALT)"
                     style="display: inline-block; cursor: pointer; vertical-align: middle" ref="detail">
                    {{value.DETAIL_INHALT}}
                </div>
            </div>
            <div class="identifier">
                <template v-if="value.DETAIL_BEMERKUNG">
                    <v-icon class="identifier-icon">mdi-note</v-icon>
                    <div @click="copyToClipboard(value.DETAIL_BEMERKUNG)"
                         style="display: inline-block; cursor: pointer; vertical-align: middle"
                    >
                        {{value.DETAIL_BEMERKUNG}}
                    </div>
                </template>
            </div>
        </div>
        <app-detail-edit-dialog :position-absolutely="true"
                                :show="edit"
                                @show="val => {edit = val}"
                                :position-x="x"
                                :position-y="y"
                                :value="value"
                                @input="$emit('input', $event); saveDetail($event)"
                                prepend-icon="mdi-phone"
        >
        </app-detail-edit-dialog>
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
        <app-detail-delete-dialog v-model="deleteDialog" :detail="value" @delete="deleteDetail"
        ></app-detail-delete-dialog>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Detail} from "../../../../server/resources";
    import {namespace, State} from "vuex-class";
    import detailIdentifier from "./DetailIdentifier.vue"
    import axios from "../../../../libraries/axios"

    const WorkplaceState = namespace('shared/workplace', State);

    @Component({
        extends: detailIdentifier
    })
    export default class PhoneIdentifier extends Vue {
        value: Detail;
        updateMessage: Function;

        @WorkplaceState('hasPhone')
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
            axios.get('/api/v1/call/' + this.value.DETAIL_ID).then(() => {
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

<style>
    .identifier-icon {
        position: absolute;
        left: 0;
    }

    .identifier {
        padding-left: 1.2em;
        position: relative;
    }
</style>