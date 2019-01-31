<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {ErrorMessages} from "./DisplaysErrorsContract";

    @Component
    export default class DisplaysErrors extends Vue {

        errorMessages: ErrorMessages = new ErrorMessages();

        clearErrorMessages() {
            this.errorMessages = new ErrorMessages();
        }

        extractErrorMessages(errors) {
            if (errors.graphQLErrors && Array.isArray(errors.graphQLErrors)) {
                for (let e of errors.graphQLErrors) {
                    if (e.extensions.category === 'validation') {
                        for (let name in e.extensions.validation) {
                            if (e.extensions.validation.hasOwnProperty(name)) {
                                this.$set(this.errorMessages, name, e.extensions.validation[name]);
                            }
                        }
                    }
                }
            }
        }
    }
</script>
