<template>
    <b-edit-dialog
            lazy
            large
            :positionAbsolutley="positionAbsolutley"
            :positionX="positionX"
            :positionY="positionY"
            :show="show"
            @show="$emit('show', $event)"
            @save="onSave"
    >
        <slot></slot>
        <v-text-field
                slot="input"
                v-model="value.HAUS_STRASSE"
                label="Straße"
                type="text"
                prepend-icon="mdi-alphabetical"
        ></v-text-field>
        <v-text-field
                slot="input"
                v-model="value.HAUS_NUMMER"
                label="Nummer"
                type="text"
                prepend-icon="mdi-alphabetical"
        ></v-text-field>
        <v-text-field
                slot="input"
                v-model="value.HAUS_PLZ"
                label="Postleitzahl"
                type="number"
                prepend-icon="mdi-numeric"
        ></v-text-field>
        <v-text-field
                slot="input"
                v-model="value.HAUS_STADT"
                label="Stadt"
                type="text"
                prepend-icon="mdi-alphabetical"
        ></v-text-field>
        <b-entity-select prepend-icon="mdi-city"
                         @input="val => value.OBJEKT_ID = val.OBJEKT_ID"
                         :value="value.objekt"
                         append-icon=""
                         slot="input"
                         :entities="['objekt']"
        >
        </b-entity-select>
    </b-edit-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Haus} from "../../../server/resources";
    import {namespace} from "vuex-class";
    import entitySelect from "../../common/EntitySelect.vue"

    const SnackbarModule = namespace('shared/snackbar');
    const RefreshModule = namespace('shared/refresh');

    @Component({components: {'b-entity-select': entitySelect}})
    export default class HouseAddDialog extends Vue {

        value: Haus = new Haus();

        @Prop()
        large: boolean;

        @Prop()
        type: String;

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

        onSave() {
            this.$emit('input', this.value);
            this.value.create().then(() => {
                this.updateMessage('Haus erstellt.');
                this.requestRefresh();
            }).catch((error) => {
                this.$emit('show', true);
                this.updateMessage('Fehler beim Erstellen des Hauses. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
            });
        }
    }
</script>
