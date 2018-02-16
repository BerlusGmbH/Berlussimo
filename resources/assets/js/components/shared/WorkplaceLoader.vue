<template></template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Watch} from "vue-property-decorator";
    import {Getter, Mutation, namespace, State} from "vuex-class";
    import {Person} from "../../server/resources/models";
    import axios from "../../libraries/axios";

    const WorkplaceMutation = namespace('shared/workplace', Mutation);

    const AuthGetter = namespace('auth', Getter);
    const AuthState = namespace('auth', State);

    @Component
    export default class WorkplaceLoader extends Vue {

        @WorkplaceMutation('updateHasPhone')
        updateHasPhone: Function;

        @AuthGetter('check')
        check: Function;

        @AuthState('user')
        user: Person | null;

        @Watch('check')
        onUserChange(check) {
            if (check) {
                axios.get('/api/v1/workplace').then(response => {
                    this.updateHasPhone(response.data.has_phone);
                });
            }
        }
    }
</script>