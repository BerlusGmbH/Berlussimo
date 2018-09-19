<template>
    <v-container grid-list-md fluid :key="key">
        <v-layout v-if="person" row wrap>
            <v-flex xs12 sm6>
                <app-person-card :value="person"></app-person-card>
            </v-flex>
            <v-flex v-if="person && person.hinweise.length > 0" xs12 sm6>
                <app-notes-card headline="Hinweise"
                                :details="person.hinweise"
                                :parent="person"
                ></app-notes-card>
            </v-flex>
            <v-flex v-if="person && person.common_details.length > 0" xs12 sm6>
                <app-details-card headline="Details"
                                  :details="person.common_details"
                                  :parent="person"
                ></app-details-card>
            </v-flex>
            <v-flex v-if="person && person.mietvertraege.length > 0" xs12 sm6>
                <app-rental-contracts-card headline="Mietverträge"
                                           :rental-contracts="person.mietvertraege"
                ></app-rental-contracts-card>
            </v-flex>
            <v-flex v-if="person && person.kaufvertraege.length > 0" xs12 sm6>
                <app-purchase-contracts-card headline="Kaufverträge"
                                             :purchase-contracts="person.kaufvertraege"
                ></app-purchase-contracts-card>
            </v-flex>
            <v-flex v-if="person && person.jobs_as_employee.length > 0" xs12 sm6>
                <app-jobs-card headline="Anstellungen"
                               :jobs="person.jobs_as_employee"
                ></app-jobs-card>
            </v-flex>
            <v-flex v-if="person && person.roles.length > 0" xs12 sm6>
                <app-roles-card headline="Rollen" :roles="person.roles"></app-roles-card>
            </v-flex>
            <v-flex v-if="person && person.audits.length > 0" xs12 sm6>
                <app-audits-card :audits="person.audits"></app-audits-card>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {namespace} from "vuex-class";
    import {Prop, Watch} from "vue-property-decorator";
    import personCard from "../../shared/cards/PersonCard.vue";
    import notesCard from "../../shared/cards/NotesCard.vue";
    import detailsCard from "../../shared/cards/DetailsCard.vue";
    import rolesCard from "../../shared/cards/RolesCard.vue";
    import auditsCard from "../../shared/cards/AuditsCard.vue";
    import rentalContractsCard from "../../shared/cards/RentalContractsCard.vue";
    import purchaseContractsCard from "../../shared/cards/PurchaseContractsCard.vue";
    import jobsCard from "../../shared/cards/JobsCard.vue";

    const ShowModule = namespace('modules/person/show');
    const RefreshModule = namespace('shared/refresh');

    @Component({
        components: {
            'app-person-card': personCard,
            'app-notes-card': notesCard,
            'app-details-card': detailsCard,
            'app-rental-contracts-card': rentalContractsCard,
            'app-purchase-contracts-card': purchaseContractsCard,
            'app-jobs-card': jobsCard,
            'app-audits-card': auditsCard,
            'app-roles-card': rolesCard
        }
    })
    export default class DetailView extends Vue {
        @Prop()
        id: string;

        @ShowModule.Action('updatePerson')
        fetchPerson;

        @ShowModule.State('person')
        person;

        @RefreshModule.State('dirty')
        dirty;

        @RefreshModule.Mutation('refreshFinished')
        refreshFinished: Function;

        @Watch('dirty')
        onDirtyChange(val) {
            if (val) {
                this.fetchPerson(this.id).then(() => {
                    this.refreshFinished();
                }).catch(() => {
                    this.refreshFinished();
                })
            }
        }

        created() {
            if (this.id) {
                this.fetchPerson(this.id);
            }
        }

        @Watch('$route')
        onRouteChange() {
            if (this.id) {
                this.fetchPerson(this.id);
            }
        }

        get key() {
            if (this.person) {
                return btoa('person-' + this.person.id);
            }
            return Math.random();
        }
    }
</script>