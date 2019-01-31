<template>
    <b-input>
        <b-icon :tooltips="value.getEntityIconTooltips()" slot="prepend">mdi-bank</b-icon>
        {{String(value)}}
    </b-input>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";

    @Component
    export default class BankAccountIdentifier extends Vue {
        @Prop()
        value;
    }
</script>