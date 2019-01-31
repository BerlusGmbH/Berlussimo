<template>
    <div>
        <template v-for="(typedMessages, type) in messages">
            <template v-if="type !== '__typename'">
                <template v-for="(message, i) in typedMessages">
                    <v-alert :error="type === 'error'"
                             :info="type === 'info'"
                             :success="type === 'success'"
                             :warning="type === 'warning'"
                             class="messages__alert"
                             dismissible
                             v-model="models[type][i]">
                        {{message}}
                    </v-alert>
                </template>
            </template>
        </template>
    </div>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import MessagesQuery from "./MessagesQuery.graphql";

    @Component({
        apollo: {
            messages: {
                query: MessagesQuery,
                update(data) {
                    return data.state.messages;
                }
            }
        }
    })
    export default class Messages extends Vue {
        messages: any;

        get models() {
            const types = Object.keys(this.messages);
            let models: any = {};
            for (const type of types) {
                if (this.messages[type] && type !== "__typename") {
                    models[type] = this.messages[type].map(() => {
                        return true;
                    }, this);
                }
            }
            return models;
        }
    }
</script>

<style>
    .messages__alert.alert {
        margin: 0;
        padding: 10px
    }
</style>
