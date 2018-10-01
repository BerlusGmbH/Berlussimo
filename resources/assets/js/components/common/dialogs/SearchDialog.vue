<template>
    <v-dialog :value="value"
              @input="$emit('input', $event)"
              fullscreen
              lazy
              transition="fade"
              :overlay="false"
    >
        <v-card style="background-color: rgb(48,48,48)">
            <v-toolbar>
                <v-toolbar-title>Suche</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn icon @click.native="$emit('input', false)">
                    <v-icon>close</v-icon>
                </v-btn>
            </v-toolbar>
            <v-toolbar color="secondary" height="initial">
                <b-searchbar style="margin-top: 3px; margin-bottom: 3px"></b-searchbar>
            </v-toolbar>
            <v-toolbar color="primary" height="initial">
                <b-global-select style="margin-top: 3px; margin-bottom: 3px"></b-global-select>
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
            'b-searchbar': searchbar,
            'b-global-select': globalSelect
        }
    })
    export default class SearchDialog extends Vue {

        @Prop({type: Boolean})
        value: boolean;
    }
</script>

<style>
    .fade-enter-active {
        transition: all .3s ease;
    }

    .fade-leave-active {
        transition: all .3s ease;
    }

    .fade-enter, .fade-leave-to {
        opacity: 0;
    }
</style>
