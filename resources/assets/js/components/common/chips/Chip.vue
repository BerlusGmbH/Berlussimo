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
    import {Prop} from "vue-property-decorator";
    import {Bankkonto, Einheit, Haus, Objekt, Partner, Person} from "../../../server/resources/models";

    @Component({
        components: {
            [Person.type]: personChip,
            [Partner.type]: partnerChip,
            [Objekt.type]: objectChip,
            [Haus.type]: houseChip,
            [Einheit.type]: unitChip,
            [Bankkonto.type]: bankAccountChip
        }
    })
    export default class Chip extends Vue {
        @Prop({type: Boolean, default: false})
        multiple: boolean;

        @Prop()
        entity;
    }
</script>