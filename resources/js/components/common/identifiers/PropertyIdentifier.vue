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
        <router-link :to="{name: 'web.properties.show', params: { id: String(value.getID()) }}" v-if="$router"
        >
            {{String(value)}}
        </router-link>
        <a :href="value.getDetailUrl()" v-else>{{String(value)}}</a>
        <template slot="append">
            <v-menu :position-absolutely="true" offset-y v-model="show">
                <v-icon slot="activator" style="font-size: inherit; vertical-align: baseline">mdi-arrow-down-drop-circle
                </v-icon>
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
                            <v-progress-circular indeterminate
                                                 v-if="loading"
                                                 color="primary"
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
                            <v-progress-circular indeterminate
                                                 v-if="loading"
                                                 color="primary"
                            ></v-progress-circular>
                            <v-chip color="primary" v-else>
                                {{ countEMails('owners') }}
                            </v-chip>
                        </v-list-tile-avatar>
                    </v-list-tile>
                    <v-list-tile @click="copyObject">
                        <v-list-tile-avatar>
                            <v-icon>mdi-city</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>
                            Kopieren
                        </v-list-tile-title>
                    </v-list-tile>
                    <v-divider></v-divider>
                    <v-list-tile @click="copyToClipboard(String(value), 'Objektname')">
                        <v-list-tile-avatar>
                            <v-icon>mdi-content-copy</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Kopieren</v-list-tile-title>
                    </v-list-tile>
                    <v-list-tile @click="editObject">
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
            <b-update-property-dialog :show="edit"
                                      :propertyId="value.id"
                                      @close="() => {edit = false}"
                                      @show="val => {edit = val}"
                                      v-if="show || edit"
            >
            </b-update-property-dialog>
            <b-detail-add-dialog :parent="value"
                                 :show="add"
                                 @close="() => {add = false}"
                                 @input="$emit('update')"
                                 @show="val => {add = val}"
                                 v-if="show || add"
            >
            </b-detail-add-dialog>
            <b-copy-property-dialog :property="value"
                                    v-if="show || copy"
                                    v-model="copy"
            >
            </b-copy-property-dialog>
        </template>
    </b-input>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop, Watch} from "vue-property-decorator";
    import UpdatePropertyDialog from "../../modules/property/dialogs/UpdateDialog.vue";
    import CopyPropertyDialog from "../../modules/property/dialogs/CopyDialog.vue";
    import DetailAddDialog from "../dialogs/DetailAddDialog.vue";
    import CopyToClipboard from "../../../mixins/CopyToClipboard.vue";
    import HasEMails from "../../../mixins/HasEMails.vue";

    @Component({
        'components': {
            'b-update-property-dialog': UpdatePropertyDialog,
            'b-copy-property-dialog': CopyPropertyDialog,
            'b-detail-add-dialog': DetailAddDialog
        },
        'mixins': [
            CopyToClipboard,
            HasEMails
        ]
    })
    export default class PropertyIdentifier extends Vue {
        @Prop()
        value;

        show: boolean = false;
        edit: boolean = false;
        add: boolean = false;
        copy: boolean = false;

        loadEMails: Function;

        @Watch('show')
        onShowChange(val) {
            if (val) {
                this.loadEMails();
            }
        }

        editObject() {
            this.edit = true;
        }

        copyObject() {
            this.copy = true;
        }

        addDetail() {
            this.add = true;
        }
    }
</script>