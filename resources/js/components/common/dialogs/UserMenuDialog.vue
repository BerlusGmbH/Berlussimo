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
                        <b-person-card :value="user"></b-person-card>
                    </v-flex>
                    <v-flex xs12>
                        <b-user-menu-list></b-user-menu-list>
                    </v-flex>
                </v-layout>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import Searchbar from "../../shared/Searchbar.vue";
    import PersonCard from "../../shared/cards/PersonCard.vue";
    import UserMenuList from "../../shared/UserMenuList.vue";
    import {Model, Person} from "../../../models";
    import UserQuery from "../../auth/UserQuery.graphql";

    @Component({
        components: {
            'b-searchbar': Searchbar,
            'b-person-card': PersonCard,
            'b-user-menu-list': UserMenuList
        },
        apollo: {
            user: {
                query: UserQuery,
                variables(this: UserMenuDialog) {
                    return {
                        id: this.userId
                    }
                },
                update(data) {
                    if (data.state && data.state.user) {
                        return Model.applyPrototype(data.state.user);
                    }
                    return null;
                }
            }
        }
    })
    export default class UserMenuDialog extends Vue {

        @Prop({type: Boolean})
        value: boolean;

        @Prop({type: String})
        userId: string;

        user: Person;
    }
</script>
