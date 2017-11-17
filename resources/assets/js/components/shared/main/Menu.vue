<template>
    <component v-if="menu" :is="menu"></component>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';
    import axios from '../../../libraries/axios';

    @Component
    export default class Menu extends Vue {

        menu: Object | null = null;

        mounted() {
            axios.get('/api/v1/menu').then(response => {
                this.menu = {
                    template: response.data
                };
            });
        }
    }
</script>