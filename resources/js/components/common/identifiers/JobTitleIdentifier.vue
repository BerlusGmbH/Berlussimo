<template>
    <b-input hide-details>
        <b-icon :tooltips="value.getEntityIconTooltips()" slot="prepend"
                class="identifier-icon">
            {{value.getEntityIcon()}}
        </b-icon>
        {{String(value)}}
    </b-input>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";

    @Component
    export default class PurchaseContractIdentifier extends Vue {
        @Prop()
        value;
    }
</script>