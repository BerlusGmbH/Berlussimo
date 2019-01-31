<template>
    <app-edit-dialog
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
                v-model="value.OBJEKT_KURZNAME"
                label="Name"
                type="text"
                prepend-icon="mdi-alphabetical"
        ></v-text-field>
        <app-entity-select prepend-icon="mdi-account-multiple"
                           @input="val => value.EIGENTUEMER_PARTNER = val.PARTNER_ID"
                           :value="value.eigentuemer"
                           append-icon=""
                           slot="input"
                           label="Eigentümer"
                           :entities="['partner']"
        >
        </app-entity-select>
    </app-edit-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Objekt} from "../../../server/resources";
    import {namespace} from "vuex-class";
    import entitySelect from "../../common/EntitySelect.vue"

    const Snackbar = namespace('shared/snackbar');
    const Refresh = namespace('shared/refresh');

    @Component({components: {'app-entity-select': entitySelect}})
    export default class ObjectAddDialog extends Vue {

        value: Objekt = new Objekt();

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

        @Snackbar.Mutation('updateMessage')
        updateMessage: Function;

        @Refresh.Mutation('requestRefresh')
        requestRefresh: Function;

        onSave() {
            this.$emit('input', this.value);
            this.value.create().then(() => {
                this.updateMessage('Objekt erstellt.');
                this.requestRefresh();
            }).catch((error) => {
                this.$emit('show', true);
                this.updateMessage('Fehler beim Erstellen des Objektes. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
            });
        }
    }
</script>
