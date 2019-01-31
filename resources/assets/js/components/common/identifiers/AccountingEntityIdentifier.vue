<template>
    <b-input hide-details>
        <b-icon :tooltips="value.getEntityIconTooltips()" class="identifier-icon" slot="prepend">
            {{value.getEntityIcon()}}
        </b-icon>
        {{text}}
    </b-input>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";

    @Component
    export default class AccountingEntityIdentifier extends Vue {
        @Prop()
        value;

        get text() {
            let str: string = String(this.value).trim();
            return str.length > 20 ? str.substr(0, 20) + '...' : str;
        }
    }
</script>