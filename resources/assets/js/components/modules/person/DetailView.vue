<template>
    <v-container grid-list-md fluid :key="key">
        <v-layout v-if="person" row wrap>
            <v-flex xs12 sm6>
                <b-person-card :value="person"></b-person-card>
            </v-flex>
            <v-flex v-if="person && person.hinweise.length > 0" xs12 sm6>
                <b-notes-card headline="Hinweise"
                              :details="person.hinweise"
                              :parent="person"
                ></b-notes-card>
            </v-flex>
            <v-flex v-if="person && person.common_details.length > 0" xs12 sm6>
                <b-details-card headline="Details"
                                :details="person.common_details"
                                :parent="person"
                ></b-details-card>
            </v-flex>
            <v-flex v-if="person && person.mietvertraege.length > 0" xs12 sm6>
                <b-rental-contracts-card headline="Mietverträge"
                                         :rental-contracts="person.mietvertraege"
                ></b-rental-contracts-card>
            </v-flex>
            <v-flex v-if="person && person.kaufvertraege.length > 0" xs12 sm6>
                <b-purchase-contracts-card headline="Kaufverträge"
                                           :purchase-contracts="person.kaufvertraege"
                ></b-purchase-contracts-card>
            </v-flex>
            <v-flex v-if="person && person.jobs_as_employee.length > 0" xs12 sm6>
                <b-jobs-card headline="Anstellungen"
                             :jobs="person.jobs_as_employee"
                ></b-jobs-card>
            </v-flex>
            <v-flex v-if="person && person.roles.length > 0" xs12 sm6>
                <b-roles-card headline="Rollen" :roles="person.roles"></b-roles-card>
            </v-flex>
            <v-flex v-if="person && person.audits.length > 0" xs12 sm6>
                <b-audits-card :audits="person.audits"></b-audits-card>
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
            'b-person-card': personCard,
            'b-notes-card': notesCard,
            'b-details-card': detailsCard,
            'b-rental-contracts-card': rentalContractsCard,
            'b-purchase-contracts-card': purchaseContractsCard,
            'b-jobs-card': jobsCard,
            'b-audits-card': auditsCard,
            'b-roles-card': rolesCard
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