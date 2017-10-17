<template>
    <div class="identifier">
        <v-icon class="identifier-icon">{{value.getEntityIcon()}}</v-icon>
        <a :href="value.getDetailUrl()">{{String(value)}}</a>
    </div>
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