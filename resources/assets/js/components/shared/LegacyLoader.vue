<template></template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Mutation, namespace} from "vuex-class";

    const LegacyMutation = namespace('shared/legacy', Mutation);

    @Component
    export default class LegacyLoader extends Vue {

        @LegacyMutation('updateIsLegacy')
        updateIsLegacy: Function;

        @Prop({type: Boolean})
        isLegacy: Boolean;

        mounted() {
            if (this.isLegacy) {
                this.updateIsLegacy(this.isLegacy);
            }
        }
    }
</script>