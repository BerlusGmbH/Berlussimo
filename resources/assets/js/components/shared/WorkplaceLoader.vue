<template></template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Mutation, namespace} from "vuex-class";

    const WorkplaceMutation = namespace('shared/workplace', Mutation);

    @Component
    export default class WorkplaceLoader extends Vue {

        @WorkplaceMutation('updateHasPhone')
        updateHasPhone: Function;

        @Prop({type: Boolean})
        hasPhone: Boolean;

        mounted() {
            if (this.hasPhone) {
                this.updateHasPhone(this.hasPhone);
            }
        }
    }
</script>