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
                v-model="inputValue.OBJEKT_KURZNAME"
                label="Name"
                type="text"
                prepend-icon="mdi-alphabetical"
        ></v-text-field>
        <app-entity-select prepend-icon="mdi-account-multiple"
                           @input="val => inputValue.EIGENTUEMER_PARTNER = val.PARTNER_ID"
                           :value="value.eigentuemer"
                           append-icon=""
                           slot="input"
                           :entities="['partner']"
        >
        </app-entity-select>
    </app-edit-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import _ from "lodash";
    import {Objekt} from "server/resources/models";
    import {Mutation, namespace} from "vuex-class";
    import entitySelect from "../../common/EntitySelect.vue"

    const SnackbarMutation = namespace('shared/snackbar', Mutation);
    const RefreshMutation = namespace('shared/refresh', Mutation);

    @Component({components: {'app-entity-select': entitySelect}})
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

        @SnackbarMutation('updateMessage')
        updateMessage: Function;

        @RefreshMutation('requestRefresh')
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
