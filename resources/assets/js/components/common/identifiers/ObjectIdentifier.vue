<template>
    <div class="identifier">
        <v-icon class="identifier-icon">{{value.getEntityIcon()}}</v-icon>
        <a :href="value.getDetailUrl()" ref="identifier">{{String(value)}}</a>
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
        <app-object-edit-dialog v-if="show || edit"
                                :position-absolutely="true"
                                :show="edit"
                                @show="val => {edit = val}"
                                :position-x="x"
                                :position-y="y"
                                :value="value"
                                @input="$emit('input', $event)"
        >
        </app-object-edit-dialog>
        <app-object-copy-dialog v-if="show || copy"
                                :position-absolutely="true"
                                :show="edit"
                                @show="val => {copy = val}"
                                :position-x="x"
                                :position-y="y"
                                :value="value"
                                @input="$emit('input', $event)"
        >
        </app-object-copy-dialog>
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
    import objectEditDialog from "../dialogs/ObjectEditDialog.vue";
    import objectCopyDialog from "../dialogs/ObjectCopyDialog.vue";
    import detailAddDialog from "../dialogs/DetailAddDialog.vue";
    import copyToClipboard from "../../../mixins/CopyToClipboard.vue";
    import hasEMails from "../../../mixins/HasEMails.vue";

    @Component({
        'components': {
            'app-object-edit-dialog': objectEditDialog,
            'app-object-copy-dialog': objectCopyDialog,
            'app-detail-add-dialog': detailAddDialog
        },
        'mixins': [
            copyToClipboard,
            hasEMails
        ]
    })
    export default class ObjectIdentifier extends Vue {
        @Prop()
        value;

        show: boolean = false;
        edit: boolean = false;
        add: boolean = false;
        copy: boolean = false;

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

        editObject() {
            this.edit = true;
            this.x = this.$refs.identifier ? (this.$refs.identifier as HTMLElement).getBoundingClientRect().left - 20 : this.x;
            this.y = this.$refs.identifier ? (this.$refs.identifier as HTMLElement).getBoundingClientRect().top - 20 : this.y;
        }

        copyObject() {
            this.copy = true;
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