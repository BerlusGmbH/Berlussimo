<template>
    <component @input="$emit('input', $event)" :multiple="multiple" :entity="entity"
               :is="entity.constructor.type"></component>
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
            [Person.type]: personChip,
            [Partner.type]: partnerChip,
            [Objekt.type]: objectChip,
            [Haus.type]: houseChip,
            [Einheit.type]: unitChip,
            [Bankkonto.type]: bankAccountChip,
            [PurchaseContract.type]: purchaseContractChip,
            [RentalContract.type]: rentalContractChip,
            [AccountingEntity.type]: accountingEntityChip,
            [ConstructionSite.type]: constructionSiteChip
        }
    })
    export default class Chip extends Vue {
        @Prop({type: Boolean, default: false})
        multiple: boolean;

        @Prop()
        entity;
    }
</script>