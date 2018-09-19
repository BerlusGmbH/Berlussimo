<template>
    <app-edit-dialog
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
                v-model="inputValue.HAUS_STRASSE"
                label="Straße"
                type="text"
                prepend-icon="mdi-alphabetical"
        ></v-text-field>
        <v-text-field
                slot="input"
                v-model="inputValue.HAUS_NUMMER"
                label="Nummer"
                type="text"
                prepend-icon="mdi-alphabetical"
        ></v-text-field>
        <v-text-field
                slot="input"
                v-model="inputValue.HAUS_PLZ"
                label="Postleitzahl"
                type="number"
                prepend-icon="mdi-numeric"
        ></v-text-field>
        <v-text-field
                slot="input"
                v-model="inputValue.HAUS_STADT"
                label="Stadt"
                type="text"
                prepend-icon="mdi-alphabetical"
        ></v-text-field>
        <app-entity-select prepend-icon="mdi-city"
                           @input="val => inputValue.OBJEKT_ID = val.OBJEKT_ID"
                           :value="value.objekt"
                           append-icon=""
                           slot="input"
                           :entities="['objekt']"
        >
        </app-entity-select>
    </app-edit-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import _ from "lodash";
    import {Haus} from "../../../server/resources";
    import {namespace} from "vuex-class";
    import entitySelect from "../../common/EntitySelect.vue"

    const SnackbarModule = namespace('shared/snackbar');
    const RefreshModule = namespace('shared/refresh');

    @Component({components: {'app-entity-select': entitySelect}})
    export default class HouseEditDialog extends Vue {

        @Prop({type: Object})
        value: Haus;

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

        inputValue: Haus = new Haus();

        kinds: Array<string> = [];

        onSave() {
            this.$emit('input', this.inputValue);
            this.inputValue.save().then(() => {
                this.updateMessage('Haus geändert.');
                this.requestRefresh();
            }).catch((error) => {
                this.updateMessage('Fehler beim Ändern des Hauses. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
            });
        }

        onOpen() {
            this.inputValue = _.cloneDeep(this.value);
        }
    }
</script>
