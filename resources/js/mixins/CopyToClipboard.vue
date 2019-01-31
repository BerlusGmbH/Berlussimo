<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import DisplaysMessages from "./DisplaysMessages.vue";

    @Component({
        mixins: [DisplaysMessages]
    })
    export default class CopyToClipboard extends Vue {

        showMessage: Function;

        copyToClipboard(value, type: string = 'Information') {
            let text = document.createElement("input");
            document.body.appendChild(text);
            text.value = value;
            text.select();
            document.execCommand('copy');
            document.body.removeChild(text);
            this.showMessage(type + ' in die Zwischenablage kopiert.');
        }
    }
</script>