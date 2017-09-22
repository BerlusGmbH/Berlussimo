<template>
    <div class="identifier" style="display: flex">
        <v-icon class="identifier-icon">{{value.getEntityIcon()}}</v-icon>
        <a style="margin-right: 0.2rem" :href="value.getDetailUrl()" ref="identifier">{{String(value)}}</a>
        <div>
            <v-icon v-if="value.getSexIcon()">{{value.getSexIcon()}}</v-icon>
        </div>
        <app-person-edit-dialog :position-absolutely="true"
                                :show="edit"
                                @show="val => {edit = val}"
                                :position-x="x"
                                :position-y="y"
                                :value="value"
                                @input="savePerson($event); $emit('input', $event)"
        >
        </app-person-edit-dialog>
        <app-detail-add-dialog :position-absolutely="true"
                               :show="add"
                               @show="val => {add = val}"
                               :position-x="x"
                               :position-y="y"
                               :parent="value"
                               @input="saveDetail($event); $emit('update')"
        >
        </app-detail-add-dialog>
        <v-menu offset-y v-model="show" :position-absolutely="true">
            <v-icon slot="activator" style="font-size: inherit">mdi-arrow-down-drop-circle</v-icon>
            <v-list>
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
            </v-list>
        </v-menu>
        <app-merge-dialog v-model="merge" :left="value"></app-merge-dialog>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";
    import personEditDialog from "../dialogs/PersonEditDialog.vue"
    import detailAddDialog from "../dialogs/DetailAddDialog.vue"
    import {Mutation, namespace} from "vuex-class";
    import {Detail, Person} from "../../../server/resources/models";
    import mergeDialog from "../../modules/person/show/Merge.vue";

    const SnackbarMutation = namespace('shared/snackbar', Mutation);
    const RefreshMutation = namespace('shared/refresh', Mutation);

    @Component({
        'components': {
            'app-person-edit-dialog': personEditDialog,
            'app-detail-add-dialog': detailAddDialog,
            'app-merge-dialog': mergeDialog
        }
    })
    export default class PersonIdentifier extends Vue {
        @Prop()
        value;

        @SnackbarMutation('updateMessage')
        updateMessage: Function;

        @RefreshMutation('requestRefresh')
        requestRefresh: Function;

        show: boolean = false;
        edit: boolean = false;
        add: boolean = false;
        merge: boolean = false;

        x: Number = 0;
        y: Number = 0;

        savePerson(person) {
            if (person instanceof Person) {
                person.save().then(() => {
                    this.updateMessage('Person geändert.');
                    this.requestRefresh();
                }).catch((error) => {
                    this.updateMessage('Fehler beim Ändern der Person. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
                });
            }
        }

        saveDetail(detail) {
            if (detail instanceof Detail) {
                detail.create().then(() => {
                    this.updateMessage('Detail hinzugefügt.');
                    this.requestRefresh();
                }).catch((error) => {
                    this.updateMessage('Fehler beim Hinzufügen des Details. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
                });
            }
        }

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

<style>
    .identifier-icon {
        position: absolute;
        left: 0;
    }

    .identifier i {
        font-size: inherit;
    }

    .identifier {
        padding-left: 1.2em;
        position: relative;
        display: inline-block
    }
</style>