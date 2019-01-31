<template>
    <v-autocomplete :append-icon="appendIcon"
                    :autofocus="autofocus"
                    :clearable="clearable"
                    :dark="dark"
                    :debounce-search="400"
                    :disabled="disabled"
                    :error-messages="errorMessages"
                    :filter="() => true"
                    :flat="flat"
                    :hide-details="hideDetails"
                    :items="items"
                    :label="label"
                    :light="light"
                    :loading="showLoading && searching"
                    :multiple="multiple"
                    :no-data-text="status"
                    :prepend-icon="icon"
                    :prepend-inner-icon="innerIcon"
                    :search-input.sync="searchInput"
                    :solo="solo"
                    :solo-inverted="soloInverted"
                    :tabindex="tabindex"
                    :value="valueInput"
                    @change="emit('change', $event)"
                    @click.native.stop
                    @input="emit('input', $event)"
                    return-object
    >
        <template slot="selection" slot-scope="data">
            <app-chip :entity="data.item"
                      :multiple="multiple" :selected="data.selected"
                      @input="data.parent.selectItem(data.item); $emit('chip-close', $event)"></app-chip>
        </template>
        <template slot="item" slot-scope="data">
            <template v-if="typeof data.item !== 'object'">
                <v-list-tile-content v-text="data.item"></v-list-tile-content>
            </template>
            <template v-else>
                <app-tile :entity="data.item"></app-tile>
            </template>
        </template>
    </v-autocomplete>
</template>
<script lang="ts">
    import Vue from "../../imports";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import _ from "lodash";
    import SearchQuery from "./SearchQuery.graphql";

    @Component({
        apollo: {
            result: {
                query: SearchQuery,
                variables(this: EntitySelect) {
                    return {
                        query: this.searchInput,
                        entities: this.entities,
                        unitForRent: this.unitForRent,
                        bookingAccountIn: this.bookingAccountIn,
                        invoiceItemsFrom: this.invoiceItemsFrom,
                        advancePayment: this.advancePayment
                    }
                },
                fetchPolicy: 'cache-and-network',
                debounce: 300
            }
        }
    })
    export default class EntitySelect extends Vue {

        result: any[] = [];

        searchInput: string = '';

        @Prop({type: [Object, Array], default: () => []})
        value;

        @Prop({type: Boolean, default: false})
        solo;

        @Prop({type: Boolean, default: false})
        showLoading;

        @Prop({type: Boolean, default: false})
        soloInverted;

        @Prop({type: Boolean, default: false})
        flat;

        @Prop({type: Boolean, default: false})
        disabled;

        @Prop({type: String, default: ''})
        label;

        @Prop({type: Boolean, default: false})
        hideDetails;

        @Prop({type: Array, default: () => []})
        entities: any[];

        @Prop({type: Boolean, default: false})
        multiple: boolean;

        @Prop({type: Boolean})
        unitForRent: boolean;

        @Prop({type: String})
        bookingAccountIn: string;

        @Prop({type: String})
        invoiceItemsFrom: string;

        @Prop({type: Object})
        advancePayment: any;

        @Prop({type: String})
        prependIcon;

        @Prop({type: String})
        prependInnerIcon;

        @Prop({type: String})
        appendIcon;

        @Prop({type: [Number, String], default: 0})
        tabindex;

        @Prop({type: [Boolean, String]})
        dark;

        @Prop({type: [Boolean, String]})
        light;

        @Prop({type: [Boolean, String]})
        clearable;

        @Prop({type: Boolean, default: false})
        autofocus;

        @Prop({type: [String, Array], default: () => []})
        errorMessages;

        valueInput: any[] | any = [];

        @Watch('value', {immediate: true})
        onValueChange(v) {
            if (v) {
                this.valueInput = _.cloneDeep(v);
            } else {
                this.valueInput = [];
            }
        }

        mounted() {
            this.$refs.input = this.$children[0].$refs.input;
        }

        get status(): string {
            if (this.searching) {
                return "Suche..."
            } else {
                if (this.searchInput) {
                    return "Keine Ergebnisse vorhanden."
                }
                return "Bitte Suchbegriff eingeben."
            }
        }

        get icon(): string {
            if (this.prependIcon) {
                return this.prependIcon;
            }
            return '';
        }

        get innerIcon(): string {
            if (this.prependIcon) {
                return '';
            }
            if (this.prependInnerIcon) {
                return this.prependInnerIcon;
            }
            if (this.multiple) {
                return 'mdi-tag-multiple';
            } else {
                return 'mdi-tag';
            }
        }

        get searching(): boolean {
            return Boolean(this.$apollo.loading);
        }

        emit(type, entities) {
            if (Array.isArray(entities)) {
                if (!this.multiple && entities.length == 1) {
                    this.$emit(type, entities[entities.length - 1]);
                    this.valueInput = entities[entities.length - 1];
                } else if (this.multiple) {
                    this.$emit(type, entities);
                    this.valueInput = entities;
                }
            } else {
                this.$emit(type, entities);
                this.valueInput = entities;
            }
        }

        get items(): any[] {
            if (this.value && this.valueInput) {
                let value = Array.isArray(this.valueInput) ? this.valueInput : [this.valueInput];
                return _.union(this.result, value);
            }
            return this.result;
        }
    }
</script>
