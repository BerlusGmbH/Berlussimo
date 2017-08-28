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
    import {Mutation, namespace, State} from "vuex-class";

    const SnackbarState = namespace('shared/snackbar', State);
    const SnackbarMutation = namespace('shared/snackbar', Mutation);

    @Component
    export default class Snackbar extends Vue {
        @SnackbarState('message')
        message: string;

        @SnackbarState('show')
        show: boolean;

        @SnackbarMutation('updateShow')
        updateShow: Function;

        @SnackbarMutation('toggleShow')
        toggleShow: Function;

        @Prop({type: String})
        id: string;
    }
</script>