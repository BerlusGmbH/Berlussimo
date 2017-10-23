<template>
    <div style="display: inline-block">
        <component :value="value"
                   @input="$emit('input', $event)"
                   @update="$emit('update', $event)"
                   :is="value.constructor.type"
                   @delete="$emit('delete', $event)"
        ></component>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import personIdentifier from "./PersonIdentifier.vue";
    import partnerIdentifier from "./PartnerIdentifier.vue";
    import detailIdentifier from "./DetailIdentifier.vue";
    import rentalContractIdentifier from "./RentalContractIdentifier.vue";
    import purchaseContractIdentifier from "./PurchaseContractIdentifier.vue";
    import jobIdentifier from "./JobIdentifier.vue";
    import jobTitleIdentifier from "./JobTitleIdentifier.vue";
    import objectIdentifier from "./ObjectIdentifier.vue";
    import houseIdentifier from "./HouseIdentifier.vue";
    import unitIdentifier from "./UnitIdentifier.vue";
    import assignmentIdentifier from "./AssignmentIdentifier.vue";
    import constructionSiteIdentifier from "./ConstructionSiteIdentifier.vue";
    import accountingEntityIdentifier from "./AccountingEntityIdentifier.vue";
    import {Prop} from "vue-property-decorator";
    import {
        AccountingEntity,
        Assignment, ConstructionSite,
        Detail, Einheit,
        Haus, Job, JobTitle,
        Objekt,
        Partner, Person,
        PurchaseContract,
        RentalContract
    } from "../../../server/resources/models";

    @Component({
        components: {
            [Person.type]: personIdentifier,
            [Partner.type]: partnerIdentifier,
            [Detail.type]: detailIdentifier,
            [RentalContract.type]: rentalContractIdentifier,
            [PurchaseContract.type]: purchaseContractIdentifier,
            [Objekt.type]: objectIdentifier,
            [Haus.type]: houseIdentifier,
            [Einheit.type]: unitIdentifier,
            [Job.type]: jobIdentifier,
            [JobTitle.type]: jobTitleIdentifier,
            [Assignment.type]: assignmentIdentifier,
            [AccountingEntity.type]: accountingEntityIdentifier,
            [ConstructionSite.type]: constructionSiteIdentifier,
        }
    })
    export default class Identifier extends Vue {
        @Prop()
        value;
    }
</script>

<style>
    .identifier-icon {
        position: absolute;
        left: 0;
        display: inline-block;
    }

    .identifier i {
        font-size: inherit;
        line-height: inherit;
    }

    .identifier {
        padding-left: 1.2em;
        position: relative;
    }
</style>