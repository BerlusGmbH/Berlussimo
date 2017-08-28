<template>
    <v-footer class="pa-3 center-xs">
        <div>
            <b>Berlussimo</b> wird von der
            <a target='_new' href='http://www.berlus.de'>Berlus GmbH</a> -
            Hausverwaltung zur Verfügung gestellt.
        </div>
    </v-footer>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';

    @Component
    export default class Footer extends Vue {
    }
</script>

<style>
</style>