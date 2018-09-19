<template>
    <div>
        <template v-for="(typedMessages, type) in messages">
            <template v-for="(message, i) in typedMessages">
                <v-alert class="messages__alert" :info="type === 'info'" dismissible v-model="models[type][i]">
                    {{message}}
                </v-alert>
            </template>
        </template>
    </div>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {namespace} from "vuex-class";

    const MessagesModule = namespace('shared/messages');

    @Component
    export default class Messages extends Vue {
        @MessagesModule.State('messages')
        messages: Object;

        b: boolean = true;

        models: Object = {
            'success': [],
            'info': [],
            'warning': [],
            'error': []
        };

        created() {
            let types = Object.keys(this.messages);
            types.forEach((type) => {
                if (this.messages[type]) {
                    this.models[type] = this.messages[type].map(() => {
                        return true;
                    }, this)
                }
            }, this);
        }
    }
</script>

<style>
    .messages__alert.alert {
        margin: 0;
        padding: 10px
    }
</style>