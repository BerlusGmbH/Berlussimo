<template>
    <v-breadcrumbs :items="items" class="pa-0">
        <v-icon slot="divider">chevron_right</v-icon>
        <template slot="item" slot-scope="props">
            <template v-if="props.item.type === 'category'">
                <div style="display: inline-block">
                    <b-input hide-details>
                        <b-icon slot="prepend">mdi-subdirectory-arrow-right</b-icon>
                        <router-link :to="{name: props.item.href}" @click.native.stop>{{props.item.name}}</router-link>
                    </b-input>
                </div>
            </template>
            <template v-if="props.item.type === 'entity' && entity">
                <app-identifier :value="entity"></app-identifier>
            </template>
        </template>
    </v-breadcrumbs>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import PersonQuery from "./PersonQuery.graphql";
    import InvoiceQuery from "./InvoiceQuery.graphql";
    import PropertyQuery from "./PropertyQuery.graphql";
    import HouseQuery from "./HouseQuery.graphql";
    import UnitQuery from "./UnitQuery.graphql";

    @Component({
        apollo: {
            entity: {
                query(this: Breadcrumbs) {
                    return this.query;
                },
                variables() {
                    return {
                        id: this.$route.params.id
                    }
                },
                skip(this: Breadcrumbs) {
                    return !this.query;
                },
                fetchPolicy: 'cache-only'
            }
        }
    })
    export default class Breadcrumbs extends Vue {

        @Prop({type: Array})
        items: any[];

        @Prop({type: String, default: () => ''})
        path: string;

        entity = null;

        get query() {
            let Query = null;
            switch (this.$route.name) {
                case 'web.persons.show':
                    Query = PersonQuery;
                    break;
                case 'web.units.show':
                    Query = UnitQuery;
                    break;
                case 'web.houses.show':
                    Query = HouseQuery;
                    break;
                case 'web.properties.show':
                    Query = PropertyQuery;
                    break;
                case 'web.invoices.show':
                    Query = InvoiceQuery;
                    break;
            }
            return Query;
        }
    }
</script>
