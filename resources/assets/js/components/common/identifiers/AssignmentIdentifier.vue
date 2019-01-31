<template>
    <b-input hide-details>
        <b-icon :tooltips="value.getEntityIconTooltips()" class="identifier-icon" slot="prepend">
            {{value.getEntityIcon()}}
        </b-icon>
        {{String(value)}}
        <template slot="append">
            <v-menu :position-absolutely="true" offset-y v-model="show">
                <v-icon slot="activator" style="font-size: inherit; vertical-align: baseline">mdi-arrow-down-drop-circle
                </v-icon>
                <v-list>
                    <v-list-tile @click="showAssignmentPDF">
                        <v-list-tile-avatar>
                            <v-icon>mdi-file-pdf</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>PDF</v-list-tile-title>
                    </v-list-tile>
                    <v-list-tile @click="toogleMarkAsDone">
                        <v-list-tile-avatar>
                            <v-icon v-if="value.ERLEDIGT === '0'">mdi-checkbox-blank-outline</v-icon>
                            <v-icon v-else="value.ERLEDIGT === '1'">mdi-checkbox-marked</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Erledigt</v-list-tile-title>
                    </v-list-tile>
                    <v-divider></v-divider>
                    <v-list-tile @click="copyToClipboard(String(value), 'Auftragsname')">
                        <v-list-tile-avatar>
                            <v-icon>mdi-content-copy</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Kopieren</v-list-tile-title>
                    </v-list-tile>
                    <v-list-tile @click="editAssignment">
                        <v-list-tile-avatar>
                            <v-icon>edit</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Bearbeiten</v-list-tile-title>
                    </v-list-tile>
                </v-list>
            </v-menu>
            <app-assignment-edit-dialog :assignment="value"
                                        v-if="show || edit"
                                        v-model="edit"
            >
            </app-assignment-edit-dialog>
        </template>
    </b-input>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";
    import copyToClipboard from "../../../mixins/CopyToClipboard.vue";
    import assignmentEditDialog from "../dialogs/AssignmentEditDialog.vue";
    import {namespace} from "vuex-class";
    import _ from "lodash";
    import {Assignment} from "../../../server/resources";

    const Refresh = namespace('shared/refresh');
    const Snackbar = namespace('shared/snackbar');

    @Component({
        'components': {
            'app-assignment-edit-dialog': assignmentEditDialog
        },
        'mixins': [
            copyToClipboard
        ]
    })
    export default class AssignmentIdentifier extends Vue {
        @Prop()
        value: Assignment;

        @Refresh.Mutation('requestRefresh')
        requestRefresh: Function;

        @Snackbar.Mutation('updateMessage')
        updateMessage: Function;

        show: boolean = false;
        edit: boolean = false;

        editAssignment() {
            this.edit = true;
        }

        toogleMarkAsDone() {
            let value: Assignment = _.cloneDeep(this.value);
            value.ERLEDIGT = value.ERLEDIGT === '1' ? '0' : '1';
            value.save().then(() => {
                this.requestRefresh();
                if (value.ERLEDIGT === '0') {
                    this.updateMessage('Auftrag als nicht erledigt markiert.');
                } else if (value.ERLEDIGT === '1') {
                    this.updateMessage('Auftrag als erledigt markiert.');
                }

            }).catch(error => {
                this.updateMessage('Fehler beim Markieren des Auftrags. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
            });
        }

        showAssignmentPDF() {
            window.open('/baustellen?option=pdf_auftrag&proj_id=' + this.value.T_ID)
        }
    }
</script>