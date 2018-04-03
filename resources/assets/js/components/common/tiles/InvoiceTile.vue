<template>
    <div style="display: inherit; width: 100%; height: 100%">
        <v-list-tile-avatar>
            <b-icon :tooltips="entity.getEntityIconTooltips()">{{entity.getEntityIcon()}}</b-icon>
        </v-list-tile-avatar>
        <v-list-tile-content>
            <v-list-tile-title>
                <b>{{String(entity)}}</b>
                <v-icon style="font-size: inherit; vertical-align: baseline">mdi-note</v-icon>
                {{entity.KURZBESCHREIBUNG|sformat}}
            </v-list-tile-title>
            <v-list-tile-sub-title>
                Netto:
                <b>{{entity.NETTO|nformat}}</b>€,
                Brutto:
                <b>{{entity.BRUTTO|nformat}}</b>€,
                Skonto: <b>{{entity.SKONTOBETRAG|nformat}}</b>€
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
    export default class InvoiceTile extends Vue {
        @Prop()
        entity;

        onInfoClick() {
            window.open(this.entity.getDetailUrl(), '_new');
        }
    }
</script>