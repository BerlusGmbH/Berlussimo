<template></template>

<script lang="ts">
    import Vue from "vue";
    import Component from 'vue-class-component'
    import {namespace} from 'vuex-class';
    import {Prop} from 'vue-property-decorator';

    const AuthModule = namespace('auth');

    @Component
    export default class UserLoader extends Vue {
        @Prop({default: null})
        user: Object;

        @AuthModule.Mutation('updateUser')
        updateUser;

        mounted() {
            return this.updateUser(this.user);
        }
    }
</script>