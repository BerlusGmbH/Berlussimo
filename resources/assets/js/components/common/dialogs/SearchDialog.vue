<template>
    <v-dialog :value="value"
              @input="$emit('input', $event)"
              fullscreen
              lazy
              transition="fade"
              :overlay="false"
    >
        <v-card>
            <v-toolbar>
                <v-toolbar-title>Suche</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn icon @click.native="$emit('input', false)">
                    <v-icon>close</v-icon>
                </v-btn>
            </v-toolbar>
            <v-toolbar color="secondary" height="initial">
                <app-searchbar style="margin-top: 3px; margin-bottom: 3px"></app-searchbar>
            </v-toolbar>
            <v-toolbar color="primary" height="initial">
                <app-global-select style="margin-top: 3px; margin-bottom: 3px"></app-global-select>
            </v-toolbar>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import searchbar from "../../shared/Searchbar.vue";
    import globalSelect from "../../shared/GlobalSelect.vue";

    @Component({
        components: {
            'app-searchbar': searchbar,
            'app-global-select': globalSelect
        }
    })
    export default class SearchDialog extends Vue {

        @Prop({type: Boolean})
        value: boolean;
    }
</script>
