<template>
    <v-dialog v-model="show" @input="$emit('input', $event)" lazy width="540">
        <v-card>
            <v-card-title class="headline">Login ändern</v-card-title>
            <v-card-text>
                <v-container grid-list-sm>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-text-field v-model="password"
                                          type="password"
                                          label="Passwort"
                                          prepend-icon="mdi-lock"
                            ></v-text-field>
                            <v-switch v-model="active" label="Aktiv"></v-switch>
                        </v-flex>
                        <v-flex xs12>
                            <v-data-table :headers="headers"
                                          :items="allRoles"
                            >
                                <template slot="items" scope="props">
                                    <td>
                                        <v-checkbox
                                                primary
                                                hide-details
                                                v-model="roles"
                                                :value="props.item.name"
                                        ></v-checkbox>
                                    </td>
                                    <td>{{ props.item.name }}</td>
                                </template>
                            </v-data-table>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn flat="flat" @click.native="show = false; $emit('input', false)">Abbrechen</v-btn>
                <v-btn class="red" @click.native="editLogin">Ändern</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import EntitySelect from '../../common/EntitySelect.vue';
    import {Person} from "../../../server/resources/models";
    import axios from "../../../libraries/axios";
    import {Mutation, namespace} from "vuex-class";

    const SnackbarMutation = namespace('shared/snackbar', Mutation);
    const RefreshMutation = namespace('shared/refresh', Mutation);

    @Component({
        components: {
            'app-entity-select': EntitySelect
        }
    })
    export default class LoginEditDialog extends Vue {

        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Object})
        person: Person;

        @SnackbarMutation('updateMessage')
        updateMessage: Function;

        @RefreshMutation('requestRefresh')
        requestRefresh: Function;

        show: boolean = false;

        password: String = '';

        active: boolean = false;

        allRoles: Array<Object> = [];

        roles: Array<Object> = [];

        headers = [
            {text: '', value: '', sortable: false},
            {text: 'Rolle', value: 'name'}
        ];

        @Watch('value')
        onValueChange(val) {
            if (val) {
                this.loadRoles();
            }
        }

        loadRoles() {
            if (this.person) {
                axios.all([
                    axios.get('/api/v1/roles').then((response) => {
                        this.allRoles = response.data;
                    }),
                    axios.get('/api/v1/persons/' + this.person.id + '/roles').then((response) => {
                        this.roles = response.data.map((v) => v.name);
                    }),
                    axios.get('/api/v1/persons/' + this.person.id + '/credential').then((response) => {
                        this.active = response.data;
                    }),
                ]).then(() => {
                    this.show = true;
                })
            }
        }

        editLogin() {
            if (this.person) {
                axios.post('/api/v1/persons/' + this.person.id + '/credential', {
                    'active': this.active,
                    'roles': this.roles,
                    'password': this.password
                }).then(() => {
                    this.$emit('input', false);
                    this.show = false;
                    this.updateMessage('Login geändert.');
                    this.requestRefresh();
                }).catch((error) => {
                    this.updateMessage('Fehler beim Ändern des Logins. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
                });
            }
        }
    }
</script>
