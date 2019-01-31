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

    @Component
    export default class DeatilViewBreadcrumbs extends Vue {

        @Prop({type: Array})
        items: any[];

        @Prop({type: String, default: () => ''})
        path: string;

        get entity() {
            let p: string[] = this.path.split('.');
            let value: any = this.$store.state.modules;
            while (p.length > 0) {
                value = value[p.pop() as string];
            }
            return value;
        }
    }
</script>