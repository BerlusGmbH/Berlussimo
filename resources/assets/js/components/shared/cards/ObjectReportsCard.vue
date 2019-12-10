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
        </v-card-text>
        <b-revenue-report-dialog v-model="revenueReport" :object="object"></b-revenue-report-dialog>
        <b-mod-base-data-report-dialog :object="object" v-model="modBaseDataReport"></b-mod-base-data-report-dialog>
    </v-card>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Objekt} from "../../../server/resources";
    import RevenueReportDialog from "../../modules/object/dialogs/RevenueReportDialog.vue";
    import ModBaseDataReportDialog from "../../modules/object/dialogs/ModBaseDataReportDialog.vue";

    @Component({
        'components': {
            'b-revenue-report-dialog': RevenueReportDialog,
            'b-mod-base-data-report-dialog': ModBaseDataReportDialog
        }
    })
    export default class ObjectReportsCard extends Vue {
        @Prop({type: Object})
        object: Objekt;

        @Prop({type: String})
        headline: string;

        search: string = '';
        headers = [
            {text: 'Bericht', value: 'report'},
            {text: 'Beschreibung', value: 'summary'},
        ];

        revenueReport: boolean = false;
        modBaseDataReport: boolean = false;

        get items() {
            let items: Object[] = [];
            items.push({
                'report': '<a target="_blank" href="/objects/legacy?objekte_raus=checkliste&objekt_id='
                + this.object.OBJEKT_ID + '">Hauswart Checkliste<i class="mdi mdi-file-pdf"></i></a>',
                'summary': 'Checkliste für einen Rundgang im Objekt',
                'html': true
            });
            items.push({
                'report': '<a target="_blank" href="/objects/legacy?objekte_raus=mietaufstellung&objekt_id='
                + this.object.OBJEKT_ID + '">Mietaufstellung<i class="mdi mdi-file-pdf"></i></a>',
                'summary': 'Mietaufstellung des aktuellen Monats',
                'html': true
            });
            items.push({
                'report': [
                    '<a target="_blank" href="/objects/legacy?objekte_raus=mietaufstellung_m_j&objekt_id='
                    + this.object.OBJEKT_ID + '&monat=' + new Date().getMonth() + '&jahr=' + new Date().getFullYear()
                    + '">Mietaufstellung Monatsjournal<i class="mdi mdi-file-pdf"></i></a>',
                    '<a target="_blank" href="/objects/legacy?objekte_raus=mietaufstellung_m_j&objekt_id='
                    + this.object.OBJEKT_ID + '&monat=' + new Date().getMonth() + '&jahr=' + new Date().getFullYear()
                    + '&XLS"><i class="mdi mdi-file-excel"></i></a>'
                ],
                'summary': 'Mietaufstellung des aktuellen Monats in der Journalansicht',
                'html': true
            });
            items.push({
                'report': '<a target="_blank" href="/mietkontenblatt?anzeigen=alle_mkb&objekt_id='
                + this.object.OBJEKT_ID + '">Alle Mietkontenblätter<i class="mdi mdi-file-pdf"></i></a>',
                'summary': 'Mietkontenblätter aller Mieter',
                'html': true
            });
            items.push({
                'report': '<a target="_blank" href="/units/legacy?einheit_raus=mieterliste_aktuell&objekt_id='
                + this.object.OBJEKT_ID + '">Mieterkontakte<i class="mdi mdi-file-pdf"></i></a>',
                'summary': 'Kontaktliste aller Mieter',
                'html': true
            });
            items.push({
                'report': '<a target="_blank" href="/objects/legacy?objekte_raus=stammdaten_pdf&objekt_id='
                + this.object.OBJEKT_ID
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
            items.push({
                'report': {
                    'name': 'MOD Basisdaten',
                    'icon': 'mdi-file-excel',
                    'show': 'modBaseDataReport'
                },
                'summary': 'MOD Basisdaten zum Erstellen von Serienbriefen.',
                'html': false
            });
            return items;
        }

        clickOnReport(show) {
            this[show] = true;
        }
    }
</script>