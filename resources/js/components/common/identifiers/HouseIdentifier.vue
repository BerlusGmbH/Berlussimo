<template>
    <b-input hide-details>
        <template slot="prepend">
            <div v-if="value.hasNotes()">
                <b-icon :tooltips="value.getNoteTooltips()" color="error">mdi-alert</b-icon>
            </div>
            <div ref="identifier">
                <b-icon :tooltips="value.getEntityIconTooltips()" class="identifier-icon">{{value.getEntityIcon()}}
                </b-icon>
            </div>
        </template>
        <router-link v-if="$router" :to="{name: 'web.houses.show', params: { id: String(value.getID()) }}"
        >
            {{String(value)}}
        </router-link>
        <a v-else :href="value.getDetailUrl()">{{String(value)}}</a>
        <template slot="append">
            <v-menu :position-absolutely="true" offset-y v-model="show">
                <v-icon slot="activator" style="font-size: inherit">mdi-arrow-down-drop-circle</v-icon>
                <v-list>
                    <v-list-tile :disabled="!countEMails('tenants')"
                                 @click="sendEMails('tenants')"
                    >
                        <v-list-tile-avatar>
                            <v-icon>mdi-mail-ru</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-content>
                            E-Mail an Mieter
                        </v-list-tile-content>
                        <v-list-tile-avatar>
                            <v-progress-circular color="primary"
                                                 indeterminate
                                                 v-if="loading"
                            ></v-progress-circular>
                            <v-chip color="primary" v-else>
                                {{ countEMails('tenants') }}
                            </v-chip>
                        </v-list-tile-avatar>
                    </v-list-tile>
                    <v-list-tile :disabled="!countEMails('owners')"
                                 @click="sendEMails('owners')"
                    >
                        <v-list-tile-avatar>
                            <v-icon>mdi-mail-ru</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-content>
                            E-Mail an WEG-Eigentümer
                        </v-list-tile-content>
                        <v-list-tile-avatar>
                            <v-progress-circular color="primary"
                                                 indeterminate
                                                 v-if="loading"
                            ></v-progress-circular>
                            <v-chip color="primary" v-else>
                                {{ countEMails('owners') }}
                            </v-chip>
                        </v-list-tile-avatar>
                    </v-list-tile>
                    <v-divider></v-divider>
                    <v-list-tile @click="copyToClipboard(String(value), 'Hausname')">
                        <v-list-tile-avatar>
                            <v-icon>mdi-content-copy</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Kopieren</v-list-tile-title>
                    </v-list-tile>
                    <v-list-tile @click="updateHouse">
                        <v-list-tile-avatar>
                            <v-icon>edit</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Bearbeiten</v-list-tile-title>
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
            <b-update-house-dialog :houseId="value.id"
                                   :show="update"
                                   @close="() => {update = false}"
                                   @show="val => {update = val}"
                                   v-if="show || update"
            >
            </b-update-house-dialog>
            <app-detail-add-dialog :parent="value"
                                   :show="add"
                                   @close="() => {add = false}"
                                   @input="$emit('update')"
                                   @show="val => {add = val}"
                                   v-if="show || add"
            >
            </app-detail-add-dialog>
        </template>
    </b-input>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop, Watch} from "vue-property-decorator";
    import UpdateHouseDialog from "../../modules/house/dialogs/UpdateDialog.vue";
    import DetailAddDialog from "../dialogs/DetailAddDialog.vue";
    import CopyToClipboard from "../../../mixins/CopyToClipboard.vue";
    import HasEMails from "../../../mixins/HasEMails.vue";

    @Component({
        'components': {
            'b-update-house-dialog': UpdateHouseDialog,
            'app-detail-add-dialog': DetailAddDialog
        },
        'mixins': [
            CopyToClipboard,
            HasEMails
        ]
    })
    export default class HouseIdentifier extends Vue {
        @Prop()
        value;

        show: boolean = false;
        update: boolean = false;
        add: boolean = false;

        loadEMails: Function;

        @Watch('show')
        onShowChange(val) {
            if (val) {
                this.loadEMails();
            }
        }

        updateHouse() {
            this.update = true;
        }

        addDetail() {
            this.add = true;
        }
    }
</script>