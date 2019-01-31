<template>
    <v-container grid-list-md fluid :key="key">
    </v-container>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";

    @Component
    export default class DetailView extends Vue {

        get key() {
            return Math.random();
        }
    }
</script>
