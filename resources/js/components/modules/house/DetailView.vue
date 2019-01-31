<template>
    <v-container grid-list-md fluid :key="key">
        <v-layout v-if="house" row wrap>
            <v-flex xs12 sm6>
                <b-house-card :value="house"></b-house-card>
            </v-flex>
            <v-flex sm6 v-if="house && house.notes.length > 0" xs12>
                <b-notes-card :details="house.notes"
                              :parent="house"
                              headline="Hinweise"
                ></b-notes-card>
            </v-flex>
            <v-flex sm6 v-if="house && house.details.length > 0" xs12>
                <b-details-card :details="house.details"
                                :parent="house"
                                headline="Details"
                ></b-details-card>
            </v-flex>
            <v-flex sm3 v-if="house && house.units.length > 0" xs12>
                <b-units-card
                    :query='{view: "Listenansicht", variables: {partOf: [{type: house.__typename, id: house.id}]}}'
                    :units="house.units"
                    headline="Einheiten"
                ></b-units-card>
            </v-flex>
            <v-flex sm3 v-if="house && house.tenants.length > 0" xs12>
                <b-persons-card
                    :persons="house.tenants"
                    :query='{view: "Mieter", variables: {tenantIn: [{type: house.__typename, id: house.id}], tenantDuring: "today"}}'
                    headline="Mieter"
                ></b-persons-card>
            </v-flex>
            <v-flex sm3 v-if="house && house.homeOwners.length > 0" xs12>
                <b-persons-card
                    :persons="house.homeOwners"
                    :query='{view: "WEG-Eigentümer", variables: {homeOwnerIn: [{type: house.__typename, id: house.id}], homeOwnerDuring: "today"}}'
                    headline="WEG-Eigentümer"
                ></b-persons-card>
            </v-flex>
            <v-flex xs12>
                <b-assignments-card :assignments="house.assignments"
                                    :cost-bearer="house"
                                    :query='{view: "Auftragsliste", variables: {costBearer: [{type: house.__typename, id: house.id}]}}'
                                    headline="Aufträge"
                                    @save="onSave"
                >
                </b-assignments-card>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import HouseCard from "../../shared/cards/HouseCard.vue";
    import NotesCard from "../../shared/cards/NotesCard.vue";
    import DetailsCard from "../../shared/cards/DetailsCard.vue";
    import RentalContractsCardCompact from "../../shared/cards/RentalContractsCardCompact.vue";
    import PurchaseContractsCardCompact from "../../shared/cards/PurchaseContractsCardCompact.vue";
    import AssignmentsCard from "../../shared/cards/AssignmentsCard.vue";
    import PersonsCard from "../../shared/cards/PersonsCard.vue";
    import UnitsCard from "../../shared/cards/UnitsCard.vue";
    import {House} from "../../../models";
    import DetailViewQuery from "./DetailView.graphql";

    @Component({
        components: {
            'b-house-card': HouseCard,
            'b-notes-card': NotesCard,
            'b-details-card': DetailsCard,
            'b-rental-contracts-card-compact': RentalContractsCardCompact,
            'b-purchase-contracts-card-compact': PurchaseContractsCardCompact,
            'b-persons-card': PersonsCard,
            'b-units-card': UnitsCard,
            'b-assignments-card': AssignmentsCard
        },
        apollo: {
            house: {
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

        house: House;

        onSave() {
            this.$apollo.queries.house.refetch();
        }

        get key() {
            if (this.house) {
                return btoa('house-' + this.house.id);
            }
            return Math.random();
        }
    }
</script>
