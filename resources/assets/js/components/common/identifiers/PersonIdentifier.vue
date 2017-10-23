<template>
    <div class="identifier" style="display: flex">
        <v-icon class="identifier-icon">{{value.getEntityIcon()}}</v-icon>
        <template v-if="value.hasNotes()">
            <v-icon color="error">mdi-alert</v-icon>&nbsp;
        </template>
        <a style="margin-right: 0.2rem" :href="value.getDetailUrl()" ref="identifier">{{String(value)}}</a>
        <div>
            <v-icon v-if="value.getSexIcon()">{{value.getSexIcon()}}</v-icon>
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
        <app-person-edit-dialog v-if="show || edit"
                                :position-absolutely="true"
                                :show="edit"
                                @show="val => {edit = val}"
                                :position-x="x"
                                :position-y="y"
                                :value="value"
                                @input="$emit('input', $event)"
        >
        </app-person-edit-dialog>
        <app-detail-add-dialog v-if="show || add"
                               :position-absolutely="true"
                               :show="add"
                               @show="val => {add = val}"
                               :position-x="x"
                               :position-y="y"
                               :parent="value"
                               @input="$emit('update')"
        >
        </app-detail-add-dialog>
        <app-person-merge-dialog v-if="show || merge" v-model="merge" :left="value"></app-person-merge-dialog>
        <app-job-add-dialog v-if="show || job" v-model="job" :employee="value"></app-job-add-dialog>
        <app-login-edit-dialog v-if="show || login" v-model="login" :left="value"
                               :person="value"></app-login-edit-dialog>
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
    import {Person} from "../../../server/resources/models";
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