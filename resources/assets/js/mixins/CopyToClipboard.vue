<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {namespace} from "vuex-class";

    const Snackbar = namespace('shared/snackbar');

    @Component
    export default class CopyToClipboard extends Vue {

        @Snackbar.Mutation('updateMessage')
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