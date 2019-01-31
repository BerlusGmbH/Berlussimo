<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import MessageMutation from "./MessageMutation.graphql";
    import {FetchResult} from "apollo-link";

    @Component
    export default class DisplaysMessages extends Vue {

        showMessage<R = any>(message: string): Promise<FetchResult<R>> {
            return this.$apollo.mutate({
                mutation: MessageMutation,
                variables: {
                    message
                }
            });
        }
    }
</script>
