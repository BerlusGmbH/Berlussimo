<template>
    <div class="identifier">
        <v-icon class="identifier-icon">{{icon}}</v-icon>
        <a :href="entity.getDetailUrl()">{{String(entity)}}</a>
        <v-icon v-if="entity.getSexIcon()">{{entity.getSexIcon()}}</v-icon>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";
    import {Person} from "../../../server/resources/models";

    @Component
    export default class PersonIdentifier extends Vue {
        @Prop()
        entity;

        get icon() {
            return Person.getEntityIcon();
        }
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