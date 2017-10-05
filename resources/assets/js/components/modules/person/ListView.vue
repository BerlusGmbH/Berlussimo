<template>
    <v-container grid-list-md fluid>
        <transition name="fade" mode="out-in">
            <v-layout row wrap>
                <v-flex xs12>
                    <v-card>
                        <v-card-text>
                            <v-layout row wrap>
                                <v-flex xs3 sm2>
                                    <v-btn>
                                        <v-icon>add</v-icon>
                                        <v-icon>mdi-account</v-icon>
                                    </v-btn>
                                </v-flex>
                                <v-flex xs9 sm8>
                                    <v-text-field prepend-icon="mdi-filter-variant"
                                                  label="Filter"
                                                  v-model="q"
                                    ></v-text-field>
                                </v-flex>
                                <v-flex xs12 sm2>
                                    <v-select :disabled="typeof parameters['v'] === 'undefined'"
                                              :items="parameterItems('v')"
                                              v-model="v"
                                    ></v-select>
                                </v-flex>
                            </v-layout>
                        </v-card-text>
                    </v-card>
                </v-flex>
                <v-flex xs12>
                    <v-card>
                        <v-data-table :items="listItems"
                                      :headers="headers"
                                      :pagination.sync="pagination"
                                      :total-items="totalListItems"
                                      :loading="loading"
                        >
                            <template slot="items" scope="props">
                                <tr>
                                    <td v-for="(v, k) in headers">
                                        <template v-for="(item, i) in props.item[k]">
                                            <template v-if="item.type === 'entity'">
                                                <template v-if="i > 0">
                                                    <br>
                                                </template>
                                                <app-identifier :value="prototypeEntity(item)">
                                                </app-identifier>
                                            </template>
                                            <template v-else-if="item.type === 'prerendered'">
                                                <template v-for="(line, l) in item.lines">
                                                    <template v-if="l > 0">
                                                        <br>
                                                    </template>
                                                    {{line}}
                                                </template>
                                            </template>
                                            <template v-else-if="item.type === 'aggregate'">
                                                Count({{item.entities.length}})
                                            </template>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </v-data-table>
                    </v-card>
                </v-flex>
            </v-layout>
        </transition>
    </v-container>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Mutation, namespace, State} from "vuex-class";
    import {Watch} from "vue-property-decorator";
    import axios from "libraries/axios"
    import {
        Detail,
        Einheit,
        Haus,
        Job,
        Objekt,
        Partner,
        Person,
        RentalContract
    } from "../../../server/resources/models";

    const RefreshState = namespace('shared/refresh', State);
    const RefreshMutation = namespace('shared/refresh', Mutation);

    @Component
    export default class ListView extends Vue {
        @RefreshState('dirty')
        dirty;

        @RefreshMutation('refreshFinished')
        refreshFinished: Function;

        parameters: Array<Object> = [];
        listItems: Array<any> = [];
        headers: Array<Object> = [];
        v: string | null = null;
        q: string | null = null;
        pagination: Object = {
            rowsPerPage: 5,
            page: 1,
            descending: false,
            sortBy: ''
        };
        totalListItems: number = 0;
        loading: boolean = false;

        created() {
            this.parseQuery();
            axios.get('/api/v1/persons/parameters').then((respnose) => {
                this.parameters = respnose.data;
                if (!this.v) {
                    this.v = this.parameters['v']['default'];
                }
            });
        }

        @Watch('dirty')
        onDirtyChange(val) {
            if (val) {
                this.updateList();
            }
        }

        @Watch('q')
        onQChange() {
            this.updateList();
            this.updateHistory();
        }

        @Watch('v')
        onVChange() {
            this.updateList();
            this.updateHistory();
        }

        @Watch('pagination', {deep: true})
        onPaginationChange() {
            this.updateList();
            this.updateHistory();
        }

        updateList() {
            this.loading = true;
            axios.get('/api/v1/persons', {
                params: {
                    'q': this.q,
                    'v': this.v,
                    's': this.pagination['rowsPerPage'],
                    'page': this.pagination['page']
                }
            }).then((respnose) => {
                this.loading = false;
                if (respnose.data.headers) {
                    this.headers = respnose.data.headers.map((val) => {
                        return {
                            text: val,
                            sortable: false,
                            value: ''
                        }
                    })
                }
                if (respnose.data.items) {
                    this.listItems = respnose.data.items
                }
                if (respnose.data.total) {
                    this.totalListItems = respnose.data.total
                }
            })
        }

        updateHistory() {
            let params = new URLSearchParams(window.location.search);
            params.delete('q');
            if (this.q) {
                params.set('q', this.q);
            }
            params.delete('v');
            if (this.v) {
                params.set('v', this.v);
            }
            params.delete('s');
            if (this.pagination['rowsPerPage']) {
                params.set('s', this.pagination['rowsPerPage']);
            }
            params.delete('page');
            if (this.pagination['page']) {
                params.set('page', this.pagination['page']);
            }
            window.history.pushState({}, document.title, '?' + params.toString());
        }

        parseQuery() {
            let params = new URLSearchParams(window.location.search);
            if (params.has('q')) {
                this.q = params.get('q');
            }
            if (params.has('v')) {
                this.v = params.get('v');
            }
            if (params.has('page')) {
                this.pagination['page'] = Number(params.get('page'));
            }
            if (params.has('s')) {
                this.pagination['rowsPerPage'] = Number(params.get('s'));
            }
        }

        parameterItems(parameter) {
            return this.parameters[parameter] ? Object.keys(this.parameters[parameter]['views']) : [];
        }

        prototypeEntity(entity) {
            switch (entity.class) {
                case "App\\Models\\Person":
                    return Person.prototypePerson(entity.entity);
                case "App\\Models\\Partner":
                    return Partner.prototypePartner(entity.entity);
                case "App\\Models\\Job":
                    return Job.prototypeJob(entity.entity);
                case "App\\Models\\Mietvertraege":
                    return RentalContract.prototypeRentalContract(entity.entity);
                case "App\\Models\\Kaufvertraege":
                    return RentalContract.prototypeRentalContract(entity.entity);
                case "App\\Models\\Objekte":
                    return Objekt.prototypeObjekt(entity.entity);
                case "App\\Models\\Haeuser":
                    return Haus.prototypeHaus(entity.entity);
                case "App\\Models\\Einheiten":
                    return Einheit.prototypeEinheit(entity.entity);
                case "App\\Models\\Details":
                    return Detail.prototypeDetail(entity.entity);
            }
            return null;
        }
    }
</script>

<style>
    .fade-enter-active {
        transition: all .3s ease;
    }

    .fade-leave-active {
        transition: all .3s ease;
    }

    .fade-enter, .fade-leave-to {
        opacity: 0;
    }
</style>