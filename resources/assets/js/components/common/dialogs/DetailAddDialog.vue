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
        <v-autocomplete v-model="inputValue.DETAIL_NAME"
                        :items="categories"
                        prepend-icon="mdi-label"
                        label="Kategorie"
                        slot="input"
                        item-text="DETAIL_KAT_NAME"
                        item-value="DETAIL_KAT_NAME"
        ></v-autocomplete>
        <v-select v-if="selectedCategory.subcategories && selectedCategory.subcategories.length > 0"
                  v-model="inputValue.DETAIL_INHALT"
                  :items="selectedCategory.subcategories"
                  prepend-icon="mdi-alphabetical"
                  :label="inputValue.DETAIL_NAME"
                  slot="input"
                  item-text="UNTERKATEGORIE_NAME"
                  item-value="UNTERKATEGORIE_NAME"
        ></v-select>
        <v-textarea v-else
                    slot="input"
                    v-model="inputValue.DETAIL_INHALT"
                    :type="type"
                    :label="inputValue.DETAIL_NAME"
                    prepend-icon="mdi-alphabetical"
        ></v-textarea>
        <v-textarea
                slot="input"
                v-model="inputValue.DETAIL_BEMERKUNG"
                :type="type"
                label="Bemerkung"
                prepend-icon="note"
        ></v-textarea>
    </b-edit-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import axios from "../../../libraries/axios";
    import {namespace} from "vuex-class";
    import {Detail, Einheit, Haus, Objekt, Person, PurchaseContract, RentalContract} from "../../../server/resources";

    const SnackbarModule = namespace('shared/snackbar');
    const RefreshModule = namespace('shared/refresh');

    @Component
    export default class DetailAddDialog extends Vue {

        @Prop({type: Object})
        parent: Person | Objekt | Haus | Einheit | RentalContract | PurchaseContract;

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
                if (Array.isArray(response.data)) {
                    this.categories = response.data as never[];
                } else {
                    this.categories = [];
                }
            })
        }

        get selectedCategory() {
            let category = this.categories.find(val => val['DETAIL_KAT_NAME'] === this.inputValue.DETAIL_NAME);
            return category ? category : {};
        }
    }
</script>
