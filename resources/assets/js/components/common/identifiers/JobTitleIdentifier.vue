<template>
    <div class="identifier">
        <v-icon class="identifier-icon">{{entity.getEntityIcon()}}</v-icon>
        String(entity)
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";

    @Component
    export default class PurchaseContractIdentifier extends Vue {
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