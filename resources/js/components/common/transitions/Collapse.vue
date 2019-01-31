<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";

    @Component
    export default class Collapse extends Vue {

        @Prop({default: 'div', type: String})
        tag;

        style: {
            height: string,
            opacity: string
        } = {
            height: '0px',
            opacity: '1'
        };

        beforeEnter() {
            this.style.height = '0px';
            this.style.opacity = '0'
        }

        enter(el, done) {
            window.requestAnimationFrame(() => {
                this.style.height = el.clientHeight + 'px';
                this.style.opacity = '1';
                setTimeout(done, 500);
            });
        }

        afterEnter() {
            this.style.height = '';
            this.style.opacity = '';
        }

        beforeLeave(el) {
            this.style.height = el.clientHeight + 'px';
        }

        leave(_el, done) {
            window.requestAnimationFrame(() => {
                this.style.height = '0px';
                this.style.opacity = '0';
                setTimeout(done, 500);
            });
        }

        afterLeave() {
            this.style.height = '0px';
        }

        render(createElement) {
            return createElement(
                this.tag,
                {
                    'class': {
                        'transition-collapse': true
                    },
                    style: this.style
                },
                [createElement(
                    'transition',
                    {
                        props: {
                            css: false
                        },
                        on: {
                            'before-enter': this.beforeEnter,
                            enter: this.enter,
                            'after-enter': this.afterEnter,
                            'before-leave': this.beforeLeave,
                            leave: this.leave
                        }
                    },
                    Array.isArray(this.$slots.default) ? this.$slots.default : [this.$slots.default]
                )]
            );
        }
    }
</script>

<style>
    .transition-collapse {
        transition: all .5s;
        overflow: hidden;
    }
</style>