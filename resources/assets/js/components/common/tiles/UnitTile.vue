<template>
    <div style="display: inherit; width: 100%; height: 100%">
        <v-list-tile-avatar>
            <v-icon>{{entity.getEntityIcon()}}</v-icon>
        </v-list-tile-avatar>
        <v-list-tile-content>
            <v-list-tile-title>{{String(entity)}}</v-list-tile-title>
            <v-list-tile-sub-title style="white-space: nowrap">
                <v-icon style="font-size: inherit">mdi-compass</v-icon>
                {{entity.EINHEIT_LAGE}}
                <v-icon style="font-size: inherit">mdi-arrow-expand-all</v-icon>
                {{entity.EINHEIT_QM}} m²
            </v-list-tile-sub-title>
        </v-list-tile-content>
        <v-list-tile-action>
            <v-btn icon @click.stop="onInfoClick">
                <v-icon>info</v-icon>
            </v-btn>
        </v-list-tile-action>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";

    @Component
    export default class UnitTile extends Vue {
        @Prop()
        entity;

        onInfoClick() {
            window.open(this.entity.getDetailUrl(), '_new');
        }
    }
</script>