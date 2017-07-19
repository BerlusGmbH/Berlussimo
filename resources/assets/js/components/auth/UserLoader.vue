<template></template>

<script lang="ts">
    import Vue from "vue";
    import Component from 'vue-class-component'
    import {Mutation, namespace} from 'vuex-class';
    import {Prop} from 'vue-property-decorator';

    const AuthMutation = namespace('auth', Mutation);

    @Component
    export default class UserLoader extends Vue {
        @Prop({default: null})
        user: Object;

        @AuthMutation('updateUser')
        updateUser;

        mounted() {
            return this.updateUser(this.user);
        }
    }
</script>