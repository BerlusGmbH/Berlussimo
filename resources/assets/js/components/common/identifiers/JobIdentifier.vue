<template>
    <div class="identifier">
        <app-identifier :entity="entity.title"></app-identifier>
        bei
        <app-identifier :entity="entity.employer"></app-identifier>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";

    @Component
    export default class JobIdentifier extends Vue {
        @Prop()
        entity;
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