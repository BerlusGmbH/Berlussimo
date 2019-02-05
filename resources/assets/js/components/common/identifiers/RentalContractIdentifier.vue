<template>
    <b-input hide-details>
        <b-icon :tooltips="value.getEntityIconTooltips()" slot="prepend"
                class="identifier-icon">
            {{value.getEntityIcon()}}
        </b-icon>
        <a :href="value.getDetailUrl()">{{String(value)}}</a>
        <template slot="append">
            <v-menu :position-absolutely="true" offset-y v-model="show">
                <v-icon slot="activator" style="font-size: inherit">mdi-arrow-down-drop-circle</v-icon>
                <v-list>
                    <v-list-tile @click="showAccountPDF">
                        <v-list-tile-avatar>
                            <v-icon>mdi-file-pdf</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Mietkonto</v-list-tile-title>
                    </v-list-tile>
                    <v-list-tile @click="contract = true">
                        <v-list-tile-avatar>
                            <v-icon>mdi-file-pdf</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Mietkonto ab...</v-list-tile-title>
                    </v-list-tile>
                    <v-list-tile @click="copyToClipboard(String(value), 'Name')">
                        <v-list-tile-avatar>
                            <v-icon>mdi-content-copy</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Kopieren</v-list-tile-title>
                    </v-list-tile>
                    <v-divider></v-divider>
                    <v-list-tile @click="addDetail">
                        <v-list-tile-avatar>
                            <v-icon>add</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Detail</v-list-tile-title>
                    </v-list-tile>
                </v-list>
            </v-menu>
        </template>
        <app-detail-add-dialog v-if="show || add"
                               :show="add"
                               @show="val => {add = val}"
                               :parent="value"
                               @input="$emit('update')"
        >
        </app-detail-add-dialog>
        <app-rentalcontract-account-show-dialog v-if="show || contract"
                                                :contract="value"
                                                v-model="contract"
        >
        </app-rentalcontract-account-show-dialog>
    </b-input>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";
    import detailAddDialog from "../dialogs/DetailAddDialog.vue";
    import copyToClipboard from "../../../mixins/CopyToClipboard.vue";
    import {RentalContract} from "../../../server/resources";
    import RentalContractAccountDialog from '../../modules/rentalcontract/AccountDialog.vue'

    @Component({
        'components': {
            'app-detail-add-dialog': detailAddDialog,
            'app-rentalcontract-account-show-dialog': RentalContractAccountDialog,
        },
        'mixins': [
            copyToClipboard
        ]
    })
    export default class RentalContractIdentifier extends Vue {
        @Prop({type: Object})
        value: RentalContract;

        show: boolean = false;
        add: boolean = false;
        contract: boolean = false;

        addDetail() {
            this.add = true;
        }

        showAccountPDF() {
            window.open('/mietkontenblatt?anzeigen=mk_pdf&mietvertrag_id=' + this.value.getID())
        }
    }
</script>