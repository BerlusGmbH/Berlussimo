<template>
    <v-container grid-list-md fluid :key="key">
    </v-container>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {namespace} from "vuex-class";
    import {Watch} from "vue-property-decorator";

    const Refresh = namespace('shared/refresh');

    @Component
    export default class DetailView extends Vue {

        @Refresh.State('dirty')
        dirty;

        @Refresh.Mutation('refreshFinished')
        refreshFinished: Function;

        @Watch('dirty')
        onDirtyChange() {
            this.refreshFinished();
        }

        get key() {
            return Math.random();
        }
    }
</script>