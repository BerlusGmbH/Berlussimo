<template>
    <div style="display: inherit; width: 100%; height: 100%">
        <v-list-tile-avatar>
            <v-icon>{{entity.getEntityIcon()}}</v-icon>
        </v-list-tile-avatar>
        <v-list-tile-content>
            <v-list-tile-title>{{String(entity)}}
                <v-icon style="font-size:inherit">{{entity.getSexIcon()}}</v-icon>
            </v-list-tile-title>
            <v-list-tile-sub-title>{{entity.birthday}}</v-list-tile-sub-title>
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
    export default class PersonTile extends Vue {
        @Prop()
        entity;

        onInfoClick() {
            window.open(this.entity.getDetailUrl(), '_new');
        }
    }
</script>