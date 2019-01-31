<template>
    <app-edit-dialog
            :loading="loading"
            :show="show"
            @close="onClose($event)"
            @open="onOpen"
            @save="onSave"
            @show="$emit('show', $event)"
            large
            lazy
    >
        <slot></slot>
        <v-autocomplete :disabled="loading"
                        :error-messages="errorMessages['input.category']"
                        :items="categories"
                        item-text="name"
                        item-value="name"
                        label="Kategorie"
                        prepend-icon="mdi-label"
                        slot="input"
                        v-model="inputValue.category"
        ></v-autocomplete>
        <v-select :disabled="loading"
                  :error-messages="errorMessages['input.name']"
                  :items="selectedCategory.subcategories"
                  :label="inputValue.category"
                  item-text="name"
                  item-value="name"
                  prepend-icon="mdi-alphabetical"
                  slot="input"
                  v-if="selectedCategory.subcategories && selectedCategory.subcategories.length > 0"
                  v-model="inputValue.value"
        ></v-select>
        <v-textarea :disabled="loading"
                    :error-messages="errorMessages['input.value']"
                    :label="inputValue.category"
                    :type="type"
                    prepend-icon="mdi-alphabetical"
                    slot="input"
                    v-else
                    v-model="inputValue.value"
        ></v-textarea>
        <v-textarea
                :disabled="loading"
                :error-messages="errorMessages['input.comment']"
                :type="type"
                label="Bemerkung"
                prepend-icon="note"
                slot="input"
                v-model="inputValue.comment"
        ></v-textarea>
    </app-edit-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Detail, House, Person, Property, PurchaseContract, RentalContract, Unit} from "../../../models";
    import DetailCategoriesQuery from "./DetailCategoriesQuery.graphql";
    import CreateDetailMutation from "./CreateDetailMutation.graphql";
    import DisplaysMessages from "../../../mixins/DisplaysMessages.vue";
    import DisplaysErrors from "../../../mixins/DisplaysErrors.vue";
    import {ErrorMessages} from "../../../mixins";

    @Component({
        mixins: [DisplaysMessages, DisplaysErrors]
    })
    export default class DetailAddDialog extends Vue {

        @Prop({type: Object})
        parent: Person | Property | House | Unit | RentalContract | PurchaseContract;

        @Prop()
        large: boolean;

        @Prop()
        type: String;

        @Prop({type: Boolean})
        show;

        inputValue: Detail = new Detail();

        categories = [];

        loading: boolean = false;

        errorMessages: ErrorMessages;
        extractErrorMessages: Function;
        clearErrorMessages: Function;

        showMessage: Function;

        onSave() {
            this.$emit('input', this.inputValue);
            this.clearErrorMessages();
            this.loading = true;
            this.$apollo.mutate({
                mutation: CreateDetailMutation,
                variables: {
                    input: {
                        detailableType: this.parent.__typename,
                        detailableId: this.parent.id,
                        category: this.inputValue.category,
                        value: this.inputValue.value,
                        comment: this.inputValue.comment
                    }
                }
            }).then(() => {
                this.loading = false;
                this.showMessage('Detail hinzugefügt.');
                this.$emit('close');
            }).catch(error => {
                this.extractErrorMessages(error);
                this.loading = false;
                this.showMessage('Fehler beim Hinzufügen des Details. Nachricht: ' + error.message);
            });
        }

        onOpen() {
            if (this.parent) {
                this.inputValue = new Detail();
                this.loading = true;
                this.$apollo.query({
                    query: DetailCategoriesQuery,
                    variables: {
                        detailableType: this.parent.__typename
                    },
                    fetchPolicy: 'network-only'
                } as any).then(result => {
                    this.categories = result.data.detailCategories;
                    this.loading = false;
                }).catch(() => {
                    this.loading = false;
                })
            }
        }

        onClose() {
            this.$emit('close');
            this.clearErrorMessages();
        }

        get selectedCategory() {
            let category = this.categories.find(val => val['name'] === this.inputValue.category);
            return category ? category : {};
        }
    }
</script>
