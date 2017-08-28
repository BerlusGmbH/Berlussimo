<template>
    <v-card>
        <v-card-title>
            <h3 class="headline">{{headline}} ({{roles.length}})</h3>
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
                    :items="roles"
                    :search="search"
                    :hide-actions="roles.length <= 5"
                    class="elevation-1"
            >
                <template slot="items" scope="props">
                    <td>{{props.item.name}}</td>
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
    export default class RolesCard extends Vue {
        @Prop({type: Array})
        roles: any;

        @Prop({type: String})
        headline: string;

        search: string = '';
        headers = [
            {text: 'Rolle', value: ''},
        ];
    }
</script>