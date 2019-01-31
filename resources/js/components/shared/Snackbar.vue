<template>
    <v-snackbar :timeout="5000" @input="clearMessage($event)" multi-line v-model="show">
        {{message}}
        <v-btn @click="this.show = false" flat>Close</v-btn>
    </v-snackbar>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import MessageQuery from "../../mixins/MessageQuery.graphql";
    import DisplaysMessages from "../../mixins/DisplaysMessages.vue";

    @Component({
        mixins: [DisplaysMessages],
        apollo: {
            message: {
                query: MessageQuery,
                update(this: Snackbar, data) {
                    this.show = !!data.state.message;
                    return data.state.message;
                }
            }
        }
    })
    export default class Snackbar extends Vue {
        message: string = '';
        show: boolean = false;
        showMessage: Function;

        clearMessage(v) {
            if (!v) {
                this.showMessage('');
            }
        }
    }
</script>