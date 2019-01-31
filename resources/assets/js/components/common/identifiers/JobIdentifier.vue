<template>
    <div>
        <app-identifier :value="value.title"></app-identifier>
        bei
        <app-identifier :value="value.employer"></app-identifier>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";

    @Component
    export default class JobIdentifier extends Vue {
        @Prop()
        value;
    }
</script>