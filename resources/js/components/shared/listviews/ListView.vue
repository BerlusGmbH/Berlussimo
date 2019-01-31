<template>
    <v-container fluid grid-list-md>
        <v-layout row wrap>
            <v-flex xs12>
                <v-card>
                    <v-card-text>
                        <v-layout row wrap>
                            <v-flex class="pt-3" lg1 sm2 xs12>
                                <slot></slot>
                            </v-flex>
                            <v-flex lg7 sm5 xs11>
                                <v-text-field label="Suche"
                                              prepend-icon="mdi-magnify"
                                              v-model="parameters.search"
                                ></v-text-field>
                            </v-flex>
                            <v-flex class="pt-3" lg1 sm1 xs1>
                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on }">
                                        <v-btn-toggle v-model="extended">
                                            <v-btn :disabled="variablesDefinitions.length === 0" flat v-on="on">
                                                <v-icon>mdi-magnify-plus</v-icon>
                                            </v-btn>
                                        </v-btn-toggle>
                                    </template>
                                    <span>Erweiterte Suche</span>
                                </v-tooltip>
                            </v-flex>
                            <v-flex lg2 sm2 xs9>
                                <v-select :items="viewsValue"
                                          label="Ansicht"
                                          return-object
                                          v-model="parameters.view"
                                ></v-select>
                            </v-flex>
                            <v-flex class="text-xs-right pt-3" lg1 sm2 xs3>
                                <v-btn @click="editQuery">
                                    <v-icon>mdi-pencil</v-icon>
                                </v-btn>
                                <b-edit-query-dialog :fragments="fragmentsString"
                                                     :query.sync="plainQuery"
                                                     @update:query="onUpdateQuery"
                                                     v-model="edit"
                                ></b-edit-query-dialog>
                            </v-flex>
                        </v-layout>
                    </v-card-text>
                </v-card>
            </v-flex>
            <v-expand-transition>
                <v-flex v-if="extended === 0" xs12>
                    <v-card>
                        <v-card-text>
                            <v-layout row wrap>
                                <v-flex :key="variable.label"
                                        :lg2="!variable.list"
                                        :lg4="variable.list"
                                        :md3="!variable.list"
                                        :md6="variable.list"
                                        :sm4="!variable.list"
                                        :sm8="variable.list"
                                        v-for="variable in variablesDefinitions"
                                        xs12
                                >
                                    <b-variable-input-field
                                        :value="parameters.variables[variable.name]"
                                        :variable="variable"
                                        @input="setVariablesValues(variable.name, $event)"
                                    ></b-variable-input-field>
                                </v-flex>
                            </v-layout>
                        </v-card-text>
                    </v-card>
                </v-flex>
            </v-expand-transition>
            <v-flex xs12>
                <v-card>
                    <b-treegrid :headers="headers"
                                :pagination.sync="parameters.pagination"
                                :result="items"
                                :total-items="parameters.pagination.totalItems"
                    ></b-treegrid>
                </v-card>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import {Model} from "../../../models";
    import EntitySelect from "../../../components/common/EntitySelect";
    import _ from "lodash";
    import TreeGrid from "./TreeGrid";
    import GraphQLDocumentModifier from "./GraphQLDocumentModifier";
    import EditQueryDialog from "./EditQueryDialog.vue";
    import {parse, print} from "graphql";
    import IdentifierFragments from "../../shared/listviews/IdentifierFragments.graphql";
    import PersonIdentifierQuery from "../../shared/listviews/PersonIdentifierQuery.graphql";
    import PartnerIdentifierQuery from "../../shared/listviews/PartnerIdentifierQuery.graphql";
    import UnitIdentifierQuery from "../../shared/listviews/UnitIdentifierQuery.graphql";
    import HouseIdentifierQuery from "../../shared/listviews/HouseIdentifierQuery.graphql";
    import PropertyIdentifierQuery from "../../shared/listviews/PropertyIdentifierQuery.graphql";
    import AccountingEntityIdentifierQuery from "../../shared/listviews/AccountingEntityIdentifierQuery.graphql";
    import ConstructionSiteIdentifierQuery from "../../shared/listviews/ConstructionSiteIdentifierQuery.graphql";
    import DetailIdentifierQuery from "../../shared/listviews/DetailIdentifierQuery.graphql";
    import PurchaseContractIdentifierQuery from "../../shared/listviews/PurchaseContractIdentifierQuery.graphql";
    import RentalContractIdentifierQuery from "../../shared/listviews/RentalContractIdentifierQuery.graphql";
    import VariableInputField from "../../shared/listviews/VariableInputField.vue";
    import EventBus from '../../../EventBus';

    @Component({
        components: {
            'b-treegrid': TreeGrid,
            'b-edit-query-dialog': EditQueryDialog,
            'b-entity-select': EntitySelect,
            'b-variable-input-field': VariableInputField
        },
        apollo: {
            items: {
                query(this: ListView) {
                    return this.enhancedQuery;
                },
                variables(this: ListView) {
                    return Object.assign({}, this.mandatoryVariables, this.optionalVariables);
                },
                update(this: ListView, data) {
                    let dataPropertyName = Object.getOwnPropertyNames(data)[0];
                    this.parameters.pagination.totalItems = data[dataPropertyName].paginatorInfo.total;
                    return data;
                },
                debounce: 300,
                skip(this: ListView) {
                    return !this.enhancedQuery;
                },
                fetchPolicy: 'cache-and-network'
            }
        }
    })
    export default class ListView extends Vue {

        @Prop({default: () => []})
        views: {
            text: string,
            query: any
        }[];

        edit: boolean = false;

        fragments = IdentifierFragments;

        items: any[] = [];
        parameters: {
            view: any | null;
            search: string | null;
            variables: any
            pagination: {
                rowsPerPage: number,
                page: number,
                descending: boolean,
                sortBy: string,
                totalItems: number
            }
        } = {
            view: null,
            search: null,
            variables: {},
            pagination: {
                rowsPerPage: 50,
                page: 1,
                descending: false,
                sortBy: '',
                totalItems: 0
            }
        };
        loading: boolean = false;

        plainQuery: string = '';
        enhancedQuery: any = null;
        query: any = null;
        headers: any[] = [];

        extended = [];

        created() {
            EventBus.$on('list-view:refetch', () => {
                this.onRefetch();
            })
        }

        @Watch('$route', {immediate: true})
        onRouteChange() {
            this.parseQuery();
        }

        @Watch('parameters', {deep: true})
        onParametersChange() {
            this.debouncedUpdateHistory();
        }

        @Watch('query')
        onQueryChange(query) {
            if (!query) {
                return;
            }
            this.headers = GraphQLDocumentModifier.getHeaders(query);
            query = GraphQLDocumentModifier.removeClientOnlyDirectives(query);
            this.enhancedQuery = GraphQLDocumentModifier.addFragments(query, this.fragments)
        }

        @Watch('parameters.view', {immediate: true})
        onViewChange(view) {
            if (!view) {
                return;
            }
            if (view.query) {
                this.query = view.query;
                this.plainQuery = print(view.query);
            } else {
                if (this.plainQuery !== '') {
                    this.query = parse(this.plainQuery);
                }
            }
        }

        @Watch('variablesDefinitions', {immediate: true})
        onVariablesDefinitionsChange(currentDefinitions, previousDefinitions) {
            if (!(currentDefinitions && previousDefinitions)) {
                return;
            }
            let previousNames = previousDefinitions.map(v => v.name);
            for (let currentDefinition of currentDefinitions) {
                if (!(
                    previousNames.includes(currentDefinition.name)
                    && currentDefinition.kind === previousDefinitions.find(v => {
                        return v.name === currentDefinition.name;
                    }).kind
                    && currentDefinition.list === previousDefinitions.find(v => {
                        return v.name === currentDefinition.name;
                    }).list
                )) {
                    delete this.parameters.variables[currentDefinition.name];
                }
            }
            if (currentDefinitions.length === 0) {
                this.extended = [];
            }
        }

        onUpdateQuery(query) {
            this.parameters.view = this.viewsValue.find(e => e.text === "Benutzerdefiniert");
            this.query = parse(query);
        }

        onRefetch() {
            this.$apollo.queries.items.refetch();
        }

        debouncedUpdateHistory: Function = _.debounce(this.updateHistory, 300);

        updateHistory() {
            const query = Object.assign({}, {view: this.parameters.view.text}, this.mandatoryVariables, {variables: this.optionalVariables});
            this.$router.replace({
                name: this.$route.name,
                query
            }).catch(_err => {
            });
        }

        parseQuery() {
            this.parameters.search = this.checkQueryParameter(this.$route.query.search);
            const view = this.viewsValue.find(e => e.text === this.checkQueryParameter(this.$route.query.view));
            if (view) {
                this.parameters.view = view;
            } else {
                this.parameters.view = this.viewsValue[0];
            }
            if (this.$route.query.variables) {
                this.$nextTick(() => {
                    this.parseVariables(this.$route.query.variables);
                });
            }
            this.parameters.pagination.page = this.$route.query.page ? Number(this.$route.query.page) : 1;
            this.parameters.pagination.rowsPerPage = this.$route.query.first ? Number(this.$route.query.first) : 50;
        }

        parseVariables(entities) {
            for (let variable of this.variablesDefinitions) {
                if (
                    entities.hasOwnProperty(variable.name)
                    && this.parameters.variables[variable.name] === undefined
                ) {
                    if (
                        variable.kind === "EntitySelect"
                        && variable.list
                        && Array.isArray(entities[variable.name])
                    ) {
                        this.setVariablesValues(variable.name, []);
                        for (let [index, entity] of entities[variable.name].entries()) {
                            if (entity.id && entity.type) {
                                this.fetchEntity(entity).then(result => {
                                    let dataPropertyName = Object.getOwnPropertyNames(result.data)[0];
                                    const entity = Model.applyPrototype(result.data[dataPropertyName]);
                                    this.$set(this.parameters.variables[variable.name], index, entity);
                                });
                            }
                        }
                    } else if (
                        variable.kind === "EntitySelect"
                        && !variable.list
                        && !Array.isArray(entities[variable.name])
                    ) {
                        const entity = entities[variable.name];
                        if (entity.id && entity.type) {
                            this.fetchEntity(entity).then(result => {
                                let dataPropertyName = Object.getOwnPropertyNames(result.data)[0];
                                const entity = Model.applyPrototype(result.data[dataPropertyName]);
                                this.$set(this.parameters, variable.name, entity);
                            });
                        }
                    } else {
                        this.$set(this.parameters.variables, variable.name, entities[variable.name]);
                    }
                }
            }
        }

        fetchEntity(entity) {
            const query = this.selectIdentifierQuery(entity.type);
            return this.$apollo.query({
                query: query,
                variables: {
                    id: entity.id
                }
            })
        }

        selectIdentifierQuery(type) {
            switch (type) {
                case "Person":
                    return PersonIdentifierQuery;
                case "Partner":
                    return PartnerIdentifierQuery;
                case "Unit":
                    return UnitIdentifierQuery;
                case "House":
                    return HouseIdentifierQuery;
                case "Property":
                    return PropertyIdentifierQuery;
                case "PurchaseContract":
                    return PurchaseContractIdentifierQuery;
                case "RentalContract":
                    return RentalContractIdentifierQuery;
                case "AccountingEntity":
                    return AccountingEntityIdentifierQuery;
                case "ConstructionSite":
                    return ConstructionSiteIdentifierQuery;
                default:
                    return DetailIdentifierQuery;
            }
        }

        prototypeEntity(entity) {
            return Model.applyPrototype(entity.entity);
        }

        editQuery() {
            this.edit = true;
        }

        checkQueryParameter(parameter: (null | string)[] | string): null | string {
            if (parameter && typeof parameter === 'string') {
                return parameter;
            } else {
                return null;
            }
        }

        setVariablesValues(name, value) {
            this.$set(this.parameters.variables, name, value);
        }

        get viewsValue(): {
            text: string,
            query: any
        }[] {
            if (this.views) {
                const viewsCopy = _.cloneDeep(this.views);
                viewsCopy.push({
                    text: "Benutzerdefiniert",
                    query: null
                });
                return viewsCopy;
            }
            return [];
        }

        get fragmentsString() {
            return print(this.fragments);
        }

        get variablesDefinitions() {
            return GraphQLDocumentModifier.getVariables(this.query);
        }

        get mandatoryVariables() {
            let v = {
                first: this.parameters.pagination.rowsPerPage,
                page: this.parameters.pagination.page
            };
            if (this.parameters.search) {
                v['search'] = this.parameters.search;
            }
            return v;
        }

        get optionalVariables() {
            let v = {};
            for (let variable of this.variablesDefinitions) {
                if (
                    this.parameters.variables.hasOwnProperty(variable.name)
                ) {
                    if (variable.kind === "EntitySelect") {
                        if (
                            variable.list
                            && Array.isArray(this.parameters.variables[variable.name])
                        ) {
                            const values: {
                                id: string,
                                type: string
                            }[] = [];
                            for (let entity of this.parameters.variables[variable.name]) {
                                if (entity && entity.id && entity.__typename) {
                                    values.push({
                                        id: entity.id,
                                        type: entity.__typename
                                    })
                                }
                            }
                            v[variable.name] = values;
                        } else if (
                            this.parameters.variables[variable.name].id
                            && this.parameters.variables[variable.name].__typename
                        ) {
                            v[variable.name] = {
                                id: this.parameters.variables[variable.name].id,
                                type: this.parameters.variables[variable.name].__typename
                            }
                        }
                    } else if (
                        this.parameters.variables[variable.name] !== null
                        && this.parameters.variables[variable.name] !== ""
                    ) {
                        v[variable.name] = this.parameters.variables[variable.name];
                    }
                }
            }
            return v;
        }
    }
</script>
