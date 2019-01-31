<template>
    <app-edit-dialog
            :large="large"
            :loading="loading"
            :show="show"
            @close="$emit('update:show', false)"
            @open="onOpen"
            @save="onSave"
            lazy
    >
        <slot></slot>
        <v-select :disabled="loading"
                  :error-messages="errorMessages.for('input.value')"
                  :items="subcategories"
                  :label="inputValue.category"
                  :menu-props="['lazy']"
                  item-text="name"
                  item-value="name"
                  prepend-icon="mdi-alphabetical"
                  slot="input"
                  v-if="subcategories.length > 0"
                  v-model="inputValue.value"
        ></v-select>
        <v-textarea :disabled="loading"
                    :error-messages="errorMessages.for('input.value')"
                    :label="inputValue.category"
                    :prepend-icon="prependIcon"
                    :type="type"
                    slot="input"
                    v-else-if="large"
                    v-model="inputValue.value"
        ></v-textarea>
        <v-text-field :disabled="loading"
                      :error-messages="errorMessages.for('input.value')"
                      :label="inputValue.category"
                      :prepend-icon="prependIcon"
                      :type="type"
                      slot="input"
                      v-else
                      v-model="inputValue.value"
        ></v-text-field>
        <v-textarea :disabled="loading"
                    :error-messages="errorMessages.for('input.comment')"
                    :type="type"
                    label="Bemerkung"
                    prepend-icon="note"
                    slot="input"
                    v-if="large"
                    v-model="inputValue.comment"
        ></v-textarea>
        <v-text-field :disabled="loading"
                      :error-messages="errorMessages.for('input.comment')"
                      :type="type"
                      label="Bemerkung"
                      prepend-icon="note"
                      slot="input"
                      v-else
                      v-model="inputValue.comment"
        ></v-text-field>
    </app-edit-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Detail, Model} from "../../../models";
    import DetailSubcategoriesQuery from "./DetailSubcategoriesQuery.graphql";
    import DetailQuery from "./DetailQuery.graphql";
    import {ErrorMessages} from "../../../mixins";

    @Component
    export default class DetailEditDialog extends Vue {

        @Prop({type: Object})
        value: Detail;

        @Prop({type: Boolean, default: false})
        large: boolean;

        @Prop({type: String, default: "text"})
        type: string;

        @Prop({type: Boolean})
        show;

        @Prop({type: String})
        prependIcon;

        @Prop({type: Boolean, default: false})
        loading: boolean;

        @Prop({type: Object, default: () => new ErrorMessages()})
        errorMessages: ErrorMessages;

        inputValue: Detail = new Detail();
        subcategories: any[] = [];

        onSave() {
            this.$emit('input', this.inputValue);
        }

        onOpen() {
            this.$emit('update:loading', true);
            Promise.all([
                this.$apollo.query({
                    query: DetailSubcategoriesQuery,
                    variables: {
                        detailableType: this.value.detailableType,
                        category: this.value.category
                    },
                    fetchPolicy: 'network-only'
                } as any).then(result => {
                    this.subcategories = result.data.detailSubcategories;
                }),
                this.$apollo.query({
                    query: DetailQuery,
                    variables: {
                        id: this.value.id
                    },
                    fetchPolicy: 'network-only'
                } as any).then(result => {
                    this.inputValue = Model.applyPrototype(result.data.detail);
                })
            ]).then(() => {
                this.$emit('update:loading', false);
            }).catch(() => {
                this.$emit('update:loading', false);
            });
        }
    }
</script>
