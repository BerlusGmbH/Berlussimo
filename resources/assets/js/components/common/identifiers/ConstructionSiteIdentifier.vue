<template>
    <div class="identifier">
        <v-icon class="identifier-icon">{{value.getEntityIcon()}}</v-icon>
        {{text}}
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";

    @Component
    export default class ConstructionSiteIdentifier extends Vue {
        @Prop()
        value;

        get text() {
            let str: string = String(this.value).trim();
            return str.length > 20 ? str.substr(0, 20) + '...' : str;
        }
    }
</script>