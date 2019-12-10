<template>
    <v-icon style="font-size: inherit" :color="color" v-if="!tooltips.length && !delayedTooltips">
        <slot></slot>
    </v-icon>
    <v-tooltip v-model="show" bottom v-else>
        <v-icon style="font-size: inherit" :color="color" slot="activator">
            <slot></slot>
        </v-icon>
        <span v-if="tooltipValues.length"><template v-for="(tooltipValue, index) in tooltipValues">{{tooltipValue}}<hr
                v-if="index + 1 != tooltipValues.length"></template></span>
        <span v-else><v-progress-circular indeterminate color="primary"></v-progress-circular></span>
    </v-tooltip>
</template>
<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import axios from "../../libraries/axios";

    @Component
    export default class BIcon extends Vue {
        @Prop({default: () => [], type: Array})
        tooltips: Array<string>;

        @Prop({default: '', type: String})
        delayedTooltips: string;

        @Prop({default: '', type: String})
        color: string;

        show: boolean = false;
        tooltipValues: Array<string> = [];

        @Watch('show')
        onShowChange(v) {
            if (v && this.delayedTooltips) {
                axios.get(this.delayedTooltips).then(response => {
                    this.tooltipValues = response.data;
                });
            } else if (v && this.tooltips) {
                this.tooltipValues = this.tooltips;
            }
        }
    }
</script>