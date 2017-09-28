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
    import {Prop} from "vue-property-decorator";
    import {Bankkonto, Einheit, Haus, Objekt, Partner, Person} from "../../../server/resources/models";

    @Component({
        components: {
            [Person.type]: personTile,
            [Partner.type]: partnerIdentifier,
            [Objekt.type]: objectTile,
            [Haus.type]: houseTile,
            [Einheit.type]: unitTile,
            [Bankkonto.type]: bankAccountTile
        }
    })
    export default class Tile extends Vue {
        @Prop()
        entity;
    }
</script>