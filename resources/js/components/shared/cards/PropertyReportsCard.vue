<template>
    <v-card>
        <v-card-title>
            <h3 class="headline">Berichte</h3>
        </v-card-title>
        <v-data-table
            :headers="headers"
            :hide-actions="true"
            :items="items"
            class="elevation-1"
        >
            <template slot="items" slot-scope="props">
                <template v-if="props.item.html">
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
                </template>
                <template v-else>
                    <td><a @click="clickOnReport(props.item.report.show)">{{props.item.report.name}}</a>
                        <v-icon style="font-size: inherit">{{props.item.report.icon}}</v-icon>
                    </td>
                </template>
                <td>{{props.item.summary}}</td>
            </template>
            <template slot="pageText" slot-scope="{ pageStart, pageStop }">
                Von {{ pageStart }} bis {{ pageStop }}
            </template>
        </v-data-table>
        <b-revenue-report-dialog :property="property" v-model="revenueReport"></b-revenue-report-dialog>
    </v-card>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Property} from "../../../models";
    import RevenueReportDialog from "../../modules/property/dialogs/RevenueReportDialog.vue";

    @Component({
        'components': {
            'b-revenue-report-dialog': RevenueReportDialog
        }
    })
    export default class PropertyReportsCard extends Vue {
        @Prop({type: Object})
        property: Property;

        @Prop({type: String})
        headline: string;

        headers = [
            {text: 'Bericht', value: 'report'},
            {text: 'Beschreibung', value: 'summary'},
        ];

        revenueReport: boolean = false;

        get items() {
            let items: Object[] = [];
            items.push({
                'report': '<a target="_blank" href="/properties/legacy?objekte_raus=checkliste&objekt_id='
                    + this.property.id + '">Hauswart Checkliste<i class="mdi mdi-file-pdf"></i></a>',
                'summary': 'Checkliste für einen Rundgang im Objekt',
                'html': true
            });
            items.push({
                'report': '<a target="_blank" href="/properties/legacy?objekte_raus=mietaufstellung&objekt_id='
                    + this.property.id + '">Mietaufstellung<i class="mdi mdi-file-pdf"></i></a>',
                'summary': 'Mietaufstellung des aktuellen Monats',
                'html': true
            });
            items.push({
                'report': [
                    '<a target="_blank" href="/properties/legacy?objekte_raus=mietaufstellung_m_j&objekt_id='
                    + this.property.id + '&monat=' + new Date().getMonth() + '&jahr=' + new Date().getFullYear()
                    + '">Mietaufstellung Monatsjournal<i class="mdi mdi-file-pdf"></i></a>',
                    '<a target="_blank" href="/properties/legacy?objekte_raus=mietaufstellung_m_j&objekt_id='
                    + this.property.id + '&monat=' + new Date().getMonth() + '&jahr=' + new Date().getFullYear()
                    + '&XLS"><i class="mdi mdi-file-excel"></i></a>'
                ],
                'summary': 'Mietaufstellung des aktuellen Monats in der Journalansicht',
                'html': true
            });
            items.push({
                'report': '<a target="_blank" href="/mietkontenblatt?anzeigen=alle_mkb&objekt_id='
                    + this.property.id + '">Alle Mietkontenblätter<i class="mdi mdi-file-pdf"></i></a>',
                'summary': 'Mietkontenblätter aller Mieter',
                'html': true
            });
            items.push({
                'report': '<a target="_blank" href="/units/legacy?einheit_raus=mieterliste_aktuell&objekt_id='
                    + this.property.id + '">Mieterkontakte<i class="mdi mdi-file-pdf"></i></a>',
                'summary': 'Kontaktliste aller Mieter',
                'html': true
            });
            items.push({
                'report': '<a target="_blank" href="/properties/legacy?objekte_raus=stammdaten_pdf&objekt_id='
                    + this.property.id
                    + '">Stammdaten<i class="mdi mdi-file-pdf"></i></a>',
                'summary': 'Stammdaten des Objektes',
                'html': true
            });
            items.push({
                'report': {
                    'name': 'Umsatzübersicht',
                    'icon': 'mdi-file-excel',
                    'show': 'revenueReport'
                },
                'summary': 'Mietumsatz kumuliert nach periodenrelevanten Mietverträgen.',
                'html': false
            });
            return items;
        }

        clickOnReport(show) {
            this[show] = true;
        }
    }
</script>
