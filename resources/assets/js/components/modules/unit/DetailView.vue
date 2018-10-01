<template>
    <v-container grid-list-md fluid :key="key">
        <v-layout v-if="unit" row wrap>
            <v-flex xs12 sm6>
                <b-unit-card :value="unit"></b-unit-card>
            </v-flex>
            <v-flex v-if="unit && unit.hinweise.length > 0" xs12 sm6>
                <b-notes-card headline="Hinweise"
                              :details="unit.hinweise"
                              :parent="unit"
                ></b-notes-card>
            </v-flex>
            <v-flex v-if="unit && unit.common_details.length > 0" xs12 sm6>
                <b-details-card headline="Details"
                                :details="unit.common_details"
                                :parent="unit"
                ></b-details-card>
            </v-flex>
            <v-flex v-if="unit && unit.mieter.length > 0" xs12 sm6>
                <b-persons-card headline="Mieter"
                                :persons="unit.mieter"
                                :filter="'!person(mietvertrag(aktiv einheit(id=' + unit.getID() + ')))'"
                ></b-persons-card>
            </v-flex>
            <v-flex v-if="unit && unit.weg_eigentuemer.length > 0" xs12 sm6>
                <b-persons-card headline="WEG-Eigentümer"
                                :persons="unit.weg_eigentuemer"
                                :filter="'!person(kaufvertrag(aktiv einheit(id=' + unit.getID() + ')))'"
                ></b-persons-card>
            </v-flex>
            <v-flex v-if="unit && unit.mietvertraege.length > 0" xs12 sm6>
                <b-rental-contracts-card-compact headline="Mietverträge"
                                                 :rental-contracts="unit.mietvertraege"></b-rental-contracts-card-compact>
            </v-flex>
            <v-flex v-if="unit && unit.kaufvertraege.length > 0" xs12 sm6>
                <b-purchase-contracts-card-compact headline="Kaufverträge"
                                                   :purchase-contracts="unit.kaufvertraege"></b-purchase-contracts-card-compact>
            </v-flex>
            <v-flex xs12>
                <b-assignments-card headline="Aufträge"
                                    :assignments="unit.auftraege"
                                    :cost-unit="unit"
                                    :filter="'!auftrag(kostenträger(einheit(id=' + unit.getID() + ')))'"
                ></b-assignments-card>
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

    const ShowModule = namespace('modules/unit/show');
    const RefreshModule = namespace('shared/refresh');

    @Component({
        components: {
            'b-unit-card': unitCard,
            'b-notes-card': notesCard,
            'b-details-card': detailsCard,
            'b-rental-contracts-card-compact': rentalContractsCardCompact,
            'b-purchase-contracts-card-compact': purchaseContractsCardCompact,
            'b-persons-card': personsCard,
            'b-assignments-card': assignmentsCard
        }
    })
    export default class DetailView extends Vue {
        @Prop()
        id: string;

        @ShowModule.Action('updateUnit')
        fetchUnit;

        @ShowModule.State('unit')
        unit;

        @RefreshModule.State('dirty')
        dirty;

        @RefreshModule.Mutation('refreshFinished')
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