<template>
    <v-container grid-list-md fluid>
        <transition name="fade" mode="out-in">
            <v-layout v-if="person" :key="key" row wrap>
                <v-flex xs12 sm6>
                    <app-person-card :key="key" :person="person"></app-person-card>
                </v-flex>
                <v-flex v-if="person && person.hinweise.length > 0" xs12 sm6>
                    <app-details-card headline="Hinweise" :details="person.hinweise"></app-details-card>
                </v-flex>
                <v-flex v-if="person && person.common_details.length > 0" xs12 sm6>
                    <app-details-card headline="Details"
                                      :details="person.common_details"></app-details-card>
                </v-flex>
                <v-flex v-if="person && person.mietvertraege.length > 0" xs12 sm6>
                    <app-rental-contracts-card headline="Mietverträge"
                                               :rental-contracts="person.mietvertraege"></app-rental-contracts-card>
                </v-flex>
                <v-flex v-if="person && person.kaufvertraege.length > 0" xs12 sm6>
                    <app-purchase-contracts-card headline="Kaufverträge"
                                                 :purchase-contracts="person.kaufvertraege"></app-purchase-contracts-card>
                </v-flex>
                <v-flex v-if="person && person.jobs_as_employee.length > 0" xs12 sm6>
                    <app-jobs-card headline="Anstellungen"
                                   :jobs="person.jobs_as_employee"></app-jobs-card>
                </v-flex>
                <v-flex v-if="person && person.roles.length > 0" xs12 sm6>
                    <app-roles-card headline="Rollen" :roles="person.roles"></app-roles-card>
                </v-flex>
                <v-flex v-if="person && person.audits.length > 0" xs12 sm6>
                    <app-audits-card :audits="person.audits"></app-audits-card>
                </v-flex>
            </v-layout>
        </transition>
        <app-merge-dialog v-model="showMergeDialog"></app-merge-dialog>
        <app-show-fab v-model="showFab" @openMergeDialog="showMergeDialog = true"></app-show-fab>
    </v-container>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Action, namespace, State} from "vuex-class";
    import {Prop} from "vue-property-decorator";
    import personCard from "../../shared/cards/PersonCard.vue";
    import detailsCard from "../../shared/cards/DetailsCard.vue";
    import rolesCard from "../../shared/cards/RolesCard.vue";
    import auditsCard from "../../shared/cards/AuditsCard.vue";
    import rentalContractsCard from "../../shared/cards/RentalContractsCard.vue";
    import purchaseContractsCard from "../../shared/cards/PurchaseContractsCard.vue";
    import jobsCard from "../../shared/cards/JobsCard.vue";
    import mergeDialog from "./show/Merge.vue";
    import fab from "./show/Fab.vue";

    const ShowAction = namespace('modules/personen/show', Action);
    const ShowState = namespace('modules/personen/show', State);

    @Component({
        components: {
            'app-person-card': personCard,
            'app-details-card': detailsCard,
            'app-rental-contracts-card': rentalContractsCard,
            'app-purchase-contracts-card': purchaseContractsCard,
            'app-jobs-card': jobsCard,
            'app-audits-card': auditsCard,
            'app-roles-card': rolesCard,
            'app-merge-dialog': mergeDialog,
            'app-show-fab': fab
        }
    })
    export default class Show extends Vue {
        @Prop()
        personId: number;

        @ShowAction('updatePerson')
        updatePerson;

        @ShowState('person')
        person;

        showMergeDialog: boolean = false;
        showFab: boolean = false;

        created() {
            this.updatePerson(this.personId);
        }

        get key() {
            return btoa('person-' + this.person.id + '-' + Math.random());
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