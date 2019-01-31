<template>
    <component :entity="entity" :is="entity.__typename"></component>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import personTile from "./PersonTile.vue";
    import partnerTile from "./PartnerTile.vue";
    import propertyTile from "./PropertyTile.vue";
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
        BankAccount,
        BankAccountStandardChart,
        BookingAccount,
        ConstructionSite,
        House,
        Invoice,
        InvoiceItem,
        Partner,
        Person,
        Property,
        PurchaseContract,
        RentalContract,
        Unit
    } from "../../../models";

    @Component({
        components: {
            [Person.__typename]: personTile,
            [Partner.__typename]: partnerTile,
            [Property.__typename]: propertyTile,
            [House.__typename]: houseTile,
            [Unit.__typename]: unitTile,
            [BankAccount.__typename]: bankAccountTile,
            [RentalContract.__typename]: rentalContractTile,
            [PurchaseContract.__typename]: purchaseContractTile,
            [AccountingEntity.__typename]: accountingEntityTile,
            [ConstructionSite.__typename]: constructionSiteTile,
            [InvoiceItem.__typename]: InvoiceItemTile,
            [Invoice.__typename]: InvoiceTile,
            [BankAccountStandardChart.__typename]: BankAccountStandardChartTile,
            [BookingAccount.__typename]: BookingAccountTile
        }
    })
    export default class Tile extends Vue {
        @Prop()
        entity;
    }
</script>