<template>
    <v-container grid-list-md fluid>
        <transition name="fade" mode="out-in">
            <v-layout row wrap>
                <v-flex xs12>
                    <v-card>
                        <v-card-text>
                            <v-layout row wrap>
                                <v-flex xs12 sm2>
                                    <v-btn @click="add = true">
                                        <v-icon>add</v-icon>
                                        <v-icon>mdi-clipboard</v-icon>
                                    </v-btn>
                                    <app-assignment-add-dialog v-model="add"></app-assignment-add-dialog>
                                </v-flex>
                                <v-flex xs12 sm5>
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
                                <v-flex xs12 sm3>
                                    <v-select :disabled="typeof parameterList['f'] === 'undefined'"
                                              :items="parameterItems('f')"
                                              v-model="parameters.f"
                                              label="Gruppen"
                                              multiple
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
                                                        <app-identifier style="white-space: nowrap"
                                                                        :value="prototypeEntity(item)">
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
    import axios from "../../../libraries/axios"
    import {Model} from "../../../server/resources/models";
    import _ from "lodash";
    import assignmentAddDialog from "../../../components/common/dialogs/AssignmentAddDialog.vue";

    const RefreshState = namespace('shared/refresh', State);
    const RefreshMutation = namespace('shared/refresh', Mutation);
    const SnackbarMutation = namespace('shared/snackbar', Mutation);

    @Component({
        'components': {
            'app-assignment-add-dialog': assignmentAddDialog
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
            f: Array<string>;
            pagination: {
                rowsPerPage: number,
                page: number,
                descending: boolean,
                sortBy: string
            }
        } = {
            v: null,
            q: null,
            f: [],
            pagination: {
                rowsPerPage: 5,
                page: 1,
                descending: false,
                sortBy: ''
            }
        };
        totalListItems: number = 0;
        loading: boolean = false;
        add: boolean = false;

        created() {
            this.parseQuery();
            axios.get('/api/v1/assignments/parameters').then((respnose) => {
                this.parameterList = respnose.data;
                if (!this.parameters.v) {
                    this.parameters.v = this.parameterList['v']['default'];
                }
            });
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
            this.debouncedUpdateHistory();
            this.debouncedUpdateList();
        }

        @Watch('$route')
        onRouteChange() {
            this.parseQuery();
        }

        updateList() {
            this.loading = true;
            return axios.get('/api/v1/assignments', {
                params: {
                    'q': this.parameters.q,
                    'v': this.parameters.v,
                    'f': this.parameters.f,
                    's': this.parameters.pagination.rowsPerPage,
                    'page': this.parameters.pagination.page
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
                        this.parameters.pagination.page = respnose.data['last-page'];
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

        debouncedUpdateList: Function = _.debounce(this.updateList, 300);
        debouncedUpdateHistory: Function = _.debounce(this.updateHistory, 300);

        updateHistory() {
            let query = {};
            ['q', 'v'].forEach(val => {
                if (this.parameters[val]) {
                    query[val] = this.parameters[val];
                }
            });
            if (this.parameters.f.length) {
                query['f'] = this.parameters.f.join(',');
            }
            query['page'] = String(this.parameters.pagination.page);
            query['s'] = String(this.parameters.pagination.rowsPerPage);

            if (!this.$route.query.v || !this.$route.query.s || !this.$route.query.page) {
                this.$router.replace({
                    name: 'web.assignments.index',
                    query
                });
            } else {
                this.$router.push({
                    name: 'web.assignments.index',
                    query
                });
            }
        }

        parseQuery() {
            this.parameters.q = this.$route.query.q ? this.$route.query.q : null;
            this.parameters.v = this.$route.query.v ? this.$route.query.v : null;
            if (!this.parameters.v && this.parameterList['v']) {
                this.parameters.v = this.parameterList['v']['default'];
            }
            this.parameters.f = this.$route.query.f ? this.$route.query.f.split(',') : [];
            this.parameters.pagination.page = this.$route.query.page ? Number(this.$route.query.page) : 1;
            this.parameters.pagination.rowsPerPage = this.$route.query.s ? Number(this.$route.query.s) : 5;
        }

        parameterItems(parameter) {
            return this.parameterList[parameter] ? Object.keys(this.parameterList[parameter]['views']) : [];
        }

        prototypeEntity(entity) {
            return Model.applyPrototype(entity.entity);
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