<template>
    <span>
        <i class="mdi mdi-subdirectory-arrow-right" style="margin-right: -2px"></i>
        <v-breadcrumbs style="display: inline-flex" class="pl-0">
            <v-icon slot="divider">chevron_right</v-icon>
            <v-breadcrumbs-item>
                <router-link @click.native.stop :to="{name: 'web.persons.index'}">Personen</router-link>
            </v-breadcrumbs-item>
            <v-breadcrumbs-item v-if="person">
                <app-identifier :value="person"></app-identifier>
            </v-breadcrumbs-item>
        </v-breadcrumbs>
    </span>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {namespace, State} from "vuex-class";
    import {Person} from "../../../server/resources/models";

    const ShowState = namespace('modules/person/show', State);

    @Component
    export default class DeatilViewBreadcrumbs extends Vue {
        @ShowState('person')
        person: Person;
    }
</script>