<template>
    <div class="identifier">
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

<style>
    .identifier-icon {
        position: absolute;
        left: 0;
    }

    .identifier i {
        font-size: inherit;
    }

    .identifier {
        padding-left: 1.2em;
        position: relative;
        display: inline-block
    }
</style>