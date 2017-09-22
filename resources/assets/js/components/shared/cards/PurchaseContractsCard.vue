<template>
    <v-card>
        <v-card-title>
            <h3 class="headline">{{headline}} ({{purchaseContracts.length}})</h3>
            <v-spacer></v-spacer>
            <v-text-field
                    append-icon="search"
                    label="Search"
                    single-line
                    hide-details
                    v-model="search"
            ></v-text-field>
        </v-card-title>
        <v-card-text>
            <v-data-table
                    :headers="headers"
                    :items="purchaseContracts"
                    :search="search"
                    :hide-actions="purchaseContracts.length <= 5"
                    class="elevation-1"
            >
                <template slot="items" scope="props">
                    <td>
                        <app-identifier v-model="props.item"></app-identifier>
                    </td>
                    <td>{{props.item.VON}}</td>
                    <td>{{props.item.BIS}}</td>
                    <td>
                        <app-identifier v-model="props.item.einheit"></app-identifier>
                    </td>
                    <td>
                        <app-identifier v-model="props.item.einheit.haus"></app-identifier>
                    </td>
                    <td>
                        <app-identifier v-model="props.item.einheit.haus.objekt"></app-identifier>
                    </td>
                </template>
                <template slot="pageText" scope="{ pageStart, pageStop }">
                    Von {{ pageStart }} bis {{ pageStop }}
                </template>
            </v-data-table>
        </v-card-text>
    </v-card>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";

    @Component
    export default class PurchaseContractsCard extends Vue {
        @Prop({type: Array})
        purchaseContracts: any;

        @Prop({type: String})
        headline;

        search: string = '';
        headers = [
            {text: 'Kaufvertrag', value: 'ID'},
            {text: 'Von', value: 'VON'},
            {text: 'Bis', value: 'BIS'},
            {text: 'Einheit', value: 'EINHEIT_ID'},
            {text: 'Haus', value: 'HAUS_ID'},
            {text: 'Objekt', value: 'OBJEKT_ID'},
        ];
    }
</script>