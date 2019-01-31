<template>
    <div>
        <b-input hide-details>
            <b-icon :tooltips="['Telefon']" class="identifier-icon" slot="prepend">mdi-phone</b-icon>
            <div @click="copyToClipboard(value.value, 'Telefon')"
                 ref="detail" style="cursor: pointer">
                {{value.value}}
            </div>
            <template slot="append">
                <v-menu offset-y v-model="show">
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
                <app-detail-edit-dialog :error-messages="errorMessages"
                                        :loading.sync="loading"
                                        :show="edit"
                                        :value="value"
                                        @input="saveDetail($event)"
                                        @show="val => {edit = val}"
                                        prepend-icon="mdi-phone"
                >
                </app-detail-edit-dialog>
                <app-detail-delete-dialog :detail="value" @delete="deleteDetail" v-model="deleteDialog"
                ></app-detail-delete-dialog>
            </template>
        </b-input>
        <b-input hide-details v-if="value.comment">
            <b-icon :tooltips="['Bemerkung']" class="identifier-icon" slot="prepend">mdi-note</b-icon>
            <div @click="copyToClipboard(value.comment, 'Bemerkung')"
                 style="cursor: pointer"
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
    import DetailIdentifier from "./DetailIdentifier.vue";
    import PhoneAtWorkplaceQuery from './PhoneAtWorkplaceQuery.graphql';
    import DialMutation from './DialMutation.graphql';
    import {ErrorMessages} from "../../../mixins";

    @Component({
        extends: DetailIdentifier,
        apollo: {
            phoneAtWorkplace: {
                query: PhoneAtWorkplaceQuery
            }

        }
    })
    export default class PhoneIdentifier extends Vue {
        value: Detail;

        phoneAtWorkplace: boolean;

        show: boolean;
        edit: boolean;

        copyToClipboard: Function;
        editDetail: Function;
        saveDetail: Function;
        deleteDetail: Function;

        loading: boolean = false;
        errorMessages: ErrorMessages;

        dialDirect() {
            window.open('tel:' + this.value.value);
        }

        dialViaServer() {
            this.$apollo.mutate({
                mutation: DialMutation,
                variables: {
                    id: this.value.id
                }
            });
        }

        call() {
            if (this.phoneAtWorkplace) {
                this.dialViaServer();
            } else {
                this.dialDirect();
            }
        }
    }
</script>