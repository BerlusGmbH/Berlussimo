<template>
    <div class="identifier">
        <v-icon class="identifier-icon">mdi-fax</v-icon>
        {{entity.DETAIL_INHALT}}
        <span style="margin-left: 10px" v-if="entity.DETAIL_BEMERKUNG">
            <v-icon>mdi-note</v-icon>
            {{entity.DETAIL_BEMERKUNG}}
        </span>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";
    import {Detail} from "../../../../server/resources/index";

    @Component
    export default class FaxIdentifier extends Vue {
        @Prop()
        entity: Detail;
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