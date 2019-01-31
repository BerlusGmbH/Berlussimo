<template>
    <v-card>
        <v-card-title>
            <h3 class="headline">{{headline}}</h3>
            <v-chip color="primary">
                <h3>{{purchaseContracts.length}}</h3>
            </v-chip>
        </v-card-title>
        <v-data-table
            :headers="headers"
            :hide-actions="purchaseContracts.length <= 5"
            :items="purchaseContracts"
            disable-initial-sort
        >
            <template slot="items" slot-scope="props">
                <td>
                    <app-identifier v-model="props.item"></app-identifier>
                </td>
                <td>{{props.item.start}}</td>
                <td>{{props.item.end}}</td>
            </template>
            <template v-slot:pageText="props">
                {{ props.pageStart }} - {{ props.pageStop }} von {{ props.itemsLength }}
            </template>
        </v-data-table>
    </v-card>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";

    @Component
    export default class PurchaseContractsCardCompact extends Vue {
        @Prop({type: Array})
        purchaseContracts: any;

        @Prop({type: String})
        headline;

        headers = [
            {text: 'Kaufvertrag', value: 'id'},
            {text: 'Von', value: 'start'},
            {text: 'Bis', value: 'end'}
        ];
    }
</script>
