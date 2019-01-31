<template>
    <v-container grid-list-md fluid :key="key">
        <v-layout v-if="object" row wrap>
            <v-flex xs12 sm6>
                <app-object-card :value="object"></app-object-card>
            </v-flex>
            <v-flex v-if="object && object.hinweise.length > 0" xs12 sm6>
                <app-notes-card headline="Hinweise"
                                :details="object.hinweise"
                                :parent="object"
                ></app-notes-card>
            </v-flex>
            <v-flex v-if="object && object.common_details.length > 0" xs12 sm6>
                <app-details-card headline="Details"
                                  :details="object.common_details"
                                  :parent="object"
                ></app-details-card>
            </v-flex>
            <v-flex v-if="object && object.haeuser.length > 0" xs12 sm6>
                <app-houses-card headline="Häuser"
                                 :houses="object.haeuser"
                                 :filter="'!haus(objekt(id=' + object.getID() + '))'"
                ></app-houses-card>
            </v-flex>
            <v-flex v-if="object && object.einheiten.length > 0" xs12 sm6>
                <app-units-card headline="Einheiten"
                                :units="object.einheiten"
                                :filter="'!einheit(objekt(id=' + object.getID() + '))'"
                ></app-units-card>
            </v-flex>
            <v-flex v-if="object && object.mieter.length > 0" xs12 sm6>
                <app-persons-card headline="Mieter"
                                  :persons="object.mieter"
                                  :filter="'!person(mietvertrag(aktiv objekt(id=' + object.getID() + ')))'"
                ></app-persons-card>
            </v-flex>
            <v-flex v-if="object && object.weg_eigentuemer.length > 0" xs12 sm6>
                <app-persons-card headline="WEG-Eigentümer"
                                  :persons="object.weg_eigentuemer"
                                  :filter="'!person(kaufvertrag(aktiv objekt(id=' + object.getID() + ')))'"
                ></app-persons-card>
            </v-flex>
            <v-flex xs12 sm6 v-if="object">
                <app-object-reports-card :object="object"></app-object-reports-card>
            </v-flex>
            <v-flex xs12>
                <app-assignments-card headline="Aufträge"
                                      :assignments="object.auftraege"
                                      :cost-unit="object"
                                      :filter="'!auftrag(kostenträger(objekt(id=' + object.getID() + ')))'"
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


    const Show = namespace('modules/object/show');

    const Refresh = namespace('shared/refresh');

    @Component({
        components: {
            'app-object-card': objectCard,
            'app-notes-card': notesCard,
            'app-details-card': detailsCard,
            'app-rental-contracts-card-compact': rentalContractsCardCompact,
            'app-purchase-contracts-card-compact': purchaseContractsCardCompact,
            'app-persons-card': personsCard,
            'app-units-card': unitsCard,
            'app-houses-card': housesCard,
            'app-assignments-card': assignmentsCard,
            'app-object-reports-card': objectReportsCard
        }
    })
    export default class DetailView extends Vue {
        @Prop()
        id: string;

        @Show.Action('updateObject')
        fetchObject;

        @Show.State('object')
        object;

        @Refresh.State('dirty')
        dirty;

        @Refresh.Mutation('refreshFinished')
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