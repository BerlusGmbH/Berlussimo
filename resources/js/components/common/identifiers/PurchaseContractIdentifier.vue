<template>
    <b-input hide-details>
        <b-icon :tooltips="value.getEntityIconTooltips()" class="identifier-icon" slot="prepend">
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
                        <v-list-tile-title>Hausgeldkonto</v-list-tile-title>
                    </v-list-tile>
                    <v-list-tile @click="contract = true">
                        <v-list-tile-avatar>
                            <v-icon>mdi-file-pdf</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Hausgeldkonto für...</v-list-tile-title>
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
            <app-detail-add-dialog :parent="value"
                                   :show="add"
                                   @input="$emit('update')"
                                   @show="val => {add = val}"
                                   @close="() => {add = false}"
                                   v-if="show || add"
            >
            </app-detail-add-dialog>
            <app-purchasecontract-account-show-dialog :contract="value"
                                                      v-if="show || contract"
                                                      v-model="contract"
            >
            </app-purchasecontract-account-show-dialog>
        </template>
    </b-input>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";
    import detailAddDialog from "../dialogs/DetailAddDialog.vue";
    import copyToClipboard from "../../../mixins/CopyToClipboard.vue";
    import {PurchaseContract} from "../../../models";
    import PurchaseContractAccountDialog from '../../modules/purchasecontract/AccountDialog.vue'

    @Component({
        'components': {
            'app-detail-add-dialog': detailAddDialog,
            'app-purchasecontract-account-show-dialog': PurchaseContractAccountDialog,
        },
        'mixins': [
            copyToClipboard
        ]
    })
    export default class PurchaseContractIdentifier extends Vue {
        @Prop({type: Object})
        value: PurchaseContract;

        show: boolean = false;
        add: boolean = false;
        contract: boolean = false;

        addDetail() {
            this.add = true;
        }

        showAccountPDF() {
            window.open(
                '/weg?option=hg_kontoauszug'
                + '&eigentuemer_id=' + this.value.getID()
                + '&jahr=' + (new Date).getFullYear()
            );
        }
    }
</script>