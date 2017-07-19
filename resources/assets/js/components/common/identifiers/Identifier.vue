<template>
    <div style="display: inline-block">
        <component :entity="entity" :is="entity.constructor.name"></component>
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
    import {Prop} from "vue-property-decorator";

    @Component({
        components: {
            'Person': personIdentifier,
            'Partner': partnerIdentifier,
            'Detail': detailIdentifier,
            'RentalContract': rentalContractIdentifier,
            'PurchaseContract': purchaseContractIdentifier,
            'Objekt': objectIdentifier,
            'Haus': houseIdentifier,
            'Einheit': unitIdentifier,
            'Job': jobIdentifier,
            'JobTitle': jobTitleIdentifier
        }
    })
    export default class Identifier extends Vue {
        @Prop()
        entity;
    }
</script>