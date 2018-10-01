<template>
    <v-container grid-list-md fluid :key="key">
        <v-layout v-if="house" row wrap>
            <v-flex xs12 sm6>
                <b-house-card :value="house"></b-house-card>
            </v-flex>
            <v-flex v-if="house && house.hinweise.length > 0" xs12 sm6>
                <b-notes-card headline="Hinweise"
                              :details="house.hinweise"
                              :parent="house"
                ></b-notes-card>
            </v-flex>
            <v-flex v-if="house && house.common_details.length > 0" xs12 sm6>
                <b-details-card headline="Details"
                                :details="house.common_details"
                                :parent="house"
                ></b-details-card>
            </v-flex>
            <v-flex v-if="house && house.einheiten.length > 0" xs12 sm6>
                <b-units-card headline="Einheiten"
                              :units="house.einheiten"
                              :filter="'!einheit(haus(id=' + house.getID() + '))'"
                ></b-units-card>
            </v-flex>
            <v-flex v-if="house && house.mieter.length > 0" xs12 sm6>
                <b-persons-card headline="Mieter"
                                :persons="house.mieter"
                                :filter="'!person(mietvertrag(aktiv haus(id=' + house.getID() + ')))'"
                ></b-persons-card>
            </v-flex>
            <v-flex v-if="house && house.weg_eigentuemer.length > 0" xs12 sm6>
                <b-persons-card headline="WEG-Eigentümer"
                                :persons="house.weg_eigentuemer"
                                :filter="'!person(kaufvertrag(aktiv haus(id=' + house.getID() + ')))'"
                ></b-persons-card>
            </v-flex>
            <v-flex xs12>
                <b-assignments-card headline="Aufträge"
                                    :assignments="house.auftraege"
                                    :cost-unit="house"
                                    :filter="'!auftrag(kostenträger(haus(id=' + house.getID() + ')))'"
                >
                </b-assignments-card>
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
            'b-house-card': houseCard,
            'b-notes-card': notesCard,
            'b-details-card': detailsCard,
            'b-rental-contracts-card-compact': rentalContractsCardCompact,
            'b-purchase-contracts-card-compact': purchaseContractsCardCompact,
            'b-persons-card': personsCard,
            'b-units-card': unitsCard,
            'b-assignments-card': assignmentsCard
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