<template>
    <v-dialog :value="value" @input="$emit('input', $event)" lazy width="1200">
        <v-card>
            <v-card-title class="headline">
                <v-icon>mdi-pencil</v-icon>
                <v-icon>mdi-clipboard</v-icon>
                &nbsp;Auftrag bearbeiten
            </v-card-title>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <app-entity-select label="Von"
                                           :value="assignmentInput.von"
                                           @input="val => {assignmentInput.VERFASSER_ID = val.id; assignmentInput.von = val}"
                                           :entities="['person']"
                                           prepend-icon="mdi-account"
                        >
                        </app-entity-select>
                    </v-flex>
                    <v-flex xs12>
                        <app-entity-select label="An"
                                           :value="assignmentInput.an"
                                           @input="setMorph('BENUTZER', $event)"
                                           :entities="['person', 'partner']"
                                           prepend-icon="mdi-account"
                        >
                        </app-entity-select>
                    </v-flex>
                    <v-flex xs12>
                        <app-entity-select label="Kostenträger"
                                           :value="assignmentInput.kostentraeger"
                                           @input="setMorph('KOS', $event)"
                                           :entities="[
                                               'objekt',
                                               'haus',
                                               'einheit',
                                               'partner',
                                               'person',
                                               'mietvertrag',
                                               'kaufvertrag',
                                               'baustelle',
                                               'wirtschaftseinheit'
                                           ]"
                                           prepend-icon="mdi-account"
                        >
                        </app-entity-select>
                    </v-flex>
                    <v-flex xs12>
                        <v-textarea label="Text"
                                    prepend-icon="mdi-alphabetical"
                                    v-model="assignmentInput.TEXT"
                                    auto-grow
                        >
                        </v-textarea>
                    </v-flex>
                    <v-flex xs12>
                        <v-switch label="Akut"
                                  v-model="akut"
                                  @change="assignmentInput.AKUT = $event ? 'JA' : 'NEIN'"
                        >
                        </v-switch>
                    </v-flex>
                </v-layout>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn flat="flat" @click.native="$emit('input', false)">Abbrechen</v-btn>
                <v-btn class="red" @click.native="create(); $emit('input', false)">Erstellen</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Assignment, Einheit, Haus, Objekt} from "../../../server/resources";
    import {namespace} from "vuex-class";

    const SnackbarModule = namespace('shared/snackbar');
    const RefreshModule = namespace('shared/refresh');
    const AuthModule = namespace('auth');

    @Component
    export default class AssignmentAddDialog extends Vue {

        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        costUnit: Objekt | Haus | Einheit;

        @AuthModule.Getter('user')
        user;

        @SnackbarModule.Mutation('updateMessage')
        updateMessage: Function;

        @RefreshModule.Mutation('requestRefresh')
        requestRefresh: Function;

        assignmentInput: Assignment = new Assignment();

        akut: boolean = false;

        mounted() {
            this.initAssignment()
        }

        create() {
            if (this.assignmentInput) {
                this.assignmentInput.create().then(() => {
                    this.assignmentInput = new Assignment();
                    this.initAssignment();
                    this.updateMessage('Auftrag erstellt.');
                    this.requestRefresh();
                }).catch(error => {
                    this.$emit('input', true);
                    this.updateMessage('Fehler beim Erstellen des Auftrags. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
                });
            }

        }

        initAssignment() {
            this.assignmentInput.VERFASSER_ID = this.user.id;
            this.assignmentInput.von = this.user;
            if (this.costUnit) {
                this.assignmentInput.KOS_ID = this.costUnit.getID();
                this.assignmentInput.KOS_TYP = this.costUnit.getMorphName();
                this.assignmentInput.kostentraeger = this.costUnit;
            }
        }

        setMorph(target, value) {
            if (value) {
                if (target === 'BENUTZER') {
                    this.assignmentInput.an = value;
                } else if (target === 'KOS') {
                    this.assignmentInput.kostentraeger = value;
                }
                this.assignmentInput[target + '_TYP'] = value.getMorphName();
                this.assignmentInput[target + '_ID'] = value.getID();
            }
        }
    }
</script>
