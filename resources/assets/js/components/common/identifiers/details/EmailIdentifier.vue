<template>
    <div>
        <b-input hide-details>
            <b-icon :tooltips="['E-Mail']" class="identifier-icon" slot="prepend">mdi-mail-ru</b-icon>
            <div @click="copyToClipboard(value.DETAIL_INHALT, 'E-Mail')"
                 ref="detail" style="display: inline-block; cursor: pointer; vertical-align: middle">
                {{value.DETAIL_INHALT}}
            </div>
            <template slot="append">
                <v-menu :position-absolutely="true" offset-y style="vertical-align: top" v-model="show">
                    <v-icon slot="activator" style="font-size: 14px">mdi-arrow-down-drop-circle</v-icon>
                    <v-list>
                        <v-list-tile @click="writeEMail">
                            <v-list-tile-avatar>
                                <v-icon>mdi-mail-ru</v-icon>
                            </v-list-tile-avatar>
                            <v-list-tile-title>Schreiben</v-list-tile-title>
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
                <app-detail-edit-dialog :position-absolutely="true"
                                        :position-x="x"
                                        :position-y="y"
                                        :show="edit"
                                        :value="value"
                                        @input="$emit('input', $event); saveDetail($event)"
                                        @show="val => {edit = val}"
                                        prepend-icon="mdi-mail-ru"
                >
                </app-detail-edit-dialog>
                <app-detail-delete-dialog :detail="value" @delete="deleteDetail" v-model="deleteDialog"
                ></app-detail-delete-dialog>
            </template>
        </b-input>
        <b-input hide-details v-if="value.DETAIL_BEMERKUNG">
            <b-icon :tooltips="['Bemerkung']" class="identifier-icon" slot="prepend">mdi-note</b-icon>
            <div @click="copyToClipboard(value.DETAIL_BEMERKUNG, 'Bemerkung')"
                 style="display: inline-block; cursor: pointer; vertical-align: middle"
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
    import detailIdentifier from "./DetailIdentifier.vue"

    @Component({
        extends: detailIdentifier
    })
    export default class EmailIdentifier extends Vue {
        value: Detail;

        show: boolean;
        edit: boolean;

        x: Number;
        y: Number;

        copyToClipboard: Function;
        editDetail: Function;
        saveDetail: Function;
        deleteDetail: Function;

        writeEMail() {
            window.open('mailto:' + this.value.DETAIL_INHALT, '_blank')
        }
    }
</script>