<template></template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {namespace} from "vuex-class";

    const LegacyModule = namespace('shared/legacy');

    @Component
    export default class LegacyLoader extends Vue {

        @LegacyModule.Mutation('updateIsLegacy')
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