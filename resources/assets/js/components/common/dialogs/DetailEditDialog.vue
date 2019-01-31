<template>
    <app-edit-dialog
            lazy
            :large="large"
            :positionAbsolutley="positionAbsolutley"
            :positionX="positionX"
            :positionY="positionY"
            :show="show"
            @show="$emit('show', $event)"
            @open="onOpen"
            @save="onSave"
    >
        <slot></slot>
        <v-select v-if="subcategories.length > 0"
                  v-model="inputValue.DETAIL_INHALT"
                  :items="subcategories"
                  prepend-icon="mdi-alphabetical"
                  :label="inputValue.DETAIL_NAME"
                  slot="input"
                  item-text="UNTERKATEGORIE_NAME"
                  item-value="UNTERKATEGORIE_NAME"
                  lazy
        ></v-select>
        <v-textarea v-else
                    slot="input"
                    v-model="inputValue.DETAIL_INHALT"
                    :label="inputValue.DETAIL_NAME"
                    :type="type"
                    :prepend-icon="prependIcon"
        ></v-textarea>
        <v-textarea slot="input"
                      v-model="inputValue.DETAIL_BEMERKUNG"
                      label="Bemerkung"
                      :type="type"
                      prepend-icon="note"
        ></v-textarea>
    </app-edit-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import _ from "lodash";
    import {Detail, Einheit, Person} from "../../../server/resources";
    import axios from "../../../libraries/axios";

    @Component
    export default class DetailEditDialog extends Vue {

        @Prop({type: Object})
        value: Detail;

        @Prop()
        parent: Person | Einheit;

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

        @Prop({type: String})
        prependIcon;

        inputValue: Detail = new Detail();
        subcategories: Array<any> = [];

        onSave() {
            this.$emit('input', this.inputValue);
        }

        onOpen() {
            this.inputValue = _.cloneDeep(this.value);
            this.loadCategories();
        }

        loadCategories() {
            if (this.parent) {
                axios.get(this.parent.getApiBaseUrl() + '/details/categories/' + this.value.DETAIL_NAME + '/subcategories').then((response) => {
                    this.subcategories = response.data;
                })
            }
        }
    }
</script>
