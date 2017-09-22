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
        <app-select v-if="subcategories.length > 0"
                    v-model="inputValue.DETAIL_INHALT"
                    :items="subcategories"
                    prepend-icon="mdi-alphabetical"
                    :label="inputValue.DETAIL_NAME"
                    slot="input"
                    menu-z-index="10"
                    item-text="UNTERKATEGORIE_NAME"
                    item-value="UNTERKATEGORIE_NAME"
        ></app-select>
        <v-text-field v-else
                      slot="input"
                      v-model="inputValue.DETAIL_INHALT"
                      single-line
                      :type="type"
                      :prepend-icon="prependIcon"
                      :multi-line="large"
        ></v-text-field>
        <v-text-field slot="input"
                      v-model="inputValue.DETAIL_BEMERKUNG"
                      single-line
                      :type="type"
                      prepend-icon="note"
                      :multi-line="large"
        ></v-text-field>
    </app-edit-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import _ from "lodash";
    import {Detail} from "../../../server/resources/models";
    import axios from "../../../libraries/axios";

    @Component
    export default class DetailEditDialog extends Vue {

        @Prop({type: Object})
        value: Detail;

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
            axios.get('/api/v1/persons/details/categories/' + this.value.DETAIL_NAME + '/subcategories').then((response) => {
                this.subcategories = response.data;
            })
        }
    }
</script>
