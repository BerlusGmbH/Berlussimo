<template>
    <component :entity="entity" :is="entity.__typename" :multiple="multiple"
               @input="$emit('input', $event)"></component>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import personChip from "./PersonChip.vue";
    import partnerChip from "./PartnerChip.vue";
    import objectChip from "./ObjectChip.vue";
    import houseChip from "./HouseChip.vue";
    import unitChip from "./UnitChip.vue";
    import bankAccountChip from "./BankAccountChip.vue";
    import purchaseContractChip from "./PurchaseContractChip.vue";
    import rentalContractChip from "./RentalContractChip.vue";
    import accountingEntityChip from "./AccountingEntityChip.vue";
    import constructionSiteChip from "./ConstructionSiteChip.vue";
    import InvoiceChip from "./InvoiceChip.vue";
    import InvoiceItemChip from "./InvoiceItemChip.vue";
    import BankAccountStandardChartChip from "./BankAccountStandardChartChip.vue";
    import BookingAccountChip from "./BookingAccountChip.vue";
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
            [Person.__typename]: personChip,
            [Partner.__typename]: partnerChip,
            [Property.__typename]: objectChip,
            [House.__typename]: houseChip,
            [Unit.__typename]: unitChip,
            [BankAccount.__typename]: bankAccountChip,
            [PurchaseContract.__typename]: purchaseContractChip,
            [RentalContract.__typename]: rentalContractChip,
            [AccountingEntity.__typename]: accountingEntityChip,
            [ConstructionSite.__typename]: constructionSiteChip,
            [Invoice.__typename]: InvoiceChip,
            [InvoiceItem.__typename]: InvoiceItemChip,
            [BankAccountStandardChart.__typename]: BankAccountStandardChartChip,
            [BookingAccount.__typename]: BookingAccountChip
        }
    })
    export default class Chip extends Vue {
        @Prop({type: Boolean, default: false})
        multiple: boolean;

        @Prop()
        entity;
    }
</script>