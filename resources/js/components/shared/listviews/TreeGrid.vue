<template>
    <v-data-table :headers="headers"
                  :hide-actions="hideActions"
                  :hide-headers="hideHeaders"
                  :items="itemsValue"
                  :pagination="pagination"
                  :total-items="totalItems"
                  :rows-per-page-items="rowsPerPageItems"
                  @update:pagination="$emit('update:pagination', $event)"
    >
        <template v-slot:items="props">
            <tr>
                <td v-for="column in headers">
                    <template v-if="printValue(column, props)">
                        {{props.item[column.value]}}
                    </template>
                    <template v-if="printIdentifier(column, props)">
                        <app-identifier :value="applyPrototype(props.item[column.value])"
                        ></app-identifier>
                    </template>
                </td>
            </tr>
        </template>
        <template v-slot:pageText="props">
            {{ props.pageStart }} - {{ props.pageStop }} von {{ props.itemsLength }}
        </template>
    </v-data-table>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Model} from "../../../models";

    @Component({
        name: 'b-treegrid'
    })
    export default class TreeGrid extends Vue {

        @Prop({type: [Array, Object], default: () => []})
        result;

        @Prop({type: Boolean, default: false})
        hideHeaders;

        @Prop({type: Boolean, default: false})
        hideActions;

        @Prop({
            type: Object, default: () => {
            }
        })
        pagination: any;

        @Prop({type: [String, Number], default: null})
        totalItems: number;

        @Prop({type: Array, default: []})
        headers: any;

        @Prop({type: Array, default: () => [10, 25, 50, 100]})
        rowsPerPageItems;

        root: string = 'root';

        get itemsValue() {
            let items: any = [];
            let meta = this.queryMetaData;
            for (let i = 0; i < meta.__rows; i++) {
                items.push({});
            }
            this.fillItems(items, this.resultList, meta, this.root);
            return items;
        }

        get queryMetaData() {
            let meta = this.extractMetaData(this.resultList, this.root);
            this.pushGlobalAboveCount(meta, 0);
            return meta;
        }

        get resultList() {
            if (this.result.length === 0) {
                return [];
            }
            let dataPropertyName = Object.getOwnPropertyNames(this.result)[0];
            this.root = dataPropertyName;
            return this.result[dataPropertyName].data;
        }

        meta: any = {};

        extractMetaData(nodes: any[], path): any {
            let results: any = [];
            let rows = 0;
            let above = 0;
            for (const node of nodes) {
                let result: any = {};
                let max = 1;
                for (const field in node) {
                    let fieldValue: any = null;
                    if (typeof node[field] === 'object' && node[field] && !Array.isArray(node[field])) {
                        fieldValue = [node[field]];
                    } else {
                        fieldValue = node[field];
                    }
                    if (Array.isArray(fieldValue) && fieldValue.length > 0 && this.isInHeaders(path + '.' + field)) {
                        result[field] = this.extractMetaData(fieldValue, path + '.' + field);
                        let localMax = result[field].__rows;
                        max = localMax > max ? localMax : max;
                    } else if (typeof node[field] === 'object' && node[field] !== null) {
                        result[field] = null;
                    }
                }
                result['__above'] = above;
                above += max;
                result['__max'] = max;
                rows += max;
                results.push(result);
            }
            return {
                __rows: rows,
                __children: results
            }
        }

        pushGlobalAboveCount(meta: any, above: number) {
            for (const child of meta.__children) {
                child.__above += above;
                for (const relationship in child) {
                    if (relationship !== '__max' && relationship !== '__above') {
                        if (child[relationship]) {
                            this.pushGlobalAboveCount(child[relationship], child.__above);
                        }
                    }
                }
            }
        }

        fillItems(items, nodes, meta, path) {
            for (let i = 0; i < nodes.length; i++) {
                if (typeof nodes[i] === 'object'
                    && nodes[i] !== null
                    && !Array.isArray(nodes[i])
                    && Array.isArray(meta.__children)
                    && meta.__children[i]
                ) {
                    items[meta.__children[i].__above][path] = nodes[i];
                }
                for (let field of Object.keys(nodes[i])) {
                    if (nodes[i][field] !== null && typeof nodes[i][field] !== "undefined" && !Array.isArray(nodes[i][field])) {
                        items[meta.__children[i].__above][path + '.' + field] = nodes[i][field];
                    }
                    if (
                        Array.isArray(nodes[i][field])
                        && Array.isArray(meta.__children)
                        && meta.__children[i]
                        && meta.__children[i][field]
                    ) {
                        this.fillItems(items, nodes[i][field], meta.__children[i][field], path + '.' + field);
                    }
                    if (typeof nodes[i][field] === 'object'
                        && nodes[i][field] !== null
                        && !Array.isArray(nodes[i][field])
                        && Array.isArray(meta.__children)
                        && meta.__children[i]
                        && meta.__children[i][field]
                    ) {
                        this.fillItems(items, [nodes[i][field]], meta.__children[i][field], path + '.' + field);
                    }
                }
            }
        }

        isInHeaders(path) {
            for (let header of this.headers) {
                if (header.value.startsWith(path)) {
                    return true;
                }
            }
            return false;
        }

        printValue(column, props) {
            return column.type === 'ScalarType' && props.item[column.value] !== null && typeof props.item[column.value] !== "undefined";
        }

        printIdentifier(column, props) {
            return column.type === 'ObjectType' && props.item[column.value] !== null && typeof props.item[column.value] !== "undefined";
        }

        applyPrototype(item) {
            return Model.applyPrototype(item);
        }
    }
</script>
