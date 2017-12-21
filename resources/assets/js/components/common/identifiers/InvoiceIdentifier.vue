<template>
    <div class="identifier">
        <div ref="identifier">
            <v-icon>{{value.getEntityIcon()}}</v-icon>
        </div>
        <router-link v-if="$router" :to="{name: 'web.invoices.show', params: { id: String(value.getID()) }}"
        >
            {{String(value)}}
        </router-link>
        <a v-else :href="value.getDetailUrl()">{{String(value)}}</a>
        <v-menu offset-y v-model="show" :position-absolutely="true">
            <v-icon slot="activator" style="font-size: inherit">mdi-arrow-down-drop-circle</v-icon>
            <v-list>
                <v-list-tile @click="openPDF()">
                    <v-list-tile-avatar>
                        <v-icon>mdi-file-pdf</v-icon>
                    </v-list-tile-avatar>
                    <v-list-tile-title>PDF</v-list-tile-title>
                </v-list-tile>
                <v-list-tile @click="openPDF(true)">
                    <v-list-tile-avatar>
                        <v-icon style="color: #895B48;">mdi-file-pdf</v-icon>
                    </v-list-tile-avatar>
                    <v-list-tile-title>PDF</v-list-tile-title>
                </v-list-tile>
                <v-divider></v-divider>
                <v-list-tile @click="copyToClipboard(String(value), 'Rechnungsnummer')">
                    <v-list-tile-avatar>
                        <v-icon>mdi-content-copy</v-icon>
                    </v-list-tile-avatar>
                    <v-list-tile-title>Kopieren</v-list-tile-title>
                </v-list-tile>
                <v-list-tile @click="editInvoice">
                    <v-list-tile-avatar>
                        <v-icon>edit</v-icon>
                    </v-list-tile-avatar>
                    <v-list-tile-title>Bearbeiten</v-list-tile-title>
                </v-list-tile>
            </v-list>
        </v-menu>
        <app-person-edit-dialog v-if="show || edit"
                                :position-absolutely="true"
                                :show="edit"
                                @show="val => {edit = val}"
                                :position-x="x"
                                :position-y="y"
                                :value="value"
                                @input="$emit('input', $event)"
        >
        </app-person-edit-dialog>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";
    import personEditDialog from "../dialogs/PersonEditDialog.vue";
    import jobAddDialog from "../dialogs/JobAddDialog.vue";
    import detailAddDialog from "../dialogs/DetailAddDialog.vue";
    import loginEditDialog from "../dialogs/LoginEditDialog.vue";
    import {Invoice} from "../../../server/resources/models";
    import personMergeDialog from "../dialogs/PersonMergeDialog.vue";
    import copyToClipboard from "../../../mixins/CopyToClipboard.vue";

    @Component({
        'components': {
            'app-person-edit-dialog': personEditDialog,
            'app-job-add-dialog': jobAddDialog,
            'app-detail-add-dialog': detailAddDialog,
            'app-person-merge-dialog': personMergeDialog,
            'app-login-edit-dialog': loginEditDialog
        },
        'mixins': [
            copyToClipboard
        ]
    })
    export default class InvoiceIdentifier extends Vue {
        @Prop({type: Object})
        value: Invoice;

        show: boolean = false;
        edit: boolean = false;

        x: Number = 0;
        y: Number = 0;

        copyToClipboard: Function;

        editInvoice() {
            this.edit = true;
            this.x = this.$refs.identifier ? (this.$refs.identifier as HTMLElement).getBoundingClientRect().left - 20 : this.x;
            this.y = this.$refs.identifier ? (this.$refs.identifier as HTMLElement).getBoundingClientRect().top - 20 : this.y;
        }

        openPDF(noLogo: boolean = false) {
            let url = '/rechnungen?option=anzeigen_pdf&belegnr=' + this.value.BELEG_NR;
            url += noLogo ? '&no_logo' : '';
            window.open(url);
        }
    }
</script>