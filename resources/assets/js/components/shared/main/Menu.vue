<template>
    <component v-if="menu" :is="menu"></component>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';
    import axios from '../../../libraries/axios';
    import {Prop} from "vue-property-decorator";

    @Component
    export default class Menu extends Vue {

        menu: Object | null = null;

        @Prop({type: String, default: ''})
        url: string;

        mounted() {
            if (this.url) {
                axios.get(this.url).then(response => {
                    this.menu = {
                        template: response.data
                    };
                });
            }
        }
    }
</script>