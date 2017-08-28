<template>
    <component :entity="entity" :is="entity.constructor.name"></component>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import personTile from "./PersonTile.vue";
    import partnerIdentifier from "./PartnerTile.vue";
    import objectTile from "./ObjectTile.vue";
    import houseTile from "./HouseTile.vue";
    import unitTile from "./UnitTile.vue";
    import bankAccountTile from "./BankAccountTile.vue";
    import {Prop} from "vue-property-decorator";

    @Component({
        components: {
            'Person': personTile,
            'Partner': partnerIdentifier,
            'Objekt': objectTile,
            'Haus': houseTile,
            'Einheit': unitTile,
            'Bankkonto': bankAccountTile
        }
    })
    export default class Tile extends Vue {
        @Prop()
        entity;
    }
</script>