<template>
    <div style="display: inherit; width: 100%; height: 100%">
        <v-list-tile-avatar>
            <v-icon>{{entity.getEntityIcon()}}</v-icon>
        </v-list-tile-avatar>
        <v-list-tile-content>
            <v-list-tile-title>{{String(entity)}}</v-list-tile-title>
            <v-list-tile-sub-title>
                <template v-if="entity.einheit">
                    <v-icon style="font-size: inherit">{{entity.einheit.getEntityIcon()}}
                    </v-icon
                    >
                    <v-icon style="font-size: inherit">{{entity.einheit.getKindIcon()}}</v-icon>
                    {{String(entity.einheit)}}
                </template>
                <template v-if="entity.eigentuemer" v-for="owner in entity.eigentuemer">
                    <v-icon style="font-size: inherit">{{owner.getEntityIcon()}}</v-icon>
                    {{String(owner)}}
                </template>
                <v-icon style="font-size: inherit">mdi-calendar</v-icon>
                {{entity.VON}}
                <template v-if="entity.BIS !== '0000-00-00'">
                    - {{entity.BIS}}
                </template>
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
    export default class PurchaseContractTile extends Vue {
        @Prop()
        entity;

        onInfoClick() {
            window.open(this.entity.getDetailUrl(), '_new');
        }
    }
</script>