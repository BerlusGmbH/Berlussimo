<template>
    <v-card>
        <v-card-title>
            <a v-if="href" :href="href"><h3 class="headline">{{headline}} ({{houses.length}})</h3></a>
            <h3 v-else class="headline">{{headline}} ({{houses.length}})</h3>
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
                    :items="houses"
                    :search="search"
                    :hide-actions="houses.length <= 5"
                    class="elevation-1"
            >
                <template slot="items" slot-scope="props">
                    <td>
                        <app-identifier v-model="props.item"></app-identifier>
                    </td>
                </template>
                <template slot="pageText" slot-scope="{ pageStart, pageStop }">
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
    export default class HousesCard extends Vue {
        @Prop({type: Array})
        houses: any;

        @Prop({type: String})
        headline;

        @Prop({type: String, default: ''})
        href;

        search: string = '';
        headers = [
            {text: 'Haus', value: 'HAUS_ID'}
        ];
    }
</script>