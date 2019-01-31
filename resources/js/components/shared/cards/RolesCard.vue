<template>
    <v-card>
        <v-card-title>
            <h3 class="headline">{{headline}}</h3>
            <v-chip color="primary">
                <h3>{{roles.length}}</h3>
            </v-chip>
            <v-spacer></v-spacer>
            <v-text-field
                append-icon="search"
                hide-details
                label="Search"
                single-line
                v-model="search"
            ></v-text-field>
        </v-card-title>
        <v-data-table
            :headers="headers"
            :hide-actions="roles.length <= 5"
            :items="roles"
            :search="search"
            class="elevation-1"
        >
            <template slot="items" slot-scope="props">
                <td>{{props.item.name}}</td>
            </template>
            <template slot="pageText" slot-scope="{ pageStart, pageStop }">
                Von {{ pageStart }} bis {{ pageStop }}
            </template>
        </v-data-table>
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
            {text: 'Rolle', value: 'name'},
        ];
    }
</script>
