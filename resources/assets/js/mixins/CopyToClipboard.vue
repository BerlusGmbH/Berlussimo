<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Mutation, namespace} from "vuex-class";

    const SnackbarMutation = namespace('shared/snackbar', Mutation);

    @Component
    export default class CopyToClipboard extends Vue {

        @SnackbarMutation('updateMessage')
        updateMessage: Function;

        copyToClipboard(value, type: string = 'Information') {
            let text = document.createElement("input");
            document.body.appendChild(text);
            text.value = value;
            text.select();
            document.execCommand('copy');
            document.body.removeChild(text);
            this.updateMessage(type + ' in die Zwischenablage kopiert.');
        }
    }
</script>