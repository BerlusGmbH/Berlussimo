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
                v-model="inputValue.EINHEIT_KURZNAME"
                label="Name"
                type="text"
                prepend-icon="mdi-alphabetical"
        ></v-text-field>
        <v-text-field
                slot="input"
                v-model="inputValue.EINHEIT_QM"
                label="Fläche"
                type="number"
                step="0.01"
                prepend-icon="mdi-numeric"
                suffix="m²"
        ></v-text-field>
        <v-text-field
                slot="input"
                v-model="inputValue.EINHEIT_LAGE"
                label="Lage"
                type="text"
                prepend-icon="mdi-alphabetical"
        ></v-text-field>
        <v-select v-model="inputValue.TYP"
                  :items="kinds"
                  :prepend-icon="inputValue.getKindIcon()"
                  label="Art"
                  slot="input"
        ></v-select>
        <app-entity-select prepend-icon="mdi-domain"
                           @input="val => inputValue.HAUS_ID = val.HAUS_ID"
                           :value="value.haus"
                           append-icon=""
                           slot="input"
                           :entities="['haus']"
        >
        </app-entity-select>
    </app-edit-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import _ from "lodash";
    import {Einheit} from "server/resources/models";
    import {Mutation, namespace} from "vuex-class";
    import axios from "libraries/axios";
    import entitySelect from "../../common/EntitySelect.vue"

    const SnackbarMutation = namespace('shared/snackbar', Mutation);
    const RefreshMutation = namespace('shared/refresh', Mutation);

    @Component({components: {'app-entity-select': entitySelect}})
    export default class UnitEditDialog extends Vue {

        @Prop({type: Object})
        value: Einheit;

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

        @SnackbarMutation('updateMessage')
        updateMessage: Function;

        @RefreshMutation('requestRefresh')
        requestRefresh: Function;

        inputValue: Einheit = new Einheit();

        kinds: Array<string> = [];

        onSave() {
            this.$emit('input', this.inputValue);
            this.inputValue.save().then(() => {
                this.updateMessage('Einheit geändert.');
                this.requestRefresh();
            }).catch((error) => {
                this.updateMessage('Fehler beim Ändern der Einheit. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
            });
        }

        onOpen() {
            this.inputValue = _.cloneDeep(this.value);
            this.loadPossibleUnitKinds();
        }

        loadPossibleUnitKinds() {
            axios.get(this.value.getApiBaseUrl() + '/possible_unit_kinds').then((response) => {
                this.kinds = response.data;
            });
        }
    }
</script>
