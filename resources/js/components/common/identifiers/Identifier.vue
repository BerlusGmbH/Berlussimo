<template>
    <div style="display: inline-block">
        <component :is="value.__typename"
                   :value="value"
                   @delete="$emit('delete', $event)"
                   @input="$emit('input', $event)"
                   @update="$emit('update', $event)"
        ></component>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import PersonIdentifier from "./PersonIdentifier.vue";
    import PartnerIdentifier from "./PartnerIdentifier.vue";
    import DetailIdentifier from "./DetailIdentifier.vue";
    import RentalContractIdentifier from "./RentalContractIdentifier.vue";
    import PurchaseContractIdentifier from "./PurchaseContractIdentifier.vue";
    import JobIdentifier from "./JobIdentifier.vue";
    import JobTitleIdentifier from "./JobTitleIdentifier.vue";
    import PropertyIdentifier from "./PropertyIdentifier.vue";
    import HouseIdentifier from "./HouseIdentifier.vue";
    import UnitIdentifier from "./UnitIdentifier.vue";
    import AssignmentIdentifier from "./AssignmentIdentifier.vue";
    import ConstructionSiteIdentifier from "./ConstructionSiteIdentifier.vue";
    import AccountingEntityIdentifier from "./AccountingEntityIdentifier.vue";
    import InvoiceIdentifier from "./InvoiceIdentifier.vue";
    import BankAccountIdentifier from "./BankAccountIdentifier.vue";
    import PhoneIdentifier from "./PhoneIdentifier.vue";
    import EmailIdentifier from "./EmailIdentifier.vue";
    import FaxIdentifier from "./FaxIdentifier.vue";
    import PostalAddressIdentifier from "./PostalAddressIdentifier.vue"
    import {Prop} from "vue-property-decorator";
    import {
        AccountingEntity,
        Assignment,
        BankAccount,
        ConstructionSite,
        Detail,
        EMail,
        Fax,
        House,
        Invoice,
        Job,
        JobTitle,
        Partner,
        Person,
        Phone,
        PostalAddress,
        Property,
        PurchaseContract,
        RentalContract,
        Unit
    } from "../../../models";

    @Component({
        components: {
            [Person.__typename]: PersonIdentifier,
            [Person.__typename + 'Stub']: PersonIdentifier,
            [Partner.__typename]: PartnerIdentifier,
            [Detail.__typename]: DetailIdentifier,
            [RentalContract.__typename]: RentalContractIdentifier,
            [PurchaseContract.__typename]: PurchaseContractIdentifier,
            [Property.__typename]: PropertyIdentifier,
            [Property.__typename + 'Stub']: PropertyIdentifier,
            [House.__typename]: HouseIdentifier,
            [Unit.__typename]: UnitIdentifier,
            [Job.__typename]: JobIdentifier,
            [JobTitle.__typename]: JobTitleIdentifier,
            [Assignment.__typename]: AssignmentIdentifier,
            [AccountingEntity.__typename]: AccountingEntityIdentifier,
            [ConstructionSite.__typename]: ConstructionSiteIdentifier,
            [Invoice.__typename]: InvoiceIdentifier,
            [BankAccount.__typename]: BankAccountIdentifier,
            [Phone.__typename]: PhoneIdentifier,
            [EMail.__typename]: EmailIdentifier,
            [Fax.__typename]: FaxIdentifier,
            [PostalAddress.__typename]: PostalAddressIdentifier,
            'Note': DetailIdentifier
        }
    })
    export default class Identifier extends Vue {
        @Prop()
        value;
    }
</script>

<style>
    .identifier i {
        font-size: inherit;
        line-height: inherit;
    }

    .identifier {
        display: flex;
    }
</style>