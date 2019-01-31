<template>
    <v-dialog :value="value"
              @input="$emit('input', $event)"
              height="70%"
              no-click-animation
              persistent
    >
        <v-card>
            <v-card-title
                    class="headline"
                    primary-title
            >
                Abfrage
            </v-card-title>

            <v-card-text>
                <textarea :value="queryValue" id="codemirror" ref="textarea"></textarea>
            </v-card-text>

            <v-divider></v-divider>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                        @click="$emit('input', false)"
                        color="primary"
                        flat
                >
                    Abbrechen
                </v-btn>
                <v-btn
                        :disabled="errors"
                        @click="onSend"
                        color="primary"
                >
                    Senden
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import CodeMirror from "codemirror";
    import MD from 'markdown-it';
    import {buildASTSchema} from 'graphql';
    import BerlussimoSchema from "../../../../../storage/app/lighthouse-schema.graphql";
    import 'codemirror/addon/hint/show-hint';
    import 'codemirror/addon/lint/lint';
    import 'codemirror/addon/comment/comment';
    import 'codemirror/addon/edit/matchbrackets';
    import 'codemirror/addon/edit/closebrackets';
    import 'codemirror/addon/dialog/dialog';
    import 'codemirror/addon/fold/foldgutter';
    import 'codemirror/addon/fold/brace-fold';
    import './lint';
    import './hint';
    import 'codemirror-graphql/info';
    import 'codemirror-graphql/mode';
    import onHasCompletion from "./onHasCompletion"

    const schema = buildASTSchema(BerlussimoSchema);
    const md = new MD();
    const AUTO_COMPLETE_AFTER_KEY = /^[a-zA-Z0-9_@(.]$/;

    @Component
    export default class EditQueryDialog extends Vue {

        @Prop({type: Boolean, default: false})
        value: boolean;

        @Prop({type: String, default: ''})
        query: string;

        @Prop({type: String, default: ''})
        fragments: string;

        queryValue: string = '';

        init: boolean = true;

        errors: boolean = true;

        cm: any = null;

        @Watch('value')
        onValueChange(v) {
            if (v && this.init) {
                setTimeout(() => {
                    this.cm = CodeMirror.fromTextArea(this.$refs.textarea, {
                        mode: 'graphql',
                        theme: 'mbo',
                        lineNumbers: true,
                        tabSize: 2,
                        lint: {
                            schema: schema,
                            appendFragments: this.fragments
                        },
                        hintOptions: {
                            schema: schema,
                            closeOnUnfocus: false,
                            completeSingle: false,
                            appendFragments: this.fragments
                        },
                        info: {
                            schema: schema,
                            renderDescription: text => md.render(text),
                        },
                        foldGutter: {
                            minFoldSize: 4,
                        },
                        gutters: [
                            'CodeMirror-lint-markers',
                            "CodeMirror-foldgutter"
                        ],
                        autoCloseBrackets: true,
                        matchBrackets: true,
                        autofocus: true,
                        extraKeys: {'Ctrl-Space': 'autocomplete'}
                    });
                    this.cm.on('update', cm => {
                        this.errors = document.getElementsByClassName('CodeMirror-lint-marker-error').length > 0;
                        if (!this.errors) {
                            this.queryValue = cm.getValue();
                        }
                    });
                    this.cm.on('keyup', (_cm, event) => {
                        if (AUTO_COMPLETE_AFTER_KEY.test(event.key)) {
                            this.cm.execCommand('autocomplete');
                        }
                    });
                    this.cm.on('hasCompletion', (cm, data) => onHasCompletion(cm, data, null));
                    setTimeout(() => {
                        this.cm.refresh();
                    }, 500);
                }, 100);
                this.init = false;
            }
            if (v && this.cm) {
                setTimeout(() => {
                    this.cm.refresh();
                }, 500);
            }
        }

        @Watch('query', {immediate: true})
        onQueryChange(query) {
            this.queryValue = query;
            if (!this.init) {
                this.cm.setValue(query);
            }
        }

        onSend() {
            this.$emit('input', false);
            this.$emit('update:query', this.queryValue);
        }
    }
</script>

<style lang="css">
    @import '~codemirror/lib/codemirror.css';
    @import '~codemirror/theme/mbo.css';
    @import '~codemirror/addon/fold/foldgutter.css';
    @import '~codemirror/addon/lint/lint.css';
    @import '~codemirror/addon/dialog/dialog.css';
    @import '~graphiql/dist/app.css';
    @import '~graphiql/dist/show-hint.css';
    @import '~graphiql/dist/info.css';


    .CodeMirror {
        height: 70vh;
    }

    .CodeMirror-hints {
        z-index: 1000;
    }

    .CodeMirror-lint-tooltip {
        z-index: 1000;
    }

    .CodeMirror-info {
        z-index: 1000;
    }
</style>
