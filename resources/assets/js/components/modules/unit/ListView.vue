<template>
    <v-container grid-list-md fluid>
        <transition name="fade" mode="out-in">
            <v-layout row wrap>
                <v-flex xs12>
                    <v-card>
                        <v-card-text>
                            <v-layout row wrap>
                                <v-flex xs12 sm2>
                                    <v-btn @click.native.stop="addUnit">
                                        <v-icon ref="identifier">add</v-icon>
                                        <v-icon>mdi-cube</v-icon>
                                    </v-btn>
                                    <app-unit-add-dialog :position-absolutely="true"
                                                         :show="add"
                                                         @show="val => {add = val}"
                                                         :position-x="x"
                                                         :position-y="y"
                                    ></app-unit-add-dialog>
                                </v-flex>
                                <v-flex xs12 sm8>
                                    <v-text-field prepend-icon="mdi-filter-variant"
                                                  label="Filter"
                                                  v-model="parameters.q"
                                    ></v-text-field>
                                </v-flex>
                                <v-flex xs12 sm2>
                                    <v-select :disabled="typeof parameterList['v'] === 'undefined'"
                                              :items="parameterItems('v')"
                                              v-model="parameters.v"
                                              label="Ansicht"
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
                                      :pagination.sync="parameters.pagination"
                                      :total-items="totalListItems"
                                      :loading="loading"
                        >
                            <template slot="headers" slot-scope="props">
                                <tr>
                                    <th v-for="header in props.headers">
                                        {{ header.text }}
                                        <template v-if="header.data['fields'] && header.data['fields'].length > 0">
                                            <br>({{header.data['fields'].join(', ')}})
                                        </template>
                                        <template v-if="header.data['columns'] && header.data['columns'].length > 0">
                                            <br>[{{header.data['columns'].join(', ')}}]
                                        </template>
                                    </th>
                                </tr>
                            </template>
                            <template slot="items" slot-scope="props">
                                <tr>
                                    <td v-for="cell in props.item">
                                        <template v-for="(cellPart, c) in cell">
                                            <template v-if="cellPart.content.length > 0">
                                                <template v-if="cell.length > 1">
                                                    {{cellPart.relation}}<br>
                                                </template>
                                                <template v-for="item in cellPart.content">
                                                    <template v-if="item.type === 'entity' && item.entity">
                                                        <app-identifier :value="prototypeEntity(item)">
                                                        </app-identifier>
                                                        <br>
                                                    </template>
                                                    <template v-else-if="item.type === 'prerendered'">
                                                        <template v-for="line in item.lines">
                                                            <template v-if="line === ''">
                                                                &nbsp;<br>
                                                            </template>
                                                            <template v-else>
                                                                {{line}}<br>
                                                            </template>
                                                        </template>
                                                    </template>
                                                    <template v-else-if="item.type === 'aggregate'">
                                                        Count({{item.entities.length}})
                                                    </template>
                                                </template>
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
    import {Model} from "server/resources/models";
    import _ from "lodash";
    import unitAddDialog from "../../../components/common/dialogs/UnitAddDialog.vue";

    const RefreshState = namespace('shared/refresh', State);
    const RefreshMutation = namespace('shared/refresh', Mutation);
    const SnackbarMutation = namespace('shared/snackbar', Mutation);

    @Component({
        'components': {
            'app-unit-add-dialog': unitAddDialog
        }
    })
    export default class ListView extends Vue {
        @RefreshState('dirty')
        dirty;

        @RefreshMutation('refreshFinished')
        refreshFinished: Function;

        @SnackbarMutation('updateMessage')
        updateMessage: Function;

        parameterList: Array<Object> = [];
        listItems: Array<any> = [];
        headers: Array<Object> = [];
        parameters: {
            v: string | null;
            q: string | null;
            pagination: {
                rowsPerPage: number,
                page: number,
                descending: boolean,
                sortBy: string
            }
        } = {
            v: null,
            q: null,
            pagination: {
                rowsPerPage: 5,
                page: 1,
                descending: false,
                sortBy: ''
            }
        };
        totalListItems: number = 0;
        loading: boolean = false;
        pauseHistory: boolean = false;
        booting: boolean = false;

        add: boolean = false;
        x: number = 0;
        y: number = 0;

        created() {
            this.pauseHistory = true;
            this.booting = true;
            window.addEventListener('popstate', this.popState);
            this.parseQuery();
            axios.get('/api/v1/units/parameters').then((respnose) => {
                this.parameterList = respnose.data;
                if (!this.parameters.v) {
                    this.parameters.v = this.parameterList['v']['default'];
                }
                this.$nextTick(() => {
                    this.pauseHistory = false;
                    this.booting = false;
                });
            });
        }

        destroyed() {
            window.removeEventListener('popstate', this.popState);
        }

        @Watch('dirty')
        onDirtyChange(val) {
            if (val) {
                this.updateList().then(() => {
                    this.refreshFinished();
                });
            }
        }

        @Watch('parameters', {deep: true})
        onParametersChange() {
            this.onParametersChangeDebounced();
        }

        onParametersChangeDebounced: Function = _.debounce(function (this: ListView) {
            this.updateHistory();
            this.updateList();
        }, 300);

        updateList() {
            this.loading = true;
            return axios.get('/api/v1/units', {
                params: {
                    'q': this.parameters.q,
                    'v': this.parameters.v,
                    's': this.parameters.pagination.rowsPerPage,
                    'page': this.parameters.pagination.page,
                }
            }).then((respnose) => {
                this.loading = false;
                if (respnose.data.headers) {
                    this.headers = respnose.data.headers.map((val) => {
                        return {
                            text: val['head'],
                            sortable: false,
                            value: '',
                            data: val
                        }
                    })
                }
                if (respnose.data.items) {
                    this.listItems = respnose.data.items
                }
                if (respnose.data.total) {
                    this.totalListItems = respnose.data.total
                }
                if (respnose.data['last-page']) {
                    if (respnose.data['last-page'] < this.parameters.pagination.page) {
                        this.booting = true;
                        this.pauseHistory = true;
                        this.parameters.pagination.page = respnose.data['last-page'];
                        this.$nextTick(() => {
                            this.booting = false;
                            this.pauseHistory = false;
                        });
                    }
                }
            }).catch((error) => {
                if (error.response.data.error.message) {
                    this.updateMessage('Message: ' + error.response.data.error.message);
                } else {
                    this.updateMessage('Code: ' + error.response.status + ' Message: ' + error.response.statusText);
                }
                this.loading = false;
            });
        }

        updateHistory() {
            let params = new URLSearchParams(window.location.search);
            params.delete('q');
            if (this.parameters.q) {
                params.set('q', this.parameters.q);
            }
            params.delete('v');
            if (this.parameters.v) {
                params.set('v', this.parameters.v);
            }
            params.delete('s');
            if (this.parameters.pagination.rowsPerPage) {
                params.set('s', String(this.parameters.pagination.rowsPerPage));
            }
            params.delete('page');
            if (this.parameters.pagination.page) {
                params.set('page', String(this.parameters.pagination.page));
            }
            if (this.booting) {
                window.history.replaceState({}, document.title, '?' + params.toString());
            }
            if (!this.pauseHistory) {
                window.history.pushState({}, document.title, '?' + params.toString());
            }

        }

        parseQuery() {
            let params = new URLSearchParams(window.location.search);
            if (params.has('q')) {
                this.parameters.q = params.get('q');
            }
            if (params.has('v')) {
                this.parameters.v = params.get('v');
            }
            if (params.has('page')) {
                this.parameters.pagination.page = Number(params.get('page'));
            }
            if (params.has('s')) {
                this.parameters.pagination.rowsPerPage = Number(params.get('s'));
            }
        }

        popState() {
            this.pauseHistory = true;
            this.parseQuery();
            this.$nextTick(() => this.pauseHistory = false)
        }

        parameterItems(parameter) {
            return this.parameterList[parameter] ? Object.keys(this.parameterList[parameter]['views']) : [];
        }

        prototypeEntity(entity) {
            return Model.applyPrototype(entity.entity);
        }

        addUnit() {
            this.add = true;
            this.x = this.$refs.identifier ? (this.$refs.identifier as HTMLElement).getBoundingClientRect().left - 20 : this.x;
            this.y = this.$refs.identifier ? (this.$refs.identifier as HTMLElement).getBoundingClientRect().top - 20 : this.y;
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