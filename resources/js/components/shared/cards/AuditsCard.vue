<template>
    <v-card>
        <v-card-title>
            <h3 class="headline">Historie</h3>
            <v-chip color="primary">
                <h3>{{audits.length}}</h3>
            </v-chip>
        </v-card-title>
        <v-data-table
            :headers="headers"
            :hide-actions="audits.length <= 5"
            :items="audits"
            disable-initial-sort
        >
            <template slot="items" slot-scope="props">
                <td class="text-xs-right">{{props.item.createdAt}}</td>
                <td class="text-xs-right">{{props.item.event}}</td>
                <td>
                    <app-identifier v-if="props.item.user" v-model="props.item.user"></app-identifier>
                </td>
                <td class="text-xs-right">{{props.item.ipAddress}}</td>
                <td>
                    <ul>
                        <template v-for="(new_value, key) in props.item.new">
                            <li v-if="new_value && key !== '__typename'">
                                {{key}}: {{props.item.old[key]}}
                                <v-icon style="font-size: inherit" v-if="props.item.old[key]">mdi-arrow-right
                                </v-icon>
                                {{new_value}}
                            </li>
                        </template>
                    </ul>
                </td>
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
    export default class AuditsCard extends Vue {
        @Prop({type: Array})
        audits: any;

        @Prop({type: String})
        personId: string;

        search: string = '';
        headers = [
            {text: 'Datum', value: 'createdAt'},
            {text: 'Ereignis', value: 'event'},
            {text: 'Person', value: 'user'},
            {text: 'IP', value: 'ipAddress'},
            {text: 'Änderung', value: 'new'}
        ];
    }
</script>
