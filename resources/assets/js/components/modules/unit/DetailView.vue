<template>
    <v-container grid-list-md fluid>
        <transition name="fade" mode="out-in">
            <v-layout v-if="unit" :key="key" row wrap>
                <v-flex xs12 sm6>
                    <app-unit-card :key="key" :value="unit"></app-unit-card>
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
                                      :href="'/personen?q=!person(mietvertrag(aktiv einheit(id=' + unit.getID() + ')))'"
                    ></app-persons-card>
                </v-flex>
                <v-flex v-if="unit && unit.weg_eigentuemer.length > 0" xs12 sm6>
                    <app-persons-card headline="WEG-Eigentümer"
                                      :persons="unit.weg_eigentuemer"
                                      :href="'/personen?q=!person(kaufvertrag(aktiv einheit(id=' + unit.getID() + ')))'"
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
                <v-flex v-if="unit && unit.auftraege.length > 0" xs12>
                    <app-assignments-card headline="Aufträge"
                                          :assignments="unit.auftraege"
                                          :href="'/auftraege?q=!auftrag(kostenträger(einheit(id=' + unit.getID() + ')))'"
                    ></app-assignments-card>
                </v-flex>
            </v-layout>
        </transition>
    </v-container>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Action, Mutation, namespace, State} from "vuex-class";
    import {Prop, Watch} from "vue-property-decorator";
    import unitCard from "../../shared/cards/UnitCard.vue";
    import notesCard from "../../shared/cards/NotesCard.vue";
    import detailsCard from "../../shared/cards/DetailsCard.vue";
    import rentalContractsCardCompact from "../../shared/cards/RentalContractsCardCompact.vue";
    import purchaseContractsCardCompact from "../../shared/cards/PurchaseContractsCardCompact.vue";
    import assignmentsCard from "../../shared/cards/AssignmentsCard.vue";
    import personsCard from "../../shared/cards/PersonsCard.vue";

    const ShowAction = namespace('modules/unit/show', Action);
    const ShowState = namespace('modules/unit/show', State);

    const RefreshState = namespace('shared/refresh', State);
    const RefreshMutation = namespace('shared/refresh', Mutation);

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
        unitId: number;

        @ShowAction('updateUnit')
        fetchUnit;

        @ShowState('unit')
        unit;

        @RefreshState('dirty')
        dirty;

        @RefreshMutation('refreshFinished')
        refreshFinished: Function;

        @Watch('dirty')
        onDirtyChange(val) {
            if (val) {
                this.fetchUnit(this.unitId).then(() => {
                    this.refreshFinished();
                }).catch(() => {
                    this.refreshFinished();
                })
            }
        }

        created() {
            this.fetchUnit(this.unitId);
        }

        get key() {
            return btoa('unit-' + this.unit.id);
        }
    }
</script>

<style>
    .fade-enter-active {
        transition: all .3s ease;
    }

    .fade-leave-active {
        transition: all .3s ease;
    }

    .fade-enter, .fade-leave-to {
        opacity: 0;
    }
</style>