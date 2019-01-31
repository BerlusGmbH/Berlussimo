<template>
    <v-container grid-list-md fluid :key="key">
        <v-layout v-if="unit" row wrap>
            <v-flex xs12 sm6>
                <b-unit-card :value="unit"></b-unit-card>
            </v-flex>
            <v-flex sm6 v-if="unit && unit.notes.length > 0" xs12>
                <b-notes-card :details="unit.notes"
                              :parent="unit"
                              headline="Hinweise"
                ></b-notes-card>
            </v-flex>
            <v-flex sm6 v-if="unit && unit.details.length > 0" xs12>
                <b-details-card :details="unit.details"
                                :parent="unit"
                                headline="Details"
                ></b-details-card>
            </v-flex>
            <v-flex sm3 v-if="unit && unit.tenants.length > 0" xs12>
                <b-persons-card
                    :persons="unit.tenants"
                    :query='{view: "Mieter", variables: {tenantIn: [{type: unit.__typename, id: unit.id}], tenantDuring: "today"}}'
                    headline="Mieter"
                ></b-persons-card>
            </v-flex>
            <v-flex sm3 v-if="unit && unit.homeOwners.length > 0" xs12>
                <b-persons-card
                    :persons="unit.homeOwners"
                    :query='{view: "WEG-Eigentümer", variables: {homeOwnerIn: [{type: unit.__typename, id: unit.id}], homeOwnerDuring: "today"}}'
                    headline="WEG-Eigentümer"
                ></b-persons-card>
            </v-flex>
            <v-flex sm6 v-if="unit && unit.rentalContracts.length > 0" xs12>
                <b-rental-contracts-card-compact :rental-contracts="unit.rentalContracts"
                                                 headline="Mietverträge"></b-rental-contracts-card-compact>
            </v-flex>
            <v-flex sm6 v-if="unit && unit.purchaseContracts.length > 0" xs12>
                <b-purchase-contracts-card-compact :purchase-contracts="unit.purchaseContracts"
                                                   headline="Kaufverträge"></b-purchase-contracts-card-compact>
            </v-flex>
            <v-flex xs12>
                <b-assignments-card :assignments="unit.assignments"
                                    :cost-bearer="unit"
                                    :query='{view: "Auftragsliste", variables: {costBearer: [{type: unit.__typename, id: unit.id}]}}'
                                    @save="onSave"
                                    headline="Aufträge"
                ></b-assignments-card>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import unitCard from "../../shared/cards/UnitCard.vue";
    import notesCard from "../../shared/cards/NotesCard.vue";
    import detailsCard from "../../shared/cards/DetailsCard.vue";
    import rentalContractsCardCompact from "../../shared/cards/RentalContractsCardCompact.vue";
    import purchaseContractsCardCompact from "../../shared/cards/PurchaseContractsCardCompact.vue";
    import assignmentsCard from "../../shared/cards/AssignmentsCard.vue";
    import personsCard from "../../shared/cards/PersonsCard.vue";
    import DetailViewQuery from "./DetailView.graphql";
    import {Unit} from "../../../models";

    @Component({
        components: {
            'b-unit-card': unitCard,
            'b-notes-card': notesCard,
            'b-details-card': detailsCard,
            'b-rental-contracts-card-compact': rentalContractsCardCompact,
            'b-purchase-contracts-card-compact': purchaseContractsCardCompact,
            'b-persons-card': personsCard,
            'b-assignments-card': assignmentsCard
        },
        apollo: {
            unit: {
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

        unit: Unit;

        onSave() {
            this.$apollo.queries.unit.refetch();
        }

        get key() {
            if (this.unit) {
                return btoa('unit-' + this.unit.id);
            }
            return Math.random();
        }
    }
</script>
