<template>
    <v-progress-linear
        :active="loading"
        background-opacity="0"
        color="primary"
        height="3"
        indeterminate
        style="margin-top: 1px; margin-bottom: 0"
    ></v-progress-linear>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';
    import UserQuery from '../auth/UserQuery.graphql';

    @Component({
        apollo: {
            state: {
                query: UserQuery,
                update(data) {
                    return data.state;
                }
            }
        }
    })
    export default class LoadingBar extends Vue {
        state: any = {};

        load: number = 0;

        mounted() {
            // todo: ugly hack to transfer global loading state into component
            // without this the last update is not reflected in the component
            // leading to an load forever situation
            const vm = this;
            this.load = (this.$apolloProvider as any).watchLoading(null, 0);
            const watchLoading = (this.$apolloProvider as any).watchLoading;
            (this.$apolloProvider as any).watchLoading = function (isLoading, countModifier) {
                vm.load += countModifier;
                watchLoading(isLoading, countModifier);
            };
        }

        get loading() {
            return !!this.load;
        }

        get user() {
            return this.state && this.state.user ? this.state.user : null;
        }
    }
</script>

<style>
    .extension-panel__menu a {
        color: inherit;
    }

    .extension-panel__menu .v-expansion-panel__header {
        min-height: 48px;
        height: initial;
        padding: 0 0 0 24px;
    }

    .extension-panel__menu .expansion-panel__header .chip i {
        margin-right: 0;
    }
</style>
