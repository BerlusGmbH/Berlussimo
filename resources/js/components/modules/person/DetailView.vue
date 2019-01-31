<template>
    <v-container :key="key" fluid grid-list-md>
        <v-layout row v-if="person" wrap>
            <v-flex sm6 xs12>
                <b-person-card :value="person"></b-person-card>
            </v-flex>
            <v-flex sm6 v-if="person.notes && person.notes.length > 0" xs12>
                <b-notes-card :details="person.notes"
                              :parent="person"
                              headline="Hinweise"
                ></b-notes-card>
            </v-flex>
            <v-flex sm6 v-if="person.details && person.details.length > 0" xs12>
                <b-details-card :details="person.details"
                                :parent="person"
                                headline="Details"
                ></b-details-card>
            </v-flex>
            <v-flex sm6 v-if="person.rentalContracts && person.rentalContracts.length > 0" xs12>
                <b-rental-contracts-card :rental-contracts="person.rentalContracts"
                                         headline="Mietverträge"
                ></b-rental-contracts-card>
            </v-flex>
            <v-flex sm6 v-if="person.purchaseContracts && person.purchaseContracts.length > 0" xs12>
                <b-purchase-contracts-card :purchase-contracts="person.purchaseContracts"
                                           headline="Kaufverträge"
                ></b-purchase-contracts-card>
            </v-flex>
            <v-flex sm6 v-if="person.jobs && person.jobs.length > 0" xs12>
                <b-jobs-card :jobs="person.jobs"
                             headline="Anstellungen"
                ></b-jobs-card>
            </v-flex>
            <v-flex sm6 v-if="person.roles && person.roles.length > 0" xs12>
                <b-roles-card :roles="person.roles" headline="Rollen"></b-roles-card>
            </v-flex>
            <v-flex sm6 v-if="person.audits && person.audits.length > 0" xs12>
                <b-audits-card :audits="person.audits"></b-audits-card>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import PersonCard from "../../shared/cards/PersonCard.vue";
    import NotesCard from "../../shared/cards/NotesCard.vue";
    import DetailsCard from "../../shared/cards/DetailsCard.vue";
    import RolesCard from "../../shared/cards/RolesCard.vue";
    import AuditsCard from "../../shared/cards/AuditsCard.vue";
    import RentalContractsCard from "../../shared/cards/RentalContractsCard.vue";
    import PurchaseContractsCard from "../../shared/cards/PurchaseContractsCard.vue";
    import JobsCard from "../../shared/cards/JobsCard.vue";
    import DetailViewQuery from "./DetailView.graphql";

    @Component({
        components: {
            'b-person-card': PersonCard,
            'b-notes-card': NotesCard,
            'b-details-card': DetailsCard,
            'b-rental-contracts-card': RentalContractsCard,
            'b-purchase-contracts-card': PurchaseContractsCard,
            'b-jobs-card': JobsCard,
            'b-audits-card': AuditsCard,
            'b-roles-card': RolesCard
        },
        apollo: {
            person: {
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

        person: any;

        get key() {
            if (this.person) {
                return btoa('person-' + this.person.id);
            }
            return Math.random();
        }
    }
</script>
