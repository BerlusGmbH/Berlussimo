<template>
    <div class="identifier">
        <b-identifier :value="value.title"></b-identifier>
        bei
        <b-identifier :value="value.employer"></b-identifier>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";

    @Component
    export default class JobIdentifier extends Vue {
        @Prop()
        value;
    }
</script>