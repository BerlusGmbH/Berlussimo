<template>
    <div style="display: flex">
        <div style="margin-right: 0.2rem; display: flex; flex-direction: column">
            <div class="identifier" style="display: block">
                <v-icon class="identifier-icon">mdi-fax</v-icon>
                <div @click="copyToClipboard(value.DETAIL_INHALT, 'Faxnummer')"
                     style="display: inline-block; cursor: pointer; vertical-align: middle" ref="detail">
                    {{value.DETAIL_INHALT}}
                </div>
            </div>
            <div class="identifier" style="display: block">
                <template v-if="value.DETAIL_BEMERKUNG">
                    <v-icon class="identifier-icon">mdi-note</v-icon>
                    <div @click="copyToClipboard(value.DETAIL_BEMERKUNG, 'Bemerkung')"
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
                                prepend-icon="mdi-fax"
        >
        </app-detail-edit-dialog>
        <v-menu offset-y v-model="show" :position-absolutely="true" style="vertical-align: top">
            <v-icon slot="activator" style="font-size: 14px">mdi-arrow-down-drop-circle</v-icon>
            <v-list>
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
    import detailIdentifier from "./DetailIdentifier.vue"

    @Component({
        extends: detailIdentifier
    })
    export default class FaxIdentifier extends Vue {
        value: Detail;

        show: boolean;
        edit: boolean;

        x: Number;
        y: Number;

        copyToClipboard: Function;
        editDetail: Function;
        saveDetail: Function;
        deleteDetail: Function;
    }
</script>