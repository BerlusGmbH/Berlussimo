<template>
    <div class="identifier">
        <div v-if="value.hasNotes()">
            <b-icon :tooltips="value.getNoteTooltips()" color="error">mdi-alert</b-icon>
        </div>
        <div ref="identifier">
            <b-icon :tooltips="value.getEntityIconTooltips()">{{value.getEntityIcon()}}</b-icon>
        </div>
        <router-link v-if="$router" :to="{name: 'web.persons.show', params: { id: String(value.getID()) }}"
        >
            {{String(value)}}
        </router-link>
        <a v-else :href="value.getDetailUrl()">{{String(value)}}</a>
        <div>
            <b-icon v-if="value.getSexIcon()" :tooltip="value.sex">{{value.getSexIcon()}}</b-icon>
        </div>
        <v-menu offset-y v-model="show" :position-absolutely="true">
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
        <b-person-edit-dialog v-if="show || edit"
                              :position-absolutely="true"
                              :show="edit"
                              @show="val => {edit = val}"
                              :position-x="x"
                              :position-y="y"
                              :value="value"
                              @input="$emit('input', $event)"
        >
        </b-person-edit-dialog>
        <b-detail-add-dialog v-if="show || add"
                               :position-absolutely="true"
                               :show="add"
                               @show="val => {add = val}"
                               :position-x="x"
                               :position-y="y"
                               :parent="value"
                               @input="$emit('update')"
        >
        </b-detail-add-dialog>
        <b-person-merge-dialog v-if="show || merge" v-model="merge" :left="value"></b-person-merge-dialog>
        <b-job-add-dialog v-if="show || job" v-model="job" :employee="value"></b-job-add-dialog>
        <b-login-edit-dialog v-if="show || login" v-model="login" :left="value"
                             :person="value"></b-login-edit-dialog>
    </div>
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
            'b-person-edit-dialog': personEditDialog,
            'b-job-add-dialog': jobAddDialog,
            'b-detail-add-dialog': detailAddDialog,
            'b-person-merge-dialog': personMergeDialog,
            'b-login-edit-dialog': loginEditDialog
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

        x: Number = 0;
        y: Number = 0;

        copyToClipboard: Function;

        editPerson() {
            this.edit = true;
            this.x = this.$refs.identifier ? (this.$refs.identifier as HTMLElement).getBoundingClientRect().left - 20 : this.x;
            this.y = this.$refs.identifier ? (this.$refs.identifier as HTMLElement).getBoundingClientRect().top - 20 : this.y;
        }

        addDetail() {
            this.add = true;
            this.x = this.$refs.identifier ? (this.$refs.identifier as HTMLElement).getBoundingClientRect().left - 20 : this.x;
            this.y = this.$refs.identifier ? (this.$refs.identifier as HTMLElement).getBoundingClientRect().top - 20 : this.y;
        }
    }
</script>