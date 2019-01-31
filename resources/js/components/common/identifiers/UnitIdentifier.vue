<template>
    <b-input hide-details>
        <template slot="prepend">
            <b-icon :tooltips="value.getNoteTooltips()" v-if="value.hasNotes()"
                    color="error">
                mdi-alert
            </b-icon>
            <b-icon :tooltips="value.getEntityIconTooltips()">
                {{value.getEntityIcon()}}
            </b-icon>
            <b-icon :tooltips="value.getKindTooltips()" ref="identifier">{{value.getKindIcon()}}</b-icon>
        </template>
        <router-link v-if="$router"
                     :to="{name: 'web.units.show', params: { id: String(value.getID()) }}"
        >
            {{String(value)}}
        </router-link>
        <a v-else :href="value.getDetailUrl()">{{String(value)}}</a>
        <template slot="append">
            <v-menu :position-absolutely="true" offset-y v-model="show">
                <v-icon slot="activator" style="font-size: inherit">mdi-arrow-down-drop-circle</v-icon>
                <v-list>
                    <v-list-tile @click="sendEMails('tenants')">
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
                    <v-list-tile @click="sendEMails('owners')">
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
                    <v-list-tile @click="copyToClipboard(value.EINHEIT_KURZNAME, 'Einheitname')">
                        <v-list-tile-avatar>
                            <v-icon>mdi-content-copy</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Kopieren</v-list-tile-title>
                    </v-list-tile>
                    <v-list-tile @click="updateUnit">
                        <v-list-tile-avatar>
                            <v-icon>edit</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Bearbeiten</v-list-tile-title>
                    </v-list-tile>
                    <v-divider></v-divider>
                    <v-list-tile @click="createDetail">
                        <v-list-tile-avatar>
                            <v-icon>add</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Detail</v-list-tile-title>
                    </v-list-tile>
                </v-list>
            </v-menu>
            <b-update-unit-dialog :show="update"
                                  :unitId="value.id"
                                  @input="$emit('input', $event)"
                                  @close="() => {update = false}"
                                  @show="val => {update = val}"
                                  v-if="show || update"
            >
            </b-update-unit-dialog>
            <b-create-detail-dialog :parent="value"
                                    :show="create"
                                    @close="() => {create = false}"
                                    @input="$emit('update')"
                                    @show="val => {create = val}"
                                    v-if="show || create"
            >
            </b-create-detail-dialog>
        </template>
    </b-input>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop, Watch} from "vue-property-decorator";
    import UpdateDialog from "../../modules/unit/dialogs/UpdateDialog.vue";
    import CreateDetailDialog from "../dialogs/DetailAddDialog.vue";
    import CopyToClipboard from "../../../mixins/CopyToClipboard.vue";
    import HasEMails from "../../../mixins/HasEMails.vue";

    @Component({
        'components': {
            'b-update-unit-dialog': UpdateDialog,
            'b-create-detail-dialog': CreateDetailDialog
        },
        'mixins': [
            CopyToClipboard,
            HasEMails
        ]
    })
    export default class UnitIdentifier extends Vue {
        @Prop()
        value;

        show: boolean = false;
        update: boolean = false;
        create: boolean = false;

        copyToClipboard: Function;

        loadEMails: Function;

        @Watch('show')
        onShowChange(val) {
            if (val) {
                this.loadEMails();
            }
        }

        updateUnit() {
            this.update = true;
        }

        createDetail() {
            this.create = true;
        }
    }
</script>