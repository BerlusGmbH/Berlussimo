<template>
    <app-select :search-input.sync="query"
                :value="value" :disabled="disabled"
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
    >
        <template slot="selection" scope="data">
            <app-chip @input="data.parent.selectItem(data.item); $emit('chip-close', $event)"
                      :multiple="multiple" :entity="data.item" :selected="data.selected"></app-chip>
        </template>
        <template slot="item" scope="data">
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
    import Vue from "vue";
    import Vuetify from "vuetify";
    import $ from "jquery";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import {Bankkonto, Einheit, Haus, Objekt, Partner, Person} from "../../server/resources/models";
    import VSelect from "./VSelect.vue"

    Vue.use(Vuetify);

    @Component({components: {'app-select': VSelect}})
    export default class EntitySelect extends Vue {

        request: any;

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

        @Watch('query')
        onQueryChanged(query: any) {
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
            if (this.request && this.request.state() === "pending") {
                this.request.abort();
            }
            this.searching = true;
            let vm = this;
            if (query !== '') {
                let token = document.head.querySelector('meta[name="csrf-token"]');
                $.ajaxSetup({
                    beforeSend: (xhr) => {
                        if (token) {
                            xhr.setRequestHeader('X-CSRF-TOKEN', token['content']);
                        }
                    }
                });
                vm.request = $.getJSON(vm.url, {
                    q: query,
                    e: vm.entities
                }).done(function (data) {
                    $.each(data, function (key, val) {
                        switch (key) {
                            case 'objekt':
                                $.each(val, function (objekt_key, objekt) {
                                    data[key][objekt_key] = Object.setPrototypeOf(objekt, Objekt.prototype);
                                });
                                break;
                            case 'haus':
                                $.each(val, function (haus_key, haus) {
                                    data[key][haus_key] = Object.setPrototypeOf(haus, Haus.prototype);
                                });
                                break;
                            case 'einheit':
                                $.each(val, function (einheit_key, einheit) {
                                    data[key][einheit_key] = Object.setPrototypeOf(einheit, Einheit.prototype);
                                });
                                break;
                            case 'person':
                                $.each(val, function (person_key, person) {
                                    data[key][person_key] = Object.setPrototypeOf(person, Person.prototype);
                                });
                                break;
                            case 'partner':
                                $.each(val, function (partner_key, partner) {
                                    data[key][partner_key] = Object.setPrototypeOf(partner, Partner.prototype);
                                });
                                break;
                            case 'bankkonto':
                                $.each(val, function (partner_key, partner) {
                                    data[key][partner_key] = Object.setPrototypeOf(partner, Bankkonto.prototype);
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
                }).fail(function (_jqxhr, textStatus) {
                    if (textStatus !== "abort") {
                        vm.searching = false;
                    }
                });
            } else {
                vm.items = [];
                vm.searching = false;
            }
        }
    }
</script>