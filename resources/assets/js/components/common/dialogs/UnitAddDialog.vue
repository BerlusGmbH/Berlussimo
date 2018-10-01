<template>
    <b-edit-dialog
            lazy
            large
            :positionAbsolutley="positionAbsolutley"
            :positionX="positionX"
            :positionY="positionY"
            :show="show"
            @show="$emit('show', $event)"
            @open="onOpen"
            @save="onSave"
    >
        <slot></slot>
        <v-text-field
                slot="input"
                v-model="value.EINHEIT_KURZNAME"
                label="Name"
                type="text"
                prepend-icon="mdi-alphabetical"
        ></v-text-field>
        <v-text-field
                slot="input"
                v-model="value.EINHEIT_QM"
                label="Fläche"
                type="number"
                step="0.01"
                prepend-icon="mdi-numeric"
                suffix="m²"
        ></v-text-field>
        <v-text-field
                slot="input"
                v-model="value.EINHEIT_LAGE"
                label="Lage"
                type="text"
                prepend-icon="mdi-alphabetical"
        ></v-text-field>
        <v-select v-model="value.TYP"
                  :items="kinds"
                  :prepend-icon="value.getKindIcon()"
                  label="Art"
                  slot="input"
        ></v-select>
        <b-entity-select prepend-icon="mdi-domain"
                         @input="val => value.HAUS_ID = val.HAUS_ID"
                         :value="value.haus"
                         append-icon=""
                         slot="input"
                         :entities="['haus']"
        >
        </b-entity-select>
    </b-edit-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Einheit} from "../../../server/resources";
    import {namespace} from "vuex-class";
    import axios from "../../../libraries/axios";
    import entitySelect from "../../common/EntitySelect.vue";

    const SnackbarModule = namespace('shared/snackbar');
    const RefreshModule = namespace('shared/refresh');

    @Component({components: {'b-entity-select': entitySelect}})
    export default class UnitAddDialog extends Vue {

        value: Einheit = new Einheit();

        @Prop()
        large: boolean;

        @Prop({type: Boolean})
        positionAbsolutley;

        @Prop({type: Number})
        positionX;

        @Prop({type: Number})
        positionY;

        @Prop({type: Boolean})
        show;

        @SnackbarModule.Mutation('updateMessage')
        updateMessage: Function;

        @RefreshModule.Mutation('requestRefresh')
        requestRefresh: Function;

        kinds: Array<string> = [];

        onSave() {
            this.$emit('input', this.value);
            this.value.create().then(() => {
                this.updateMessage('Einheit erstellt.');
                this.requestRefresh();
            }).catch((error) => {
                this.$emit('show', true);
                this.updateMessage('Fehler beim Erstellen der Einheit. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
            });
        }

        onOpen() {
            this.loadPossibleUnitKinds();
        }

        loadPossibleUnitKinds() {
            axios.get(this.value.getApiBaseUrl() + '/possible_unit_kinds').then((response) => {
                this.kinds = response.data;
            });
        }
    }
</script>
