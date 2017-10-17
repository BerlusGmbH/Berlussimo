<template>
    <v-card>
        <v-card-title>
            <h3 class="headline">Berichte</h3>
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
                    :items="items"
                    :search="search"
                    :hide-actions="true"
                    class="elevation-1"
            >
                <template slot="items" scope="props">
                    <template v-if="Array.isArray(props.item.report)">
                        <td>
                            <div v-for="r in props.item.report" v-html="r"></div>
                        </td>
                    </template>
                    <template v-else>
                        <td>
                            <div v-html="props.item.report"></div>
                        </td>
                    </template>
                    <td>{{props.item.summary}}</td>
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
    import {Objekt} from "../../../server/resources/models";

    @Component
    export default class AssignmentsCard extends Vue {
        @Prop({type: Object})
        object: Objekt;

        @Prop({type: String})
        headline: string;

        search: string = '';
        headers = [
            {text: 'Bericht', value: 'report'},
            {text: 'Beschreibung', value: 'summary'},
        ];

        get items() {
            let items: Array<Object> = [];
            items.push({
                'report': '<a target="_blank" href="/objekte/legacy?objekte_raus=checkliste&objekt_id='
                + this.object.OBJEKT_ID + '">Hauswart Checkliste<i class="mdi mdi-file-pdf"></i></a>',
                'summary': 'Checkliste für einen Rundgang im Objekt'
            });
            items.push({
                'report': '<a target="_blank" href="/objekte/legacy?objekte_raus=mietaufstellung&objekt_id='
                + this.object.OBJEKT_ID + '">Mietaufstellung<i class="mdi mdi-file-pdf"></i></a>',
                'summary': 'Mietaufstellung des aktuellen Monats'
            });
            items.push({
                'report': [
                    '<a target="_blank" href="/objekte/legacy?objekte_raus=mietaufstellung_m_j&objekt_id='
                    + this.object.OBJEKT_ID + '&monat=' + new Date().getMonth() + '&jahr=' + new Date().getFullYear()
                    + '">Mietaufstellung Monatsjournal<i class="mdi mdi-file-pdf"></i></a>',
                    '<a target="_blank" href="/objekte/legacy?objekte_raus=mietaufstellung_m_j&objekt_id='
                    + this.object.OBJEKT_ID + '&monat=' + new Date().getMonth() + '&jahr=' + new Date().getFullYear()
                    + '&XLS"><i class="mdi mdi-file-excel"></i></a>'
                ],
                'summary': 'Mietaufstellung des aktuellen Monats in der Journalansicht'
            });
            items.push({
                'report': '<a target="_blank" href="/mietkontenblatt?anzeigen=alle_mkb&objekt_id='
                + this.object.OBJEKT_ID + '">Alle Mietkontenblätter<i class="mdi mdi-file-pdf"></i></a>',
                'summary': 'Mietkontenblätter aller Mieter'
            });
            items.push({
                'report': '<a target="_blank" href="/einheiten/legacy?einheit_raus=mieterliste_aktuell&objekt_id='
                + this.object.OBJEKT_ID + '">Mieterkontakte<i class="mdi mdi-file-pdf"></i></a>',
                'summary': 'Kontaktliste aller Mieter'
            });
            items.push({
                'report': '<a target="_blank" href="/objekte/legacy?objekte_raus=mietaufstellung_j&objekt_id='
                + this.object.OBJEKT_ID
                + '&jahr=' + (new Date().getFullYear() - 1)
                + '">SOLL/IST<i class="mdi mdi-file-pdf"></i></a>',
                'summary': 'Mieten SOLL/IST kumuliert über das vorherige Jahr'
            });
            items.push({
                'report': '<a target="_blank" href="/objekte/legacy?objekte_raus=stammdaten_pdf&objekt_id='
                + this.object.OBJEKT_ID
                + '">Stammdaten<i class="mdi mdi-file-pdf"></i></a>',
                'summary': 'Stammdaten des Objektes'
            });
            return items;
        }
    }
</script>