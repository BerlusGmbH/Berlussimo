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
                                           @input="val => assignmentInput.VERFASSER_ID = val.id"
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
                        <v-text-field label="Text"
                                      prepend-icon="mdi-alphabetical"
                                      v-model="assignmentInput.TEXT"
                                      multi-line
                                      auto-grow
                        >
                        </v-text-field>
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
                <v-btn class="red" @click.native="edit(); $emit('input', false)">Bearbeiten</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import {Assignment} from "../../../server/resources/models";
    import _ from "lodash";
    import {Mutation, namespace} from "vuex-class";

    const SnackbarMutation = namespace('shared/snackbar', Mutation);
    const RefreshMutation = namespace('shared/refresh', Mutation);

    @Component
    export default class AssignmentEditDialog extends Vue {

        @Prop({type: Object})
        assignment: Assignment;

        @Prop({type: Boolean})
        value: boolean;

        @Watch('value')
        onvalueChange(val) {
            if (val && this.assignment) {
                this.assignmentInput = _.cloneDeep(this.assignment);
                this.akut = (this.assignment.AKUT === 'JA');
            }
        }

        @SnackbarMutation('updateMessage')
        updateMessage: Function;

        @RefreshMutation('requestRefresh')
        requestRefresh: Function;

        assignmentInput: Assignment = new Assignment();

        akut: boolean = false;

        edit() {
            if (this.assignmentInput) {
                this.assignmentInput.save().then(() => {
                    this.updateMessage('Auftrag geändert.');
                    this.requestRefresh();
                }).catch(error => {
                    this.$emit('input', true);
                    this.updateMessage('Fehler beim Ändern des Auftrags. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
                });
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
