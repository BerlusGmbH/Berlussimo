<template>
    <v-snackbar multi-line :timeout="5000" :value="show" @input="updateShow">
        {{message}}
        <v-btn flat @click="toggleShow">Close</v-btn>
    </v-snackbar>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {namespace} from "vuex-class";

    const SnackbarState = namespace('shared/snackbar');

    @Component
    export default class Snackbar extends Vue {
        @SnackbarState.State('message')
        message: string;

        @SnackbarState.State('show')
        show: boolean;

        @SnackbarState.Mutation('updateShow')
        updateShow: Function;

        @SnackbarState.Mutation('toggleShow')
        toggleShow: Function;

        @Prop({type: String})
        id: string;
    }
</script>