<template>
    <div>
        <b-input hide-details>
            <b-icon :tooltips="[value.__typename]" slot="prepend">mdi-notebook</b-icon>
            <div @click="copyToClipboard(value.value, value.__typename)"
                 ref="detail" style="display: inline-block; cursor: pointer; vertical-align: middle">
                {{value.value}}
            </div>
            <template slot="append">
                <app-detail-edit-dialog :error-messages="errorMessages"
                                        :loading.sync="loading"
                                        :show="edit"
                                        :value="value"
                                        @input="saveDetail($event)"
                                        @show="val => {edit = val}"
                                        prepend-icon="mdi-fax"
                >
                </app-detail-edit-dialog>
                <v-menu :position-absolutely="true" offset-y style="vertical-align: top" v-model="show">
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
                <app-detail-delete-dialog :detail="value" @delete="deleteDetail" v-model="deleteDialog"
                ></app-detail-delete-dialog>
            </template>
        </b-input>
        <b-input hide-details v-if="value.comment">
            <b-icon :tooltips="['Bemerkung']" class="identifier-icon" slot="prepend">mdi-note</b-icon>
            <div @click="copyToClipboard(value.comment, 'Bemerkung')"
                 style="cursor: pointer;"
            >
                {{value.comment}}
            </div>
        </b-input>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Detail} from "../../../models";
    import BaseDetailIdentifier from "./BaseDetailIdentifier.vue"
    import {ErrorMessages} from "../../../mixins";

    @Component({
        extends: BaseDetailIdentifier
    })
    export default class DetailIdentifier extends Vue {
        value: Detail;

        show: boolean;
        edit: boolean;

        loading: boolean = false;
        errorMessages: ErrorMessages;

        copyToClipboard: Function;
        editDetail: Function;
        saveDetail: Function;
        deleteDetail: Function;
    }
</script>