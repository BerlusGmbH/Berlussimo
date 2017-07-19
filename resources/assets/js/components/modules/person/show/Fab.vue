<template>
    <v-speed-dial
            v-model="fab"
            bottom
            right
            fixed
            direction="top"
            hover
            transition="slide-y-reverse-transition"
            class="mb-5 mr-2"
    >
        <v-btn
                slot="activator"
                class="primary"
                dark
                fab
                v-model="fab"
        >
            <v-icon>mdi-account-edit</v-icon>
            <v-icon>close</v-icon>
        </v-btn>
        <v-btn
                fab
                dark
                small
        >
            <v-icon>mdi-pencil</v-icon>
        </v-btn>
        <v-btn
                fab
                dark
                small
        >
            <v-icon>mdi-table</v-icon>
        </v-btn>
        <v-btn
                fab
                dark
                small
        >
            <v-icon>mdi-worker</v-icon>
        </v-btn>
        <v-btn
                fab
                dark
                small
        >
            <v-icon>mdi-lock</v-icon>
        </v-btn>
        <v-btn
                fab
                dark
                small
        >
            <v-icon>mdi-call-merge</v-icon>
        </v-btn>
    </v-speed-dial>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";


    @Component
    export default class Fab extends Vue {
        @Prop()
        fab: any;
    }
</script>