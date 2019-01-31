<template></template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import StateQuery from "./StateQuery.graphql";
    import UserQuery from "../auth/UserQuery.graphql";
    import {Model, Person} from "../../models";

    @Component({
        apollo: {
            state: {
                query: StateQuery,
                fetchPolicy: 'network-only',
                skip(this: StateLoader) {
                    return !!this.user;
                }
            },
            user: {
                query: UserQuery,
                update(data) {
                    if (data.state && data.state.user) {
                        return Model.applyPrototype(data.state.user);
                    }
                    return null;
                }
            }
        }
    })
    export default class StateLoader extends Vue {
        user: Person | null = null;
    }
</script>
