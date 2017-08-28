<template>
    <div v-once tabindex="0" class="chip">
        <i class="chip-prefix mdi" :class="entity.icon"></i>
        {{String(entity)}}
        <i class="chip-close material-icons" @click="remove">close</i>
    </div>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";

    @Component
    export default class Chip extends Vue {
        @Prop()
        entity: Object;

        remove() {
            this.$emit('remove');
        }
    }
</script>

<style>
    .chip-close {
        cursor: pointer;
        float: right;
        font-size: 16px;
        line-height: 32px;
        padding-left: 8px;
    }

    .chip-prefix {
        float: left;
        font-size: 16px;
        line-height: 32px;
        padding-right: 8px;
    }
</style>