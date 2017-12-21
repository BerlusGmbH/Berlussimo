<template>
    <div style="display: inherit; width: 100%; height: 100%">
        <v-list-tile-avatar>
            <v-icon>{{entity.getEntityIcon()}}</v-icon>
        </v-list-tile-avatar>
        <v-list-tile-content>
            <v-list-tile-title>
                <b>{{entity.ARTIKEL_NR}}</b>
                <v-icon style="font-size: inherit; vertical-align: baseline">mdi-note</v-icon>
                {{String(entity)}}
                <v-icon style="font-size: inherit; vertical-align: baseline">{{entity.supplier.getEntityIcon()}}
                </v-icon>
                {{String(entity.supplier)}}
            </v-list-tile-title>
            <v-list-tile-sub-title>
                <v-icon style="font-size: inherit; vertical-align: baseline">mdi-currency-eur</v-icon>
                :
                <b>{{entity.LISTENPREIS}}</b>€,
                <v-icon style="font-size: inherit; vertical-align: baseline">mdi-ruler</v-icon>
                :
                <b>{{entity.EINHEIT}}</b>,
                MwSt.: <b>{{entity.MWST_SATZ}}</b>%,
                Rabatt: <b>{{entity.RABATT_SATZ}}</b>%,
                Skonto: <b>{{entity.SKONTO}}</b>%
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
    export default class InvoiceItemTile extends Vue {
        @Prop()
        entity;

        onInfoClick() {
            window.open(this.entity.getDetailUrl(), '_new');
        }
    }
</script>