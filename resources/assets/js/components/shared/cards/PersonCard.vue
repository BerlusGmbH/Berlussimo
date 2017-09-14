<template>
    <v-card>
        <v-card-title>
            <v-layout row wrap>
                <v-flex xs12>
                    <app-identifier class="headline" :entity="person"></app-identifier>
                </v-flex>
                <v-flex xs12 v-if="person.birthday">
                    <v-icon style="font-size: inherit">mdi-star</v-icon>
                    {{person.birthday}}
                </v-flex>
            </v-layout>
        </v-card-title>
        <v-card-text>
            <v-container fluid grid-list-sm>
                <v-layout row wrap>
                    <v-flex xs12 sm6 v-for="phone in person.phones" :key="'detail-' + phone.DETAIL_ID">
                        <app-identifier :entity="phone"></app-identifier>
                    </v-flex>
                    <v-flex xs12 sm6 v-for="email in person.emails" :key="'detail-' + email.DETAIL_ID">
                        <app-identifier :entity="email"></app-identifier>
                    </v-flex>
                    <v-flex xs12 sm6 v-for="fax in person.faxs" :key="'detail-' + fax.DETAIL_ID">
                        <app-identifier :entity="fax"></app-identifier>
                    </v-flex>
                    <v-flex xs12 sm6 v-for="address in person.adressen" :key="'detail-' + address.DETAIL_ID">
                        <app-identifier :entity="address"></app-identifier>
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

    @Component
    export default class PersonCard extends Vue {
        @Prop()
        person;
    }
</script>