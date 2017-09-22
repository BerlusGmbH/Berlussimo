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
                    <v-flex xs12 sm6 v-for="(phone, i) in value.phones" :key="'detail-' + phone.DETAIL_ID">
                        <app-identifier v-model="value.phones[i]"></app-identifier>
                    </v-flex>
                    <v-flex xs12 sm6 v-for="(email, i) in value.emails" :key="'detail-' + email.DETAIL_ID">
                        <app-identifier v-model="value.emails[i]"></app-identifier>
                    </v-flex>
                    <v-flex xs12 sm6 v-for="(fax, i) in value.faxs" :key="'detail-' + fax.DETAIL_ID">
                        <app-identifier v-model="value.faxs[i]"></app-identifier>
                    </v-flex>
                    <v-flex xs12 sm6 v-for="(address, i) in value.adressen" :key="'detail-' + address.DETAIL_ID">
                        <app-identifier v-model="value.adressen[i]"></app-identifier>
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
        value;
    }
</script>