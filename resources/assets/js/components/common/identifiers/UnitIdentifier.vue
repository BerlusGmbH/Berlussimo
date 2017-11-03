<template>
    <div class="identifier">
        <div v-if="value.hasNotes()">
            <v-icon color="error">mdi-alert</v-icon>
        </div>
        <div>
            <v-icon class="identifier-icon">{{value.getEntityIcon()}}</v-icon>
        </div>
        <div ref="identifier">
            <v-icon>{{value.getKindIcon()}}</v-icon>
        </div>
        <router-link v-if="$router"
                     :to="{name: 'web.units.show', params: { id: String(value.getID()) }}"
        >
            {{String(value)}}
        </router-link>
        <a v-else :href="value.getDetailUrl()">{{String(value)}}</a>
        <v-menu offset-y v-model="show" :position-absolutely="true">
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
                <v-list-tile @click="copyToClipboard(value.EINHEIT_KURZNAME, 'Einheitname')">
                    <v-list-tile-avatar>
                        <v-icon>mdi-content-copy</v-icon>
                    </v-list-tile-avatar>
                    <v-list-tile-title>Kopieren</v-list-tile-title>
                </v-list-tile>
                <v-list-tile @click="editUnit">
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
        <app-unit-edit-dialog v-if="show || edit"
                              :position-absolutely="true"
                              :show="edit"
                              @show="val => {edit = val}"
                              :position-x="x"
                              :position-y="y"
                              :value="value"
                              @input="$emit('input', $event)"
        >
        </app-unit-edit-dialog>
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
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop, Watch} from "vue-property-decorator";
    import unitEditDialog from "../dialogs/UnitEditDialog.vue";
    import detailAddDialog from "../dialogs/DetailAddDialog.vue";
    import copyToClipboard from "../../../mixins/CopyToClipboard.vue";
    import hasEMails from "../../../mixins/HasEMails.vue";

    @Component({
        'components': {
            'app-unit-edit-dialog': unitEditDialog,
            'app-detail-add-dialog': detailAddDialog
        },
        'mixins': [
            copyToClipboard,
            hasEMails
        ]
    })
    export default class UnitIdentifier extends Vue {
        @Prop()
        value;

        show: boolean = false;
        edit: boolean = false;
        add: boolean = false;

        x: Number = 0;
        y: Number = 0;

        copyToClipboard: Function;

        loadEMails: Function;

        @Watch('show')
        onShowChange(val) {
            if (val) {
                this.loadEMails('tenants');
                this.loadEMails('owners');
            }
        }

        editUnit() {
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