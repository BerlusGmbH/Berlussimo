<template>
    <div class="identifier">
        <v-icon>mdi-bank</v-icon>
        {{String(value)}}
    </div>
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