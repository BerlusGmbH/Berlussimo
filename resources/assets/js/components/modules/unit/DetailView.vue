<template>
    <v-container grid-list-md fluid :key="key">
        <v-layout v-if="unit" row wrap>
            <v-flex xs12 sm6>
                <app-unit-card :value="unit"></app-unit-card>
            </v-flex>
            <v-flex v-if="unit && unit.hinweise.length > 0" xs12 sm6>
                <app-notes-card headline="Hinweise"
                                :details="unit.hinweise"
                                :parent="unit"
                ></app-notes-card>
            </v-flex>
            <v-flex v-if="unit && unit.common_details.length > 0" xs12 sm6>
                <app-details-card headline="Details"
                                  :details="unit.common_details"
                                  :parent="unit"
                ></app-details-card>
            </v-flex>
            <v-flex v-if="unit && unit.mieter.length > 0" xs12 sm6>
                <app-persons-card headline="Mieter"
                                  :persons="unit.mieter"
                                  :filter="'!person(mietvertrag(aktiv einheit(id=' + unit.getID() + ')))'"
                ></app-persons-card>
            </v-flex>
            <v-flex v-if="unit && unit.weg_eigentuemer.length > 0" xs12 sm6>
                <app-persons-card headline="WEG-Eigentümer"
                                  :persons="unit.weg_eigentuemer"
                                  :filter="'!person(kaufvertrag(aktiv einheit(id=' + unit.getID() + ')))'"
                ></app-persons-card>
            </v-flex>
            <v-flex v-if="unit && unit.mietvertraege.length > 0" xs12 sm6>
                <app-rental-contracts-card-compact headline="Mietverträge"
                                                   :rental-contracts="unit.mietvertraege"></app-rental-contracts-card-compact>
            </v-flex>
            <v-flex v-if="unit && unit.kaufvertraege.length > 0" xs12 sm6>
                <app-purchase-contracts-card-compact headline="Kaufverträge"
                                                     :purchase-contracts="unit.kaufvertraege"></app-purchase-contracts-card-compact>
            </v-flex>
            <v-flex xs12>
                <app-assignments-card headline="Aufträge"
                                      :assignments="unit.auftraege"
                                      :cost-unit="unit"
                                      :filter="'!auftrag(kostenträger(einheit(id=' + unit.getID() + ')))'"
                ></app-assignments-card>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {namespace} from "vuex-class";
    import {Prop, Watch} from "vue-property-decorator";
    import unitCard from "../../shared/cards/UnitCard.vue";
    import notesCard from "../../shared/cards/NotesCard.vue";
    import detailsCard from "../../shared/cards/DetailsCard.vue";
    import rentalContractsCardCompact from "../../shared/cards/RentalContractsCardCompact.vue";
    import purchaseContractsCardCompact from "../../shared/cards/PurchaseContractsCardCompact.vue";
    import assignmentsCard from "../../shared/cards/AssignmentsCard.vue";
    import personsCard from "../../shared/cards/PersonsCard.vue";

    const Show = namespace('modules/unit/show');

    const Refresh = namespace('shared/refresh');

    @Component({
        components: {
            'app-unit-card': unitCard,
            'app-notes-card': notesCard,
            'app-details-card': detailsCard,
            'app-rental-contracts-card-compact': rentalContractsCardCompact,
            'app-purchase-contracts-card-compact': purchaseContractsCardCompact,
            'app-persons-card': personsCard,
            'app-assignments-card': assignmentsCard
        }
    })
    export default class DetailView extends Vue {
        @Prop()
        id: string;

        @Show.Action('updateUnit')
        fetchUnit;

        @Show.State('unit')
        unit;

        @Refresh.State('dirty')
        dirty;

        @Refresh.Mutation('refreshFinished')
        refreshFinished: Function;

        @Watch('dirty')
        onDirtyChange(val) {
            if (val) {
                this.fetchUnit(this.id).then(() => {
                    this.refreshFinished();
                }).catch(() => {
                    this.refreshFinished();
                })
            }
        }

        created() {
            if (this.id) {
                this.fetchUnit(this.id);
            }
        }

        @Watch('$route')
        onRouteChange() {
            if (this.id) {
                this.fetchUnit(this.id);
            }
        }

        get key() {
            if (this.unit) {
                return btoa('unit-' + this.unit.EINHEIT_ID);
            }
            return Math.random();
        }
    }
</script>