<template>
    <b-input hide-details>
        <b-icon :tooltips="value.getEntityIconTooltips()" class="identifier-icon" slot="prepend">
            {{value.getEntityIcon()}}
        </b-icon>
        <span
            style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap">{{String(value)}}</span>
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
                            <v-icon v-if="!value.done">mdi-checkbox-blank-outline</v-icon>
                            <v-icon v-else>mdi-checkbox-marked</v-icon>
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
            <b-update-assignment-dialog :assignment="value"
                                        v-if="show || edit"
                                        v-model="edit"
            >
            </b-update-assignment-dialog>
        </template>
    </b-input>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";
    import CopyToClipboard from "../../../mixins/CopyToClipboard.vue";
    import UpdateAssignmentDialog from "../../modules/assignment/dialogs/UpdateDialog.vue";
    import {Assignment} from "../../../models";
    import UpdateAssignmentMutation from "../../../components/modules/assignment/dialogs/UpdateMutation.graphql";
    import {DisplaysMessagesContract} from "../../../mixins";
    import DisplaysMessages from "../../../mixins/DisplaysMessages.vue";
    import {FetchResult} from "apollo-link";

    @Component({
        'components': {
            'b-update-assignment-dialog': UpdateAssignmentDialog
        },
        'mixins': [
            CopyToClipboard,
            DisplaysMessages
        ]
    })
    export default class AssignmentIdentifier extends Vue implements DisplaysMessagesContract {
        @Prop()
        value: Assignment;

        show: boolean = false;
        edit: boolean = false;

        showMessage: <R = any>(message: string) => Promise<FetchResult<R>>;

        editAssignment() {
            this.edit = true;
        }

        toogleMarkAsDone() {
            this.$apollo.mutate({
                mutation: UpdateAssignmentMutation,
                variables: {
                    input: {
                        id: this.value.id,
                        done: !this.value.done
                    }
                }
            }).then((result) => {
                if (result.data.updateAssignment.done) {
                    this.showMessage('Auftrag als erledigt markiert.');
                } else {
                    this.showMessage('Auftrag als nicht erledigt markiert.');
                }
            }).catch((error) => {
                this.showMessage('Fehler beim Markieren des Auftrags. Nachricht: ' + error.message);
            })
        }

        showAssignmentPDF() {
            window.open('/baustellen?option=pdf_auftrag&proj_id=' + this.value.id)
        }
    }
</script>
