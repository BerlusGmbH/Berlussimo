<template>
    <v-container grid-list-md fluid :key="key">
        <v-layout v-if="object" row wrap>
            <v-flex xs12 sm6>
                <b-object-card :value="object"></b-object-card>
            </v-flex>
            <v-flex v-if="object && object.hinweise.length > 0" xs12 sm6>
                <b-notes-card headline="Hinweise"
                              :details="object.hinweise"
                              :parent="object"
                ></b-notes-card>
            </v-flex>
            <v-flex v-if="object && object.common_details.length > 0" xs12 sm6>
                <b-details-card headline="Details"
                                :details="object.common_details"
                                :parent="object"
                ></b-details-card>
            </v-flex>
            <v-flex v-if="object && object.haeuser.length > 0" xs12 sm6>
                <b-houses-card headline="Häuser"
                               :houses="object.haeuser"
                               :filter="'!haus(objekt(id=' + object.getID() + '))'"
                ></b-houses-card>
            </v-flex>
            <v-flex v-if="object && object.einheiten.length > 0" xs12 sm6>
                <b-units-card headline="Einheiten"
                              :units="object.einheiten"
                              :filter="'!einheit(objekt(id=' + object.getID() + '))'"
                ></b-units-card>
            </v-flex>
            <v-flex v-if="object && object.mieter.length > 0" xs12 sm6>
                <b-persons-card headline="Mieter"
                                :persons="object.mieter"
                                :filter="'!person(mietvertrag(aktiv objekt(id=' + object.getID() + ')))'"
                ></b-persons-card>
            </v-flex>
            <v-flex v-if="object && object.weg_eigentuemer.length > 0" xs12 sm6>
                <b-persons-card headline="WEG-Eigentümer"
                                :persons="object.weg_eigentuemer"
                                :filter="'!person(kaufvertrag(aktiv objekt(id=' + object.getID() + ')))'"
                ></b-persons-card>
            </v-flex>
            <v-flex xs12 sm6 v-if="object">
                <b-object-reports-card :object="object"></b-object-reports-card>
            </v-flex>
            <v-flex xs12>
                <b-assignments-card headline="Aufträge"
                                    :assignments="object.auftraege"
                                    :cost-unit="object"
                                    :filter="'!auftrag(kostenträger(objekt(id=' + object.getID() + ')))'"
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
    import objectCard from "../../shared/cards/ObjectCard.vue";
    import notesCard from "../../shared/cards/NotesCard.vue";
    import detailsCard from "../../shared/cards/DetailsCard.vue";
    import rentalContractsCardCompact from "../../shared/cards/RentalContractsCardCompact.vue";
    import purchaseContractsCardCompact from "../../shared/cards/PurchaseContractsCardCompact.vue";
    import assignmentsCard from "../../shared/cards/AssignmentsCard.vue";
    import personsCard from "../../shared/cards/PersonsCard.vue";
    import unitsCard from "../../shared/cards/UnitsCard.vue";
    import housesCard from "../../shared/cards/HousesCard.vue";
    import objectReportsCard from "../../shared/cards/ObjectReportsCard.vue";


    const ShowModule = namespace('modules/object/show');
    const RefreshModule = namespace('shared/refresh');

    @Component({
        components: {
            'b-object-card': objectCard,
            'b-notes-card': notesCard,
            'b-details-card': detailsCard,
            'b-rental-contracts-card-compact': rentalContractsCardCompact,
            'b-purchase-contracts-card-compact': purchaseContractsCardCompact,
            'b-persons-card': personsCard,
            'b-units-card': unitsCard,
            'b-houses-card': housesCard,
            'b-assignments-card': assignmentsCard,
            'b-object-reports-card': objectReportsCard
        }
    })
    export default class DetailView extends Vue {
        @Prop()
        id: string;

        @ShowModule.Action('updateObject')
        fetchObject;

        @ShowModule.State('object')
        object;

        @RefreshModule.State('dirty')
        dirty;

        @RefreshModule.Mutation('refreshFinished')
        refreshFinished: Function;

        @Watch('dirty')
        onDirtyChange(val) {
            if (val) {
                this.fetchObject(this.id).then(() => {
                    this.refreshFinished();
                }).catch(() => {
                    this.refreshFinished();
                })
            }
        }

        created() {
            if (this.id) {
                this.fetchObject(this.id);
            }
        }

        @Watch('$route')
        onRouteChange() {
            if (this.id) {
                this.fetchObject(this.id);
            }
        }

        get key() {
            if (this.object) {
                return btoa('object-' + this.object.OBJEKT_ID);
            }
            return Math.random();
        }
    }
</script>