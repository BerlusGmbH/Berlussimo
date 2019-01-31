<template>
    <v-dialog @input="$emit('input', $event)" lazy v-model="show" width="540">
        <v-card>
            <v-card-title class="headline">Login ändern</v-card-title>
            <v-card-text>
                <v-container>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-text-field :disabled="loading"
                                          label="Passwort"
                                          prepend-icon="mdi-lock"
                                          type="password"
                                          v-model="password"
                            ></v-text-field>
                            <v-switch :disabled="loading"
                                      label="Aktiv"
                                      v-model="active"
                            ></v-switch>
                        </v-flex>
                        <v-flex xs12>
                            <v-data-table :headers="headers"
                                          :items="allRoles"
                            >
                                <template slot="items" slot-scope="props">
                                    <td>
                                        <v-checkbox
                                                :disabled="loading"
                                                :value="props.item.name"
                                                hide-details
                                                primary
                                                v-model="roles"
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
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-progress-linear :active="loading"
                                           :query="$apollo.loading"
                                           indeterminate
                        ></v-progress-linear>
                    </v-flex>
                    <v-flex class="text-xs-right" xs12>
                        <v-btn @click.native="show = false; $emit('input', false)"
                               flat="flat"
                        >Abbrechen
                        </v-btn>
                        <v-btn :disabled="loading"
                               @click.native="editLogin"
                               class="red"
                        >Ändern
                        </v-btn>
                    </v-flex>
                </v-layout>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import EntitySelect from '../../../common/EntitySelect.vue';
    import RolesQuery from "./RolesQuery.graphql";
    import AllRolesQuery from "./AllRolesQuery.graphql";
    import CredentialQuery from "./CredentialQuery.graphql";
    import SyncRolesMutation from "./SyncRolesMutation.graphql";
    import UpdateCredentialMutation from "./UpdateCredentialMutation.graphql";
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import {ErrorMessages} from "../../../../mixins";

    @Component({
        components: {
            'app-entity-select': EntitySelect
        },
        mixins: [
            DisplaysErrors, DisplaysMessages
        ],
        apollo: {
            personRoles: {
                query: RolesQuery,
                variables(this: LoginEditDialog) {
                    return {
                        personId: this.personId
                    }
                },
                update(this: LoginEditDialog, data) {
                    this.roles = data.person.roles.map(v => v.name);
                },
                skip: true
            },
            allRoles: {
                query: AllRolesQuery,
                skip: true
            },
            credential: {
                query: CredentialQuery,
                variables(this: LoginEditDialog) {
                    return {
                        personId: this.personId
                    }
                },
                update(this: LoginEditDialog, data) {
                    this.active = data.person.credential.enabled;
                },
                skip: true
            },
        }
    })
    export default class LoginEditDialog extends Vue {

        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: String})
        personId: String;

        show: boolean = false;

        password: String = '';

        active: boolean = false;

        allRoles: Object[] = [];

        roles: Object[] = [];

        migrating: boolean = false;

        errorMessages: ErrorMessages;

        showMessage: Function;

        headers = [
            {text: '', value: '', sortable: false},
            {text: 'Rolle', value: 'name'}
        ];

        @Watch('value')
        onValueChange(val) {
            if (val) {
                this.show = true;
                this.$apollo.skipAll = false;
                this.$apollo.queries.personRoles.refetch();
                this.$apollo.queries.allRoles.refetch();
                this.$apollo.queries.credential.refetch();
            }
        }

        get loading() {
            return this.$apollo.loading || this.migrating
        }

        editLogin() {
            if (this.personId) {
                this.migrating = true;
                Promise.all([
                    this.$apollo.mutate({
                        mutation: UpdateCredentialMutation,
                        variables: {
                            personId: this.personId,
                            enabled: this.active,
                            password: this.password
                        },
                    }),
                    this.$apollo.mutate({
                        mutation: SyncRolesMutation,
                        variables: {
                            personId: this.personId,
                            roles: this.roles
                        }
                    }),
                ]).then(() => {
                    this.$emit('input', false);
                    this.show = false;
                    this.migrating = false;
                    this.showMessage('Login geändert.');
                }).catch((error) => {
                    this.migrating = false;
                    this.showMessage(
                        'Fehler beim Ändern des Logins. Nachricht: ' + error.message
                    );
                });

            }
        }
    }
</script>
