<template>
    <div class="identifier">
        <b-icon :tooltips="value.getEntityIconTooltips()" class="identifier-icon">{{value.getEntityIcon()}}</b-icon>
        <a :href="value.getDetailUrl()">{{String(value)}}</a>
        <v-menu offset-y v-model="show" :position-absolutely="true">
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
        <app-detail-add-dialog v-if="show || add"
                               :position-absolutely="true"
                               :show="add"
                               @show="val => {add = val}"
                               :position-x="x"
                               :position-y="y"
                               :parent="value"
                               @input="$emit('update')"
        >
        </app-detail-add-dialog>
        <app-purchasecontract-account-show-dialog v-if="show || contract"
                                                  :contract="value"
                                                  v-model="contract"
        >
        </app-purchasecontract-account-show-dialog>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";
    import detailAddDialog from "../dialogs/DetailAddDialog.vue";
    import copyToClipboard from "../../../mixins/CopyToClipboard.vue";
    import {PurchaseContract} from "../../../server/resources";
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

        x: number = 0;
        y: number = 0;

        addDetail() {
            this.add = true;
            this.x = this.$refs.identifier ? (this.$refs.identifier as HTMLElement).getBoundingClientRect().left - 20 : this.x;
            this.y = this.$refs.identifier ? (this.$refs.identifier as HTMLElement).getBoundingClientRect().top - 20 : this.y;
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