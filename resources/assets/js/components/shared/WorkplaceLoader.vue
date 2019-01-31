<template></template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Watch} from "vue-property-decorator";
    import {namespace} from "vuex-class";
    import {Person} from "../../server/resources";
    import axios from "../../libraries/axios";

    const Workplace = namespace('shared/workplace');

    const Auth = namespace('auth');

    @Component
    export default class WorkplaceLoader extends Vue {

        @Workplace.Mutation('updateHasPhone')
        updateHasPhone: Function;

        @Auth.Getter('check')
        check: Function;

        @Auth.State('user')
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