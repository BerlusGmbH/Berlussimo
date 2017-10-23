<template>
    <v-card>
        <v-card-title>
            <h3 class="headline">Historie ({{audits.length}})</h3>
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
                    :items="audits"
                    :search="search"
                    :hide-actions="audits.length <= 5"
                    class="elevation-1"
            >
                <template slot="items" slot-scope="props">
                    <td class="text-xs-right">{{props.item.created_at}}</td>
                    <td class="text-xs-right">{{props.item.event}}</td>
                    <td>
                        <app-identifier v-if="props.item.user" v-model="props.item.user"></app-identifier>
                    </td>
                    <td class="text-xs-right">{{props.item.ip_address}}</td>
                    <td>
                        <ul>
                            <li v-for="(new_value, key) in props.item.new_values">
                                {{key}}: {{props.item.old_values[key]}}
                                <v-icon v-if="props.item.old_values[key]" style="font-size: inherit">mdi-arrow-right
                                </v-icon>
                                {{new_value}}
                            </li>
                        </ul>
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
    export default class AuditsCard extends Vue {
        @Prop({type: Array})
        audits: any;

        search: string = '';
        headers = [
            {text: 'Datum', sortable: false, value: 'created_at'},
            {text: 'Ereignis', value: 'event'},
            {text: 'Person', value: 'person'},
            {text: 'IP', value: 'ip'},
            {text: 'Änderung', value: 'values'}
        ];
    }
</script>