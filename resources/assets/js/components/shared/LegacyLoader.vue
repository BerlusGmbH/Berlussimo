<template></template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {namespace} from "vuex-class";

    const Legacy = namespace('shared/legacy');

    @Component
    export default class LegacyLoader extends Vue {

        @Legacy.Mutation('updateIsLegacy')
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