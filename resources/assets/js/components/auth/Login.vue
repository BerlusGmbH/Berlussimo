<template>
    <v-container>
        <v-card>
            <v-card-title primary-title><h3 class="display-1 mb-0">Login</h3></v-card-title>
            <v-card-text @keypress.enter="login">
                <v-layout row class="bottom-xs" wrap>
                    <v-flex xs12>
                        <v-text-field type="email" id="email" name="email" prepend-icon="email"
                                      v-model="parameters.email" label="E-Mail Adresse"></v-text-field>
                    </v-flex>
                    <v-flex xs12>
                        <v-text-field type="password" id="password" name="password" label="Passwort"
                                      v-model="parameters.password" prepend-icon="lock"></v-text-field>
                    </v-flex>
                    <v-flex xs6>
                        <v-switch type="checkbox" name="remember" id="remember"
                                  v-model="parameters.remember" label="Angemeldet bleiben"></v-switch>
                    </v-flex>
                    <v-flex xs6 class="text-xs-right">
                        <a href="/password/reset">Password vergessen</a>
                        <v-btn @click.native="login">
                            Anmelden
                        </v-btn>
                    </v-flex>
                </v-layout>
            </v-card-text>
        </v-card>
    </v-container>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';
    import {} from 'vue-property-decorator';
    import axios from 'libraries/axios';

    @Component
    export default class Login extends Vue {
        parameters: {
            email: string;
            password: string;
            remember: boolean;
        } = {
            email: '',
            password: '',
            remember: false
        };

        login() {
            axios.post('/login', this.parameters).then(response => {
                if (200 === response.status) {
                    if (document.referrer) {
                        let previous = new URL(document.referrer);
                        if (previous.hostname === window.location.hostname) {
                            window.history.back();
                        } else {
                            window.location.assign('/');
                        }
                    } else {
                        window.location.assign('/');
                    }
                }
            });
        }
    }
</script>