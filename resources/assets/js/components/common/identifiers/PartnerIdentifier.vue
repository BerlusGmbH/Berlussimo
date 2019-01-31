<template>
    <b-input hide-details>
        <b-icon :tooltips="value.getEntityIconTooltips()" class="identifier-icon" slot="prepend">
            {{value.getEntityIcon()}}
        </b-icon>
        <a :href="value.getDetailUrl()"
           style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{String(this.value).trim()}}</a>
    </b-input>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";

    @Component
    export default class PartnerIdentifier extends Vue {
        @Prop()
        value;
    }
</script>