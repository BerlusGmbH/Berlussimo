<template>
    <v-card>
        <v-card-title>
            <template v-if="query">
                <router-link :to="{name: 'web.persons.index', query: query}"
                >
                    <h3 class="headline">{{headline}}</h3>
                </router-link>
                <v-chip color="primary">
                    {{persons.length}}
                </v-chip>
            </template>
            <template v-else>
                <h3 class="headline">{{headline}}</h3>
                <v-chip color="primary">
                    <h3>{{persons.length}}</h3>
                </v-chip>
            </template>
        </v-card-title>
        <v-data-table
            :headers="headers"
            :hide-actions="persons.length <= 5"
            :items="persons"
            disable-initial-sort
        >
            <template slot="items" slot-scope="props">
                <td>
                    <app-identifier v-model="props.item"></app-identifier>
                </td>
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
    export default class PersonsCard extends Vue {
        @Prop({type: Array})
        persons: any;

        @Prop({type: String})
        headline;

        @Prop({
            type: Object, default: () => {
            }
        })
        query;

        headers = [
            {text: 'Person', value: 'id'}
        ];
    }
</script>
