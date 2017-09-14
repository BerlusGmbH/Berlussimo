<template></template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Mutation, namespace} from "vuex-class";

    const MessagesMutation = namespace('shared/messages', Mutation);

    @Component
    export default class MessagesLoader extends Vue {

        @MessagesMutation('updateMessages')
        updateMessages: Function;

        @Prop({type: Array})
        successMessages: Array<any>;

        @Prop({type: Array})
        infoMessages: Array<any>;

        @Prop({type: Array})
        warningMessages: Array<any>;

        @Prop({type: Array})
        errorMessages: Array<any>;


        created() {
            if (this.successMessages) {
                this.updateMessages({type: 'success', messages: this.successMessages});
            }
            if (this.infoMessages) {
                this.updateMessages({type: 'info', messages: this.infoMessages});
            }
            if (this.warningMessages) {
                this.updateMessages({type: 'warning', messages: this.warningMessages});
            }
            if (this.errorMessages) {
                this.updateMessages({type: 'error', messages: this.errorMessages});
            }
        }
    }
</script>