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
    import $ from "jquery";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import {Model} from "../../server/resources";
    import VSelect from "./VSelect.vue"
    import {CancelTokenSource} from "axios";
    import axios from "../../libraries/axios";

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

        @Prop({type: [Boolean, String]})
        dark;

        @Prop({type: [Boolean, String]})
        light;

        @Prop({type: [Boolean, String]})
        clearable;

        @Watch('query')
        onQueryChanged(query) {
            if (typeof query === 'string') {
                this.goSearch(query);
            }
        }

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
                    $.each(data, function (key, val) {
                        switch (key) {
                            case 'objekt':
                                $.each(val, function (objekt_key, objekt) {
                                    data[key][objekt_key] = Model.applyPrototype(objekt);
                                });
                                break;
                            case 'haus':
                                $.each(val, function (haus_key, haus) {
                                    data[key][haus_key] = Model.applyPrototype(haus);
                                });
                                break;
                            case 'einheit':
                                $.each(val, function (einheit_key, einheit) {
                                    data[key][einheit_key] = Model.applyPrototype(einheit);
                                });
                                break;
                            case 'person':
                                $.each(val, function (person_key, person) {
                                    data[key][person_key] = Model.applyPrototype(person);
                                });
                                break;
                            case 'partner':
                                $.each(val, function (partner_key, partner) {
                                    data[key][partner_key] = Model.applyPrototype(partner);
                                });
                                break;
                            case 'bankkonto':
                                $.each(val, function (bankaccount_key, account) {
                                    data[key][bankaccount_key] = Model.applyPrototype(account);
                                });
                                break;
                            case 'mietvertrag':
                                $.each(val, function (rental_contract_key, contract) {
                                    data[key][rental_contract_key] = Model.applyPrototype(contract);
                                });
                                break;
                            case 'kaufvertrag':
                                $.each(val, function (purchase_contract_key, contract) {
                                    data[key][purchase_contract_key] = Model.applyPrototype(contract);
                                });
                                break;
                            case 'baustelle':
                                $.each(val, function (construction_site_key, site) {
                                    data[key][construction_site_key] = Model.applyPrototype(site);
                                });
                                break;
                            case 'wirtschaftseinheit':
                                $.each(val, function (accouting_entity, entity) {
                                    data[key][accouting_entity] = Model.applyPrototype(entity);
                                });
                                break;
                            case 'artikel':
                                $.each(val, function (invoice_item_entity, entity) {
                                    data[key][invoice_item_entity] = Model.applyPrototype(entity);
                                });
                                break;
                            case 'kontenrahmen':
                                $.each(val, function (bank_account_standard_chart_entity, entity) {
                                    data[key][bank_account_standard_chart_entity] = Model.applyPrototype(entity);
                                });
                                break;
                            case 'buchungskonto':
                                $.each(val, function (booking_account_entity, entity) {
                                    data[key][booking_account_entity] = Model.applyPrototype(entity);
                                });
                                break;
                        }
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