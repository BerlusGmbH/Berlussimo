<template>
    <b-input hide-details>
        <b-icon :tooltips="value.getEntityIconTooltips()" class="identifier-icon" slot="prepend">
            {{value.getEntityIcon()}}
        </b-icon>
        <a :href="value.getDetailUrl()"
           style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 200px"
        >
            <v-tooltip bottom>
                <template v-slot:activator="{ on }">
                    <span v-on="on">{{String(value).trim()}}</span>
                </template>
                <span>{{String(value).trim()}}</span>
            </v-tooltip>
        </a>

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
