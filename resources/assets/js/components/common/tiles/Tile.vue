<template>
    <component :entity="entity" :is="entity.constructor.type"></component>
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
    import rentalContractTile from "./RentalContractTile.vue";
    import purchaseContractTile from "./PurchaseContractTile.vue";
    import accountingEntityTile from "./AccountingEntityTile.vue";
    import constructionSiteTile from "./ConstructionSiteTile.vue";
    import {Prop} from "vue-property-decorator";
    import {
        AccountingEntity,
        Bankkonto, ConstructionSite,
        Einheit,
        Haus,
        Objekt,
        Partner,
        Person,
        PurchaseContract,
        RentalContract
    } from "../../../server/resources/models";

    @Component({
        components: {
            [Person.type]: personTile,
            [Partner.type]: partnerIdentifier,
            [Objekt.type]: objectTile,
            [Haus.type]: houseTile,
            [Einheit.type]: unitTile,
            [Bankkonto.type]: bankAccountTile,
            [RentalContract.type]: rentalContractTile,
            [PurchaseContract.type]: purchaseContractTile,
            [AccountingEntity.type]: accountingEntityTile,
            [ConstructionSite.type]: constructionSiteTile
        }
    })
    export default class Tile extends Vue {
        @Prop()
        entity;
    }
</script>