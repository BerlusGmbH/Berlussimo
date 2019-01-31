<template></template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";

    @Component
    export default class MessagesLoader extends Vue {
        @Prop({type: Array})
        successMessages: Array<any>;

        @Prop({type: Array})
        infoMessages: Array<any>;

        @Prop({type: Array})
        warningMessages: Array<any>;

        @Prop({type: Array})
        errorMessages: Array<any>;


        created() {
            this.$apolloProvider.defaultClient.cache.writeData({
                data: {
                    state: {
                        __typename: "State",
                        messages: {
                            __typename: "DisplayMessages",
                            info: this.infoMessages ? this.infoMessages : [],
                            success: this.successMessages ? this.successMessages : [],
                            warning: this.warningMessages ? this.warningMessages : [],
                            error: this.errorMessages ? this.errorMessages : [],
                        }
                    }
                }
            });
        }
    }
</script>
