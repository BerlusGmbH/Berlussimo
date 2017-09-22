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
        <app-select v-model="inputValue.DETAIL_NAME"
                    :items="categories"
                    prepend-icon="mdi-label"
                    label="Kategorie"
                    slot="input"
                    menu-z-index="10"
                    item-text="DETAIL_KAT_NAME"
                    item-value="DETAIL_KAT_NAME"
                    autocomplete
        ></app-select>
        <app-select v-if="selectedCategory.subcategories && selectedCategory.subcategories.length > 0"
                    v-model="inputValue.DETAIL_INHALT"
                    :items="selectedCategory.subcategories"
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
                      :type="type"
                      :label="inputValue.DETAIL_NAME"
                      prepend-icon="mdi-alphabetical"
                      multi-line
        ></v-text-field>
        <v-text-field
                slot="input"
                v-model="inputValue.DETAIL_BEMERKUNG"
                :type="type"
                label="Bemerkung"
                prepend-icon="note"
                multi-line
        ></v-text-field>
    </app-edit-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Detail, Person} from "../../../server/resources/models";
    import axios from "../../../libraries/axios";

    @Component
    export default class DetailAddDialog extends Vue {

        @Prop({type: Object})
        parent: Person;

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

        inputValue: Detail = new Detail();

        categories = [];

        onSave() {
            this.$emit('input', this.inputValue);
        }

        onOpen() {
            if (this.parent) {
                this.inputValue = new Detail();
                this.inputValue.DETAIL_ZUORDNUNG_TABELLE = this.parent.getMorphName();
                this.inputValue.DETAIL_ZUORDNUNG_ID = String(this.parent.getID());
            }
            this.loadCategories();
        }

        loadCategories() {
            axios.get('/api/v1/persons/details/categories').then((response) => {
                this.categories = response.data;
            })
        }

        get selectedCategory() {
            let category = this.categories.find(val => val['DETAIL_KAT_NAME'] === this.inputValue.DETAIL_NAME);
            return category ? category : {};
        }
    }
</script>
