<template>
    <v-card>
        <v-card-title>
            <h3 class="headline">{{headline}} ({{details.length}})</h3>
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
                    :items="details"
                    :search="search"
                    :hide-actions="details.length <= 5"
                    class="elevation-1"
            >
                <template slot="headerCell" scope="props">
                    <span class="primary--text">{{props.header.text}}</span>
                </template>
                <template slot="items" scope="props">
                    <td>{{props.item.DETAIL_INHALT}}</td>
                    <td>{{props.item.DETAIL_BEMERKUNG}}</td>
                </template>
                <template slot="pageText" scope="{ pageStart, pageStop }">
                    From {{ pageStart }} to {{ pageStop }}
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
    export default class DetailsCard extends Vue {
        @Prop({type: Array})
        details: any;

        @Prop({type: String})
        headline;

        search: string = '';
        headers = [
            {text: 'Eintrag', value: 'DETAIL_INHALT'},
            {text: 'Bemerkung', value: 'DETAIL_BEMERKUNG'},
        ];
    }
</script>