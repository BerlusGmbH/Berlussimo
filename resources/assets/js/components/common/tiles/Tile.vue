<template>
    <component :entity="entity" :is="entity.constructor.type"></component>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import personTile from "./PersonTile.vue";
    import partnerTile from "./PartnerTile.vue";
    import objectTile from "./ObjectTile.vue";
    import houseTile from "./HouseTile.vue";
    import unitTile from "./UnitTile.vue";
    import bankAccountTile from "./BankAccountTile.vue";
    import rentalContractTile from "./RentalContractTile.vue";
    import purchaseContractTile from "./PurchaseContractTile.vue";
    import accountingEntityTile from "./AccountingEntityTile.vue";
    import constructionSiteTile from "./ConstructionSiteTile.vue";
    import InvoiceItemTile from "./InvoiceItemTile.vue";
    import InvoiceTile from "./InvoiceTile.vue";
    import BankAccountStandardChartTile from "./BankAccountStandardChartTile.vue";
    import BookingAccountTile from "./BookingAccountTile.vue";
    import {Prop} from "vue-property-decorator";
    import {
        AccountingEntity,
        BankAccountStandardChart,
        Bankkonto,
        BookingAccount,
        ConstructionSite,
        Einheit,
        Haus,
        Invoice,
        InvoiceItem,
        Objekt,
        Partner,
        Person,
        PurchaseContract,
        RentalContract
    } from "../../../server/resources";

    @Component({
        components: {
            [Person.type]: personTile,
            [Partner.type]: partnerTile,
            [Objekt.type]: objectTile,
            [Haus.type]: houseTile,
            [Einheit.type]: unitTile,
            [Bankkonto.type]: bankAccountTile,
            [RentalContract.type]: rentalContractTile,
            [PurchaseContract.type]: purchaseContractTile,
            [AccountingEntity.type]: accountingEntityTile,
            [ConstructionSite.type]: constructionSiteTile,
            [InvoiceItem.type]: InvoiceItemTile,
            [Invoice.type]: InvoiceTile,
            [BankAccountStandardChart.type]: BankAccountStandardChartTile,
            [BookingAccount.type]: BookingAccountTile
        }
    })
    export default class Tile extends Vue {
        @Prop()
        entity;
    }
</script>