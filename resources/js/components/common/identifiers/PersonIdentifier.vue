<template>
    <b-input hide-details>
        <template slot="prepend">
            <div v-if="value.hasNotes()">
                <b-icon :tooltips="value.getNoteTooltips()" color="error">mdi-alert</b-icon>
            </div>
            <div ref="identifier">
                <b-icon :tooltips="value.getEntityIconTooltips()">{{value.getEntityIcon()}}</b-icon>
            </div>
        </template>
        <router-link :to="{name: 'web.persons.show', params: { id: String(value.getID()) }}"
                     v-if="$router"
        >
            {{String(value)}}
        </router-link>
        <a :href="value.getDetailUrl()" v-else>{{String(value)}}</a>
        <template slot="append">
            <b-icon :tooltip="value.gender" v-if="value.getSexIcon()">{{value.getSexIcon()}}</b-icon>
            <v-menu offset-y v-model="show">
                <v-icon slot="activator" style="font-size: inherit">mdi-arrow-down-drop-circle</v-icon>
                <v-list>
                    <v-list-tile @click="copyToClipboard(String(value), 'Name')">
                        <v-list-tile-avatar>
                            <v-icon>mdi-content-copy</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Kopieren</v-list-tile-title>
                    </v-list-tile>
                    <v-list-tile @click="editPerson">
                        <v-list-tile-avatar>
                            <v-icon>edit</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Bearbeiten</v-list-tile-title>
                    </v-list-tile>
                    <v-list-tile @click="merge = true">
                        <v-list-tile-avatar>
                            <v-icon>mdi-call-merge</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Zusammenführen</v-list-tile-title>
                    </v-list-tile>
                    <v-divider></v-divider>
                    <v-list-tile @click="addDetail">
                        <v-list-tile-avatar>
                            <v-icon>add</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Detail</v-list-tile-title>
                    </v-list-tile>
                    <v-list-tile @click="login = true">
                        <v-list-tile-avatar>
                            <v-icon>mdi-pencil</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Login</v-list-tile-title>
                    </v-list-tile>
                    <v-list-tile @click="job = true">
                        <v-list-tile-avatar>
                            <v-icon>add</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Anstellung</v-list-tile-title>
                    </v-list-tile>
                </v-list>
            </v-menu>
            <b-update-person-dialog :person-id="value.id"
                                    :show="edit"
                                    @close="() => {edit = false}"
            >
            </b-update-person-dialog>
            <b-detail-add-dialog :parent="value"
                                 :show="add"
                                 @close="() => {add = false}"
                                 @input="$emit('update')"
                                 @show="val => {add = val}"
            >
            </b-detail-add-dialog>
            <b-merge-person-dialog :left-id="value.id"
                                   v-model="merge"
            >
            </b-merge-person-dialog>
            <b-create-job-dialog :employee="value"
                                 v-model="job"
            ></b-create-job-dialog>
            <b-login-edit-dialog :personId="value.id"
                                 v-model="login"
            ></b-login-edit-dialog>
        </template>
    </b-input>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";
    import UpdatePersonDialog from "../../modules/person/dialogs/UpdateDialog.vue";
    import CreateJobDialog from "../../modules/job/dialogs/CreateDialog.vue";
    import DetailAddDialog from "../dialogs/DetailAddDialog.vue";
    import LoginEditDialog from "../../modules/person/dialogs/LoginEditDialog.vue";
    import {Person} from "../../../models";
    import MergePersonDialog from "../../modules/person/dialogs/MergeDialog.vue";
    import CopyToClipboard from "../../../mixins/CopyToClipboard.vue";

    @Component({
        components: {
            'b-update-person-dialog': UpdatePersonDialog,
            'b-create-job-dialog': CreateJobDialog,
            'b-detail-add-dialog': DetailAddDialog,
            'b-merge-person-dialog': MergePersonDialog,
            'b-login-edit-dialog': LoginEditDialog
        },
        mixins: [
            CopyToClipboard
        ]
    })
    export default class PersonIdentifier extends Vue {
        @Prop({type: Object})
        value: Person;

        show: boolean = false;
        edit: boolean = false;
        add: boolean = false;
        merge: boolean = false;
        login: boolean = false;
        job: boolean = false;

        copyToClipboard: Function;

        editPerson() {
            this.edit = true;
        }

        addDetail() {
            this.add = true;
        }
    }
</script>
