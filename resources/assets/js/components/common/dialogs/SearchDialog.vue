<template>
    <v-dialog :value="value"
              @input="$emit('input', $event)"
              fullscreen
              lazy
              transition="dialog-bottom-transition"
              :overlay="false"
    >
        <v-card>
            <v-toolbar>
                <app-searchbar class="pt-0"></app-searchbar>
                <v-btn icon @click.native="$emit('input', false)">
                    <v-icon>close</v-icon>
                </v-btn>
            </v-toolbar>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import searchbar from "../../shared/Searchbar.vue";

    @Component({
        components: {
            'app-searchbar': searchbar
        }
    })
    export default class SearchDialog extends Vue {

        @Prop({type: Boolean})
        value: boolean;
    }
</script>
