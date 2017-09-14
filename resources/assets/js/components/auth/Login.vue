<template>
    <v-container>
        <v-card>
            <v-card-title primary-title><h3 class="display-1 mb-0">Login</h3></v-card-title>
            <v-card-text>
                <form role="form" method="POST" action="/login">
                    <slot name="csrf_token"></slot>
                    <v-layout row class="bottom-xs" wrap>
                        <v-flex xs12>
                            <v-text-field type="email" id="email" name="email" prepend-icon="email"
                                          v-model="email" label="E-Mail Adresse"></v-text-field>
                        </v-flex>
                        <v-flex xs12>
                            <v-text-field type="password" id="password" name="password" label="Passwort"
                                          v-model="password" prepend-icon="lock"></v-text-field>
                        </v-flex>
                        <v-flex xs6>
                            <v-switch type="checkbox" name="remember" id="remember"
                                      v-model="remember" label="Angemeldet bleiben"></v-switch>
                        </v-flex>
                        <v-flex xs6 class="text-xs-right">
                            <a href="/password/reset">Password vergessen</a>
                            <v-btn type="submit">
                                Anmelden
                            </v-btn>
                        </v-flex>
                    </v-layout>
                </form>
            </v-card-text>
        </v-card>
    </v-container>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';
    import {} from 'vue-property-decorator';

    @Component
    export default class Login extends Vue {
        email: string = '';
        password: string = '';
        remember: boolean = false;
    }
</script>