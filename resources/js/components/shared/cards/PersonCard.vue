<template>
    <v-card>
        <v-card-title>
            <v-layout row wrap>
                <v-flex xs12>
                    <app-identifier class="headline"
                                    :value="value"
                    ></app-identifier>
                </v-flex>
                <v-flex xs12 v-if="value.birthday">
                    <v-icon style="font-size: inherit">mdi-star</v-icon>
                    {{value.birthday}}
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text>
            <v-container fluid grid-list-sm>
                <v-layout row wrap>
                    <v-flex :key="'detail-' + phone.id" sm6 v-for="(phone, i) in value.phones" xs12>
                        <app-identifier v-model="value.phones[i]"></app-identifier>
                    </v-flex>
                    <v-flex :key="'detail-' + email.id" sm6 v-for="(email, i) in value.eMails" xs12>
                        <app-identifier v-model="value.eMails[i]"></app-identifier>
                    </v-flex>
                    <v-flex :key="'detail-' + fax.id" sm6 v-for="(fax, i) in value.faxs" xs12>
                        <app-identifier v-model="value.faxs[i]"></app-identifier>
                    </v-flex>
                    <v-flex :key="'detail-' + address.id" sm6 v-for="(address, i) in value.postalAddresses" xs12>
                        <app-identifier v-model="value.postalAddresses[i]"></app-identifier>
                    </v-flex>
                </v-layout>
            </v-container>
        </v-card-text>
    </v-card>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Person} from "../../../models";

    @Component
    export default class PersonCard extends Vue {
        @Prop()
        value: Person;
    }
</script>