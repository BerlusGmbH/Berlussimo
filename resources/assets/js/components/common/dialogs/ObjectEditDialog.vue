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
                v-model="inputValue.OBJEKT_KURZNAME"
                label="Name"
                type="text"
                prepend-icon="mdi-alphabetical"
        ></v-text-field>
        <b-entity-select prepend-icon="mdi-account-multiple"
                         @input="val => inputValue.EIGENTUEMER_PARTNER = val.PARTNER_ID"
                         :value="value.eigentuemer"
                         append-icon=""
                         slot="input"
                         label="Eigentümer"
                         :entities="['partner']"
        >
        </b-entity-select>
    </b-edit-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import _ from "lodash";
    import {Objekt} from "../../../server/resources";
    import {namespace} from "vuex-class";
    import entitySelect from "../../common/EntitySelect.vue"

    const SnackbarModule = namespace('shared/snackbar');
    const RefreshModule = namespace('shared/refresh');

    @Component({components: {'b-entity-select': entitySelect}})
    export default class ObjectEditDialog extends Vue {

        @Prop({type: Object})
        value: Objekt;

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

        inputValue: Objekt = new Objekt();

        kinds: Array<string> = [];

        onSave() {
            this.$emit('input', this.inputValue);
            this.inputValue.save().then(() => {
                this.updateMessage('Objekt geändert.');
                this.requestRefresh();
            }).catch((error) => {
                this.updateMessage('Fehler beim Ändern des Objektes. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
            });
        }

        onOpen() {
            this.inputValue = _.cloneDeep(this.value);
        }
    }
</script>
