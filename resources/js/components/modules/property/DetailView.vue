<template>
    <v-container grid-list-md fluid :key="key">
        <v-layout row v-if="property" wrap>
            <v-flex xs12 sm6>
                <b-property-card :value="property"></b-property-card>
            </v-flex>
            <v-flex sm6 v-if="property && property.notes.length > 0" xs12>
                <b-notes-card :details="property.notes"
                              :parent="property"
                              headline="Hinweise"
                ></b-notes-card>
            </v-flex>
            <v-flex sm6 v-if="property && property.details.length > 0" xs12>
                <b-details-card :details="property.details"
                                :parent="property"
                                headline="Details"
                ></b-details-card>
            </v-flex>
            <v-flex md3 sm6 v-if="property && property.houses.length > 0" xs12>
                <b-houses-card :houses="property.houses"
                               :query='{view: "Listenansicht", variables: {partOf: [{type: property.__typename, id: property.id}]}}'
                               headline="Häuser"
                ></b-houses-card>
            </v-flex>
            <v-flex md3 sm6 v-if="property && property.units.length > 0" xs12>
                <b-units-card :query='{view: "Listenansicht", variables: {partOf: [{type: property.__typename, id: property.id}]}}'
                              :units="property.units"
                              headline="Einheiten"
                ></b-units-card>
            </v-flex>
            <v-flex md3 sm6 v-if="property && property.tenants.length > 0" xs12>
                <b-persons-card :persons="property.tenants"
                                :query='{view: "Mieter", variables: {tenantIn: [{type: property.__typename, id: property.id}], tenantDuring: "today"}}'
                                headline="Mieter"
                ></b-persons-card>
            </v-flex>
            <v-flex md3 sm6 v-if="property && property.homeOwners.length > 0" xs12>
                <b-persons-card :persons="property.homeOwners"
                                :query='{view: "WEG-Eigentümer", variables: {homeOwnerIn: [{type: property.__typename, id: property.id}], homeOwnerDuring: "today"}}'
                                headline="WEG-Eigentümer"
                ></b-persons-card>
            </v-flex>
            <v-flex sm6 v-if="property" xs12>
                <b-property-reports-card :property="property"></b-property-reports-card>
            </v-flex>
            <v-flex xs12>
                <b-assignments-card :assignments="property.assignments"
                                    :cost-bearer="property"
                                    :query='{view: "Auftragsliste", variables: {costBearer: [{type: property.__typename, id: property.id}]}}'
                                    headline="Aufträge"
                                    @save="onSave"
                ></b-assignments-card>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import PropertyCard from "../../shared/cards/PropertyCard.vue";
    import NotesCard from "../../shared/cards/NotesCard.vue";
    import DetailsCard from "../../shared/cards/DetailsCard.vue";
    import RentalContractsCardCompact from "../../shared/cards/RentalContractsCardCompact.vue";
    import PurchaseContractsCardCompact from "../../shared/cards/PurchaseContractsCardCompact.vue";
    import AssignmentsCard from "../../shared/cards/AssignmentsCard.vue";
    import PersonsCard from "../../shared/cards/PersonsCard.vue";
    import UnitsCard from "../../shared/cards/UnitsCard.vue";
    import HousesCard from "../../shared/cards/HousesCard.vue";
    import PropertyReportsCard from "../../shared/cards/PropertyReportsCard.vue";
    import {Property} from "../../../models";
    import DetailViewQuery from "./DetailView.graphql";

    @Component({
        components: {
            'b-property-card': PropertyCard,
            'b-notes-card': NotesCard,
            'b-details-card': DetailsCard,
            'b-rental-contracts-card-compact': RentalContractsCardCompact,
            'b-purchase-contracts-card-compact': PurchaseContractsCardCompact,
            'b-persons-card': PersonsCard,
            'b-units-card': UnitsCard,
            'b-houses-card': HousesCard,
            'b-assignments-card': AssignmentsCard,
            'b-property-reports-card': PropertyReportsCard
        },
        apollo: {
            property: {
                query: DetailViewQuery,
                variables(this: DetailView) {
                    return {
                        id: this.id
                    }
                }
            }
        }
    })
    export default class DetailView extends Vue {
        @Prop()
        id: string;

        property: Property;

        onSave() {
            this.$apollo.queries.property.refetch();
        }

        get key() {
            if (this.property) {
                return btoa('property-' + this.property.id);
            }
            return Math.random();
        }
    }
</script>
