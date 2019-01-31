<template>
    <div style="display: inherit; width: 100%; height: 100%">
        <v-list-tile-avatar>
            <v-icon>{{entity.getEntityIcon()}}</v-icon>
        </v-list-tile-avatar>
        <v-list-tile-content>
            <v-list-tile-title>
                <b>{{entity.itemNumber}}</b>
                <v-icon style="font-size: inherit; vertical-align: baseline">mdi-note</v-icon>
                {{String(entity)}}
                <v-icon style="font-size: inherit; vertical-align: baseline">{{entity.supplier.getEntityIcon()}}
                </v-icon>
                {{String(entity.supplier)}}
            </v-list-tile-title>
            <v-list-tile-sub-title>
                <v-icon style="font-size: inherit; vertical-align: baseline">mdi-currency-eur</v-icon>
                :
                <b>{{entity.price}}</b>€,
                <v-icon style="font-size: inherit; vertical-align: baseline">mdi-ruler</v-icon>
                :
                <b>{{entity.quantityUnit}}</b>,
                MwSt.: <b>{{entity.VAT}}</b>%,
                Rabatt: <b>{{entity.rebate}}</b>%,
                Skonto: <b>{{entity.discount}}</b>%
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
    export default class InvoiceItemTile extends Vue {
        @Prop()
        entity;

        onInfoClick() {
            window.open(this.entity.getDetailUrl(), '_new');
        }
    }
</script>
