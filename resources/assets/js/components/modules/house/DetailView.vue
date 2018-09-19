<template>
    <v-container grid-list-md fluid :key="key">
        <v-layout v-if="house" row wrap>
            <v-flex xs12 sm6>
                <app-house-card :value="house"></app-house-card>
            </v-flex>
            <v-flex v-if="house && house.hinweise.length > 0" xs12 sm6>
                <app-notes-card headline="Hinweise"
                                :details="house.hinweise"
                                :parent="house"
                ></app-notes-card>
            </v-flex>
            <v-flex v-if="house && house.common_details.length > 0" xs12 sm6>
                <app-details-card headline="Details"
                                  :details="house.common_details"
                                  :parent="house"
                ></app-details-card>
            </v-flex>
            <v-flex v-if="house && house.einheiten.length > 0" xs12 sm6>
                <app-units-card headline="Einheiten"
                                :units="house.einheiten"
                                :filter="'!einheit(haus(id=' + house.getID() + '))'"
                ></app-units-card>
            </v-flex>
            <v-flex v-if="house && house.mieter.length > 0" xs12 sm6>
                <app-persons-card headline="Mieter"
                                  :persons="house.mieter"
                                  :filter="'!person(mietvertrag(aktiv haus(id=' + house.getID() + ')))'"
                ></app-persons-card>
            </v-flex>
            <v-flex v-if="house && house.weg_eigentuemer.length > 0" xs12 sm6>
                <app-persons-card headline="WEG-Eigentümer"
                                  :persons="house.weg_eigentuemer"
                                  :filter="'!person(kaufvertrag(aktiv haus(id=' + house.getID() + ')))'"
                ></app-persons-card>
            </v-flex>
            <v-flex xs12>
                <app-assignments-card headline="Aufträge"
                                      :assignments="house.auftraege"
                                      :cost-unit="house"
                                      :filter="'!auftrag(kostenträger(haus(id=' + house.getID() + ')))'"
                >
                </app-assignments-card>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {namespace} from "vuex-class";
    import {Prop, Watch} from "vue-property-decorator";
    import houseCard from "../../shared/cards/HouseCard.vue";
    import notesCard from "../../shared/cards/NotesCard.vue";
    import detailsCard from "../../shared/cards/DetailsCard.vue";
    import rentalContractsCardCompact from "../../shared/cards/RentalContractsCardCompact.vue";
    import purchaseContractsCardCompact from "../../shared/cards/PurchaseContractsCardCompact.vue";
    import assignmentsCard from "../../shared/cards/AssignmentsCard.vue";
    import personsCard from "../../shared/cards/PersonsCard.vue";
    import unitsCard from "../../shared/cards/UnitsCard.vue";


    const ShowModule = namespace('modules/house/show');
    const RefreshModule = namespace('shared/refresh');

    @Component({
        components: {
            'app-house-card': houseCard,
            'app-notes-card': notesCard,
            'app-details-card': detailsCard,
            'app-rental-contracts-card-compact': rentalContractsCardCompact,
            'app-purchase-contracts-card-compact': purchaseContractsCardCompact,
            'app-persons-card': personsCard,
            'app-units-card': unitsCard,
            'app-assignments-card': assignmentsCard
        }
    })
    export default class DetailView extends Vue {
        @Prop()
        id: string;

        @ShowModule.Action('updateHouse')
        fetchHouse;

        @ShowModule.State('house')
        house;

        @RefreshModule.State('dirty')
        dirty;

        @RefreshModule.Mutation('refreshFinished')
        refreshFinished: Function;

        @Watch('dirty')
        onDirtyChange(val) {
            if (val) {
                this.fetchHouse(this.id).then(() => {
                    this.refreshFinished();
                }).catch(() => {
                    this.refreshFinished();
                })
            }
        }

        created() {
            if (this.id) {
                this.fetchHouse(this.id);
            }
        }

        @Watch('$route')
        onRouteChange() {
            if (this.id) {
                this.fetchHouse(this.id);
            }
        }

        get key() {
            if (this.house) {
                return btoa('house-' + this.house.HAUS_ID);
            }
            return Math.random();
        }
    }
</script>