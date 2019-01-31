<template>
    <v-list>
        <v-list-tile @click="onLogout">
            <v-list-tile-avatar>
                <v-icon>mdi-logout</v-icon>
            </v-list-tile-avatar>
            <v-list-tile-title>Abmelden</v-list-tile-title>
        </v-list-tile>
    </v-list>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';
    import LogoutMutation from '../auth/LogoutMutation.graphql';
    import LegacyQuery from './LegacyQuery.graphql';

    @Component({
        apollo: {
            state: LegacyQuery
        }
    })
    export default class UserMenuList extends Vue {
        state;

        onLogout() {
            if (!this.state.isLegcy) {
                let vm = this;
                vm.$apollo.mutate({
                    mutation: LogoutMutation
                }).then(() => {
                    vm.$router.push({
                        name: 'web.login'
                    }).catch(_err => {
                    });
                    vm.$nextTick(() => {
                        vm.$apollo.provider.defaultClient.resetStore();
                    });
                });
            } else {
                this.$apollo.mutate({
                    mutation: LogoutMutation
                }).then(() => {
                    window.location.assign('/login');
                });
            }
        }
    }
</script>
