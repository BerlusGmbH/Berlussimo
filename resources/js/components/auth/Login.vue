<template>
    <v-container>
        <v-card>
            <v-card-title primary-title><h3 class="display-1 mb-0">Login</h3></v-card-title>
            <v-card-text @keypress.enter="login">
                <v-form ref="form">
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-text-field :disabled="loading"
                                          :error-messages="errorMessages.username"
                                          id="email"
                                          label="E-Mail Adresse"
                                          name="email"
                                          prepend-icon="email"
                                          type="email"
                                          v-model="parameters.username"
                            ></v-text-field>
                        </v-flex>
                        <v-flex xs12>
                            <v-text-field :disabled="loading"
                                          :error-messages="errorMessages.password"
                                          id="password"
                                          label="Passwort"
                                          name="password"
                                          prepend-icon="lock"
                                          type="password"
                                          v-model="parameters.password"
                            ></v-text-field>
                        </v-flex>
                        <v-flex class="text-xs-right" xs12>
                            <v-btn :loading="loading"
                                   @click.native="login"
                            >
                                Anmelden
                            </v-btn>
                        </v-flex>
                    </v-layout>
                </v-form>
            </v-card-text>
        </v-card>
    </v-container>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';
    import LoginMutation from './LoginMutation.graphql';
    import DisplaysErrors from '../../mixins/DisplaysErrors.vue';
    import {ErrorMessages} from '../../mixins';

    @Component({
        mixins: [DisplaysErrors]
    })
    export default class Login extends Vue {
        parameters: {
            username: string,
            password: string
        } = {
            username: '',
            password: ''
        };

        errorMessages: ErrorMessages;
        extractErrorMessages: Function;

        loading: boolean = false;

        login() {
            this.loading = true;
            this.$apollo.mutate({
                mutation: LoginMutation,
                variables: {
                    ...this.parameters
                }
            }).then(() => {
                this.$router.replace({
                    name: 'web.dashboard.show'
                }).catch(_err => {
                });
                this.loading = false;
            }).catch(exception => {
                this.loading = false;
                this.extractErrorMessages(exception);
            });
        }
    }
</script>
