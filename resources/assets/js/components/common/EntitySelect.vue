<template>
    <app-select :search-input.sync="query"
                :value="value"
                :disabled="disabled"
                @change="emit('change', $event)"
                @input="emit('input', $event)"
                :loading="searching"
                :items="items" autocomplete
                @click.native.stop
                :multiple="multiple"
                :hide-details="hideDetails"
                return-object
                :no-data-text="status"
                :prepend-icon="icon"
                :append-icon="appendIcon"
                :filter="() => true"
                :solo="solo"
                :label="label"
                :dark="dark"
                :light="light"
                :debounce-search="400"
                :clearable="clearable"
                :tabindex="tabindex"
                :autofocus="autofocus"
    >
        <template slot="selection" slot-scope="data">
            <app-chip @input="data.parent.selectItem(data.item); $emit('chip-close', $event)"
                      :multiple="multiple" :entity="data.item" :selected="data.selected"></app-chip>
        </template>
        <template slot="item" slot-scope="data">
            <template v-if="typeof data.item !== 'object'">
                <v-list-tile-content v-text="data.item"></v-list-tile-content>
            </template>
            <template v-else>
                <app-tile :entity="data.item"></app-tile>
            </template>
        </template>
    </app-select>
</template>
<script lang="ts">
    import Vue from "../../imports";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import {Model} from "../../server/resources";
    import VSelect from "./VSelect.vue"
    import {CancelTokenSource} from "axios";
    import axios from "../../libraries/axios";
    import _ from "lodash";

    @Component({components: {'app-select': VSelect}})
    export default class EntitySelect extends Vue {

        source: CancelTokenSource;

        searching: boolean = false;

        items: Array<any> = [];

        query: string = '';

        @Prop({type: [Object, Array], default: () => []})
        value;

        @Prop({type: Boolean, default: false})
        solo;

        @Prop({type: Boolean, default: false})
        disabled;

        @Prop({type: String, default: ''})
        label;

        @Prop({type: Boolean, default: false})
        hideDetails;

        @Prop({type: Array, default: () => []})
        entities: Array<string>;

        @Prop({type: Boolean, default: false})
        multiple: boolean;

        @Prop({type: String, default: "/api/v1/search"})
        url;

        @Prop({type: String})
        prependIcon;

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

        mounted() {
            this.$refs.input = this.$children[0].$refs.input;
        }

        @Watch('query')
        onQueryChanged(query) {
            if (typeof query === 'string') {
                this.debouncedSearch(query);
            }
        }

        debouncedSearch: Function = _.debounce(function (this: EntitySelect, query) {
            this.goSearch(query);
        }, 300);

        get status(): string {
            if (this.searching) {
                return "Suche..."
            } else {
                if (this.query) {
                    return "Keine Ergebnisse vorhanden."
                }
                return "Bitte Suchbegriff eingeben."
            }
        }

        get icon(): string {
            if (this.prependIcon) {
                return this.prependIcon;
            }
            if (this.multiple) {
                return 'mdi-tag-multiple';
            } else {
                return 'mdi-tag';
            }
        }

        emit(type, entities) {
            if (Array.isArray(entities)) {
                if (!this.multiple && entities.length == 1) {
                    this.$emit(type, entities[entities.length - 1]);
                } else if (this.multiple) {
                    this.$emit(type, entities);
                }
            } else {
                this.$emit(type, entities);
            }
        }

        goSearch(query) {
            if (this.source) {
                this.source.cancel();
            }
            this.source = axios.CancelToken.source();
            this.searching = true;
            let vm = this;
            if (query !== '') {
                axios.get(vm.url, {
                    params: {
                        q: query,
                        e: vm.entities
                    }
                }).then(function (response) {
                    let data = response.data;
                    Object.keys(data).forEach(function (val) {
                        Object.keys(data[val]).forEach(function (objekt) {
                            data[val][objekt] = Model.applyPrototype(data[val][objekt]);
                        });
                    });
                    let total: Array<Object> = [];
                    Object.keys(data).forEach((key) => {
                        total = total.concat(data[key]);
                    });
                    vm.items = total;
                    vm.searching = false;
                }).catch(function () {
                    vm.searching = false;
                });
            } else {
                vm.items = [];
                vm.searching = false;
            }
        }
    }
</script>