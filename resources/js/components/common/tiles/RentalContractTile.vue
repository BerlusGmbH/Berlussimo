<template>
    <div style="display: inherit; width: 100%; height: 100%">
        <v-list-tile-avatar>
            <v-icon>{{entity.getEntityIcon()}}</v-icon>
        </v-list-tile-avatar>
        <v-list-tile-content>
            <v-list-tile-title>{{String(entity)}}</v-list-tile-title>
            <v-list-tile-sub-title>
                <template v-if="entity.unit">
                    <v-icon style="font-size: inherit">{{entity.unit.getEntityIcon()}}
                    </v-icon
                    >
                    <v-icon style="font-size: inherit">{{entity.unit.getKindIcon()}}</v-icon>
                    {{String(entity.unit)}}
                </template>
                <template v-for="tenant in entity.tenants" v-if="entity.tenants">
                    <v-icon style="font-size: inherit">{{tenant.getEntityIcon()}}</v-icon>
                    {{String(tenant)}}
                </template>
                <v-icon style="font-size: inherit">mdi-calendar</v-icon>
                {{entity.start}}
                <template v-if="entity.end !== '0000-00-00'">
                    - {{entity.end}}
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

    @Component
    export default class RentalContractTile extends Vue {
        @Prop()
        entity;

        onInfoClick() {
            window.open(this.entity.getDetailUrl(), '_new');
        }
    }
</script>