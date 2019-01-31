<template>
    <component :is="menu" v-if="menu"></component>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';
    import {Prop} from "vue-property-decorator";
    import MenuQuery from './MenuQuery.graphql'

    @Component({
        apollo: {
            menu: {
                query: MenuQuery,
                variables(this: Menu) {
                    return {
                        module: this.module
                    }
                },
                update(data) {
                    if (data.menu) {
                        return {
                            template: data.menu
                        }
                    }
                    return null;
                }
            }
        }
    })
    export default class Menu extends Vue {

        menu: Object | null = null;

        @Prop({type: String, default: 'MAIN'})
        module: string;
    }
</script>
