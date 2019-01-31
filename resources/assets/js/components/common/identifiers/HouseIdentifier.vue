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
                    <v-list-tile @click="sendEMails('tenants')">
                        <v-list-tile-avatar>
                            <v-icon>mdi-mail-ru</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>
                            E-Mail an Mieter
                            {{ countEMails('tenants') }}
                        </v-list-tile-title>
                    </v-list-tile>
                    <v-list-tile @click="sendEMails('owners')">
                        <v-list-tile-avatar>
                            <v-icon>mdi-mail-ru</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>
                            E-Mail an WEG-Eigentümer
                            {{ countEMails('owners') }}
                        </v-list-tile-title>
                    </v-list-tile>
                    <v-divider></v-divider>
                    <v-list-tile @click="copyToClipboard(String(value), 'Hausname')">
                        <v-list-tile-avatar>
                            <v-icon>mdi-content-copy</v-icon>
                        </v-list-tile-avatar>
                        <v-list-tile-title>Kopieren</v-list-tile-title>
                    </v-list-tile>
                    <v-list-tile @click="editHouse">
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
            <app-house-edit-dialog :position-absolutely="true"
                                   :position-x="x"
                                   :position-y="y"
                                   :show="edit"
                                   :value="value"
                                   @input="$emit('input', $event)"
                                   @show="val => {edit = val}"
                                   v-if="show || edit"
            >
            </app-house-edit-dialog>
            <app-detail-add-dialog :parent="value"
                                   :position-absolutely="true"
                                   :position-x="x"
                                   :position-y="y"
                                   :show="add"
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
    import houseEditDialog from "../dialogs/HouseEditDialog.vue";
    import detailAddDialog from "../dialogs/DetailAddDialog.vue";
    import copyToClipboard from "../../../mixins/CopyToClipboard.vue";
    import hasEMails from "../../../mixins/HasEMails.vue";

    @Component({
        'components': {
            'app-house-edit-dialog': houseEditDialog,
            'app-detail-add-dialog': detailAddDialog
        },
        'mixins': [
            copyToClipboard,
            hasEMails
        ]
    })
    export default class HouseIdentifier extends Vue {
        @Prop()
        value;

        show: boolean = false;
        edit: boolean = false;
        add: boolean = false;

        x: Number = 0;
        y: Number = 0;

        loadEMails: Function;

        @Watch('show')
        onShowChange(val) {
            if (val) {
                this.loadEMails('tenants');
                this.loadEMails('owners');
            }
        }

        editHouse() {
            this.edit = true;
            this.x = this.$refs.identifier ? (this.$refs.identifier as HTMLElement).getBoundingClientRect().left - 20 : this.x;
            this.y = this.$refs.identifier ? (this.$refs.identifier as HTMLElement).getBoundingClientRect().top - 20 : this.y;
        }

        addDetail() {
            this.add = true;
            this.x = this.$refs.identifier ? (this.$refs.identifier as HTMLElement).getBoundingClientRect().left - 20 : this.x;
            this.y = this.$refs.identifier ? (this.$refs.identifier as HTMLElement).getBoundingClientRect().top - 20 : this.y;
        }
    }
</script>