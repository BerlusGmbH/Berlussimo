<template>
    <div style="display: inherit; width: 100%; height: 100%">
        <v-list-tile-avatar>
            <v-icon>{{entity.getEntityIcon()}}</v-icon>
        </v-list-tile-avatar>
        <v-list-tile-content>
            <v-list-tile-title>{{String(entity)}}</v-list-tile-title>
            <v-list-tile-sub-title style="white-space: nowrap">
                <v-tooltip bottom>
                    <template v-slot:activator="{ on }">
                        <v-icon style="font-size: inherit" v-on="on">mdi-compass</v-icon>
                    </template>
                    <span>Lage</span>
                </v-tooltip>
                {{entity.location}}
                <v-tooltip bottom>
                    <template v-slot:activator="{ on }">
                        <v-icon style="font-size: inherit" v-on="on">mdi-arrow-expand-all</v-icon>
                    </template>
                    <span>Fläche</span>
                </v-tooltip>
                {{entity.size}} m²
                <template v-if="entity.movingOut && entity.movingOut.length > 0">
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <v-icon style="font-size: inherit" v-on="on">mdi-arrow-up-bold-circle-outline</v-icon>
                        </template>
                        <span>Nächster Auszug</span>
                    </v-tooltip>
                    {{entity.movingOut[0].end}}
                </template>
                <template v-if="entity.movingIn && entity.movingIn.length > 0">
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <v-icon style="font-size: inherit" v-on="on">mdi-arrow-down-bold-circle</v-icon>
                        </template>
                        <span>Nächster Einzug</span>
                    </v-tooltip>
                    {{entity.movingIn[0].start}}
                </template>
            </v-list-tile-sub-title>
        </v-list-tile-content>
        <v-list-tile-action>
            <v-btn @click.stop="onInfoClick" icon>
                <v-icon>info</v-icon>
            </v-btn>
        </v-list-tile-action>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";
    import {Unit} from "../../../models";

    @Component
    export default class UnitTile extends Vue {
        @Prop()
        entity: Unit;

        onInfoClick() {
            window.open(this.entity.getDetailUrl(), '_new');
        }
    }
</script>
