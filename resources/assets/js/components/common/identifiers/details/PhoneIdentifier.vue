<template>
    <div class="identifier">
        <span style="padding-right: 10px">
            <v-icon class="identifier-icon">mdi-phone</v-icon>
            {{entity.DETAIL_INHALT}}
        </span>
        <span v-if="entity.DETAIL_BEMERKUNG">
            <v-icon>mdi-note</v-icon>
            {{entity.DETAIL_BEMERKUNG}}
        </span>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";
    import {Detail} from "../../../../server/resources";

    @Component
    export default class PhoneIdentifier extends Vue {
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