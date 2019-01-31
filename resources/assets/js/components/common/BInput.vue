<script lang="ts">
    import Vue from "../../imports";
    import Component from "vue-class-component"

    @Component({extends: Vue.component('VInput')})
    export default class BInput extends Vue {
    }
</script>
<style>
    .v-input {
        font-size: inherit;
    }
</style>