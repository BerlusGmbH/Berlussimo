<template>
    <v-dialog :value="value"
              @input="$emit('input', $event)"
              fullscreen
              lazy
              transition="fade"
              :overlay="false"
    >
        <v-card style="background: #303030">
            <v-toolbar>
                <v-toolbar-title>Benutzermenü</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn icon @click.native="$emit('input', false)">
                    <v-icon>close</v-icon>
                </v-btn>
            </v-toolbar>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <app-person-card :value="user"></app-person-card>
                    </v-flex>
                    <v-flex xs12>
                        <app-user-menu-list></app-user-menu-list>
                    </v-flex>
                </v-layout>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import searchbar from "../../shared/Searchbar.vue";
    import personCard from "../../shared/cards/PersonCard.vue";
    import userMenuList from "../../shared/UserMenuList.vue";
    import axios from "libraries/axios"
    import {Person} from "server/resources/models";

    @Component({
        components: {
            'app-searchbar': searchbar,
            'app-person-card': personCard,
            'app-user-menu-list': userMenuList
        }
    })
    export default class UserMenuDialog extends Vue {

        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: Number})
        userId: number;

        user: Person = new Person();

        @Watch('value')
        onValueChange(val) {
            if (val && this.userId) {
                axios.get('/api/v1/persons/' + this.userId).then((result) => {
                    this.user = Person.applyPrototype(result.data);
                });
            }
        }
    }
</script>
