<script lang="ts">
    import Vue from "../../imports";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";

    @Component({extends: Vue.component('v-edit-dialog')})
    export default class VEditDialog extends Vue {
        transition;
        isActive;
        lazy;
        focus;
        isSaving;
        cancel;
        save;
        large;

        @Prop({type: String, default: 'Speichern'})
        saveText;

        @Prop({type: String, default: 'Abbrechen'})
        cancelText;

        @Prop({type: Boolean})
        positionAbsolutley;

        @Prop({type: Number})
        positionX;

        @Prop({type: Number})
        positionY;

        @Prop({type: Boolean})
        show;

        @Watch('show')
        onShowChange(val) {
            this.isActive = val;
        }

        @Watch('isActive')
        onIsActiveChange(val) {
            this.$emit('show', val);
        }

        genActivator() {
            if (this.$slots.default) {
                return this.$createElement('a', {
                    domProps: {href: 'javascript:;'},
                    slot: 'activator'
                }, this.$slots.default)
            } else {
                return '';
            }
        }

        genContent() {
            return this.$createElement('div', {
                on: {
                    keydown: this.onKeydown
                },
                ref: 'content'
            }, [this.$slots.input])
        }

        genButton(fn, text, emphasize = false) {
            return this.$createElement('v-btn', {
                'class': {'red': emphasize},
                props: {
                    flat: !emphasize,
                    light: false
                },
                on: {click: fn}
            }, text)
        }

        genActions(h: Function) {
            return h('div', {
                'class': 'small-dialog__actions',
                directives: [{
                    name: 'show',
                    value: this.large
                }]
            }, [
                this.genButton(this.cancel, this.cancelText),
                this.genButton(this.save, this.saveText, true)
            ])
        }

        onKeydown(e) {
            if (!this.large) {
                e.keyCode === 27 && this.cancel();
                e.keyCode === 13 && this.save();
            }
        }

        render(h) {
            return h('v-menu', {
                'class': 'small-dialog',
                props: {
                    contentClass: 'small-dialog__content',
                    transition: this.transition,
                    origin: 'top right',
                    right: true,
                    value: this.isActive,
                    closeOnContentClick: false,
                    lazy: this.lazy,
                    positionAbsolutely: this.positionAbsolutley,
                    positionX: this.positionX,
                    positionY: this.positionY
                },
                on: {
                    input: val => (this.isActive = val)
                }
            }, [
                this.genActivator(),
                this.genContent(),
                this.genActions(h)
            ])
        }
    }
</script>