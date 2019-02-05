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
        <router-link v-if="$router" :to="{name: 'web.persons.show', params: { id: String(value.getID()) }}"
        >
            {{String(value)}}
        </router-link>
        <a v-else :href="value.getDetailUrl()">{{String(value)}}</a>
        <template slot="append">
            <div>
                <b-icon :tooltip="value.sex" v-if="value.getSexIcon()">{{value.getSexIcon()}}</b-icon>
            </div>
            <v-menu :position-absolutely="true" offset-y v-model="show">
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
            <app-person-edit-dialog :show="edit"
                                    :value="value"
                                    @input="$emit('input', $event)"
                                    @show="val => {edit = val}"
                                    v-if="show || edit"
            >
            </app-person-edit-dialog>
            <app-detail-add-dialog :parent="value"
                                   :show="add"
                                   @input="$emit('update')"
                                   @show="val => {add = val}"
                                   v-if="show || add"
            >
            </app-detail-add-dialog>
            <app-person-merge-dialog :left="value" v-if="show || merge" v-model="merge"></app-person-merge-dialog>
            <app-job-add-dialog :employee="value" v-if="show || job" v-model="job"></app-job-add-dialog>
            <app-login-edit-dialog :left="value" :person="value" v-if="show || login"
                                   v-model="login"></app-login-edit-dialog>
        </template>
    </b-input>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";
    import personEditDialog from "../dialogs/PersonEditDialog.vue";
    import jobAddDialog from "../dialogs/JobAddDialog.vue";
    import detailAddDialog from "../dialogs/DetailAddDialog.vue";
    import loginEditDialog from "../dialogs/LoginEditDialog.vue";
    import {Person} from "../../../server/resources";
    import personMergeDialog from "../dialogs/PersonMergeDialog.vue";
    import copyToClipboard from "../../../mixins/CopyToClipboard.vue";

    @Component({
        'components': {
            'app-person-edit-dialog': personEditDialog,
            'app-job-add-dialog': jobAddDialog,
            'app-detail-add-dialog': detailAddDialog,
            'app-person-merge-dialog': personMergeDialog,
            'app-login-edit-dialog': loginEditDialog
        },
        'mixins': [
            copyToClipboard
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