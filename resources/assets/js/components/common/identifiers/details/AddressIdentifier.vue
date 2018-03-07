<template>
    <div style="display: flex">
        <div style="margin-right: 0.2rem; display: flex; flex-direction: column">
            <div class="identifier" style="display: block">
                <b-icon :tooltips="['Adresse']" class="identifier-icon">mdi-email</b-icon>
                <div @click="copyToClipboard(value.DETAIL_INHALT, 'Adresse')"
                     style="display: inline-block; cursor: pointer; vertical-align: middle" ref="detail"
                     v-html="$options.filters.substituteNewlineWithBr(value.DETAIL_INHALT)"
                >
                </div>
            </div>
            <div class="identifier" style="display: block">
                <template :tooltips="['Bemerkung']" v-if="value.DETAIL_BEMERKUNG">
                    <b-icon class="identifier-icon">mdi-note</b-icon>
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
                                @input="saveDetail($event); $emit('input', $event)"
                                prepend-icon="mdi-email"
                                large
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