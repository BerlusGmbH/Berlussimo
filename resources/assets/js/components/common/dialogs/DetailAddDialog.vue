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
        <v-select v-model="inputValue.DETAIL_NAME"
                  :items="categories"
                  prepend-icon="mdi-label"
                  label="Kategorie"
                  slot="input"
                  item-text="DETAIL_KAT_NAME"
                  item-value="DETAIL_KAT_NAME"
                  autocomplete
        ></v-select>
        <v-select v-if="selectedCategory.subcategories && selectedCategory.subcategories.length > 0"
                  v-model="inputValue.DETAIL_INHALT"
                  :items="selectedCategory.subcategories"
                  prepend-icon="mdi-alphabetical"
                  :label="inputValue.DETAIL_NAME"
                  slot="input"
                  item-text="UNTERKATEGORIE_NAME"
                  item-value="UNTERKATEGORIE_NAME"
        ></v-select>
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
    import {Detail, Einheit, Person} from "../../../server/resources/models";
    import axios from "../../../libraries/axios";
    import {Mutation, namespace} from "vuex-class";

    const SnackbarMutation = namespace('shared/snackbar', Mutation);
    const RefreshMutation = namespace('shared/refresh', Mutation);

    @Component
    export default class DetailAddDialog extends Vue {

        @Prop({type: Object})
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

        @SnackbarMutation('updateMessage')
        updateMessage: Function;

        @RefreshMutation('requestRefresh')
        requestRefresh: Function;

        inputValue: Detail = new Detail();

        categories = [];

        onSave() {
            this.$emit('input', this.inputValue);
            this.inputValue.create().then(() => {
                this.updateMessage('Detail hinzugefügt.');
                this.requestRefresh();
            }).catch((error) => {
                this.updateMessage('Fehler beim Hinzufügen des Details. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
            });
        }

        onOpen() {
            if (this.parent) {
                this.inputValue = new Detail();
                this.inputValue.DETAIL_ZUORDNUNG_TABELLE = this.parent.getMorphName();
                this.inputValue.DETAIL_ZUORDNUNG_ID = String(this.parent.getID());
                this.loadCategories(this.parent.getApiBaseUrl());
            }
        }

        loadCategories(base) {
            axios.get(base + '/details/categories').then((response) => {
                this.categories = response.data;
            })
        }

        get selectedCategory() {
            let category = this.categories.find(val => val['DETAIL_KAT_NAME'] === this.inputValue.DETAIL_NAME);
            return category ? category : {};
        }
    }
</script>
