<template>
    <div class="input-field">
        <i v-if="!multiple" :class="{active: inputIsFocused}" class="mdi mdi-tag prefix"></i>
        <i v-if="multiple" :class="{active: inputIsFocused}" class="mdi mdi-tag-multiple prefix"></i>
        <div :class="{focus: inputIsFocused}" @click="focusInput" class="chips">
            <chip ref="chips" v-for="(person, index) in selected" :key="person.constructor.name + '.' + person.id"
                  :entity="person"
                  @keyup.native.left.stop="prevChip(index, $event)" @keyup.native.right.stop="nextChip(index, $event)"
                  @focusin.native="onFocusIn" @focusout.native="onFocusOut"
                  @keydown.native.delete.prevent="remove(index)"
                  @click.native.stop="" @remove="remove(index)">
            </chip>
            <input :id="id" type="text" ref="input" autocomplete="off"
                   style="width: 200px; margin: 0; border-bottom: 0; box-shadow: none;"
                   v-model="query" @focusin="onFocusIn" @keydown.left.stop="prevChip(null, $event)"
                   @keydown.8="prevChip(null, $event)" :placeholder="placeholder"
                   @focusout="onFocusOut" @keydown.down.prevent="onDown" @keyup.enter.prevent="onSelect"
                   @keydown.up.prevent="onUp">
            <span v-if="answerHasResults && !searching" class='new badge selector-input-indicator'
                  data-badge-caption=''>{{answer.count}}</span>
            <div v-show="searching" class="preloader-wrapper small active selector-input-indicator">
                <div class="spinner-layer">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="gap-patch">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>
            </div>
        </div>
        <ul v-show="showResult" ref="answer" class="selector-content">
            <li v-if="answerHasObjects" class='primary-color text-variation-2'>
                <a tabindex='-1'
                   class='active-alternative primary-color text-variation-2'>Objekte<span
                        class='new badge' data-badge-caption=''>{{answer['objekt'].length}}</span></a>
            </li>
            <li :id="entity.OBJEKT_ID" v-for="(entity, index) in answer['objekt']"
                :class="{active: isFocused('objekt', index)}"
                @mouseenter="updateFocused({ type: 'objekt', index: index})">
                <a tabindex='-1' @click="onSelect">{{String(entity)}}</a>
                <a tabindex='-1' class="right selector-info-link" target="_blank" :href="entity.getDetailUrl()">
                    <i class="mdi mdi-information"></i>
                </a>
            </li>
            <li v-if="answerHasHouses" class='primary-color text-variation-2'>
                <a tabindex='-1'
                   class='active-alternative primary-color text-variation-2'>Häuser<span
                        class='new badge' data-badge-caption=''>{{answer['haus'].length}}</span></a>
            </li>
            <li :id="entity.HAUS_ID" v-for="(entity, index) in answer['haus']">
                <a :class="{active: isFocused('haus', index)}"
                   @mouseenter="updateFocused({ type: 'haus', index: index})"
                   tabindex='-1'>{{String(entity)}}</a>
            </li>
            <li v-if="answerHasUnits" class='primary-color text-variation-2'>
                <a tabindex='-1'
                   class='active-alternative primary-color text-variation-2'>Einheiten<span
                        class='new badge' data-badge-caption=''>{{answer['einheit'].length}}</span></a>
            </li>
            <li :id="entity.EINHEIT_ID" v-for="(entity, index) in answer['einheit']"
                :class="{active: isFocused('einheit', index)}"
                @mouseenter="updateFocused({ type: 'einheit', index: index})">
                <a tabindex='-1' @click="onSelect">{{String(entity)}}</a>
                <a tabindex='-1' class="right selector-info-link" target="_blank" :href="entity.getDetailUrl()">
                    <i class="mdi mdi-information"></i>
                </a>
            </li>
            <li v-if="answerHasPersons" class='primary-color text-variation-2'>
                <a tabindex='-1'
                   class='active-alternative primary-color text-variation-2'>Personen<span
                        class='new badge' data-badge-caption=''>{{answer['person'].length}}</span></a>
            </li>
            <li :id="entity.id" v-for="(entity, index) in answer['person']"
                :class="{active: isFocused('person', index)}"
                @mouseenter="updateFocused({ type: 'person', index: index})">
                <a tabindex='-1' @click="onSelect">{{String(entity)}}</a>
                <a tabindex='-1' class="right selector-info-link" target="_blank" :href="entity.getDetailUrl()">
                    <i class="mdi mdi-information"></i>
                </a>
            </li>
            <li v-if="answerHasPartners" class='primary-color text-variation-2'>
                <a tabindex='-1'
                   class='active-alternative primary-color text-variation-2'>Partner<span
                        class='new badge' data-badge-caption=''>{{answer['partner'].length}}</span></a>
            </li>
            <li :id="entity.PARTNER_ID" v-for="(entity, index) in answer['partner']"
                :class="{active: isFocused('partner', index)}"
                @mouseenter="updateFocused({ type: 'partner', index: index})">
                <a tabindex='-1' @click="onSelect">{{String(entity)}}</a>
                <a tabindex='-1' class="right selector-info-link" target="_blank" :href="entity.getDetailUrl()">
                    <i class="mdi mdi-information"></i>
                </a>
            </li>
        </ul>
    </div>
</template>

<script>
    export default {
        props: {
            id: String,
            multiple: Boolean,
            entities: {
                type: Array,
                default: []
            }
        },
        data() {
            return {
                query: '',
                inputIsFocused: false,
                waiting: false
            }
        },
        watch: {
            query() {
                this.search({query: this.query, entities: this.entities})
            },
            answer() {
                if (this.waiting) {
                    this.waiting = false;
                    this.onSelect();
                } else if (this.answerHasResults) {
                    this.$nextTick(function () {
                        this.scrollToFocused();
                    });
                }
            }
        },
        computed: {
            showResult: function () {
                return this.inputIsFocused
                    && this.answerHasResults
            },
            placeholder() {
                if (this.multiple) {
                    if (this.entities.length > 1) {
                        if (this.selected.length === 0) {
                            return 'Entität wählen'
                        } else {
                            return 'Weitere Entität wählen'
                        }
                    } else {
                        if (this.selected.length === 0) {
                            return this.entities[0][0].toUpperCase() + this.entities[0].substr(1) + ' wählen'
                        } else {
                            return 'Weitere' + this.entities[0][0].toUpperCase() + this.entities[0].substr(1) + ' wählen'
                        }
                    }
                } else {
                    if (this.entities.length > 1) {
                        if (this.selected.length === 0) {
                            return 'Entität wählen'
                        } else {
                            return 'Entität wechseln'
                        }
                    } else {
                        if (this.selected.length === 0) {
                            return this.entities[0][0].toUpperCase() + this.entities[0].substr(1) + ' wählen'
                        } else {
                            return this.entities[0][0].toUpperCase() + this.entities[0].substr(1) + ' wechseln'
                        }
                    }
                }
            }
        },
        components: {
            chip: require('./Chip.vue')
        },
        methods: {
            isFocused: function (type, index) {
                return this.focused.type === type && this.focused.index === index;
            },
            focusInput() {
                this.$refs.input.focus();
            },
            scrollToFocused: function () {
                let $answer = $(this.$refs.answer);
                let $focused = $answer.find('.active').first();
                $answer.stop(false, true, true);
                if ($focused && $focused.position()) {
                    $answer.animate({
                        scrollTop: $answer.scrollTop() - 150 + $focused.position().top
                    }, 100);
                }
            },
            onFocusIn: function () {
                this.inputIsFocused = true;
            },
            onFocusOut: function (event) {
                this.inputIsFocused = false;
                let $related = $(event.relatedTarget);
                let $answer = $(this.$refs.answer);
                if ($.contains($answer[0], $related[0])) {
                    $related[0].click();
                }
            },
            remove(index) {
                this.removeSelected(index);
                if (this.selected.length === 0) {
                    this.$refs.input.focus();
                } else if (this.selected.length > index) {
                    this.$refs.chips[index].$el.focus();
                } else {
                    this.$refs.chips[this.selected.length - 1].$el.focus();
                }
            },
            prevChip(index, event) {
                if (this.$refs.chips && this.$refs.chips.length > 0) {
                    if (index === null && event.target.selectionStart === 0) {
                        this.$refs.chips[this.$refs.chips.length - 1].$el.focus();
                    } else if (index > 0) {
                        this.$refs.chips[index - 1].$el.focus();
                    }
                }
            },
            nextChip(index) {
                if (this.selected !== []) {
                    if (index === this.$refs.chips.length - 1) {
                        this.$refs.input.focus();
                    } else if (index > 0) {
                        this.$refs.chips[index + 1].$el.focus();
                    }
                }
            },
            onUp: _.throttle(function () {
                this.focusPrevious();
                this.$nextTick(function () {
                    this.scrollToFocused();
                });
            }, 75),
            onDown: _.throttle(function () {
                this.focusNext();
                this.$nextTick(function () {
                    this.scrollToFocused();
                });
            }, 75),
            onSelect() {
                if (!this.searching) {
                    this.select();
                    this.query = '';
                    this.$nextTick(function () {
                        this.$refs.input.focus();
                    });
                } else {
                    this.waiting = true;
                }
            }
        }
    }
</script>

<style>
    .selector-content {
        position: absolute;
        width: 92%;
        width: calc(100% - 3rem);
        margin-left: 3rem;
        min-width: 100px;
        max-height: 350px;
        overflow-y: auto;
        opacity: 1;
        margin-top: 0;
        background-color: #fff;
        z-index: 999;
        box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.14), 0 1px 5px 0 rgba(0, 0, 0, 0.12), 0 3px 1px -2px rgba(0, 0, 0, 0.2);
    }

    .selector-content li {
        clear: both;
        cursor: pointer;
        min-height: 50px;
        line-height: 1.5rem;
        width: 100%;
        text-align: left;
        text-transform: none;
    }

    .selector-content li > a {
        font-size: 16px;
        display: block;
        line-height: 22px;
        padding: 14px 16px;
    }

    .selector-input-indicator {
        position: absolute;
        top: 5px;
        right: 0;
    }

    .selector-info-link {
        font-size: 22px !important;
        position: absolute;
        top: 0;
        right: 0;
    }

    .selector-content li {
        position: relative;
    }

    .selector-content li.active {
        outline: 2px solid black;
        outline-offset: -2px;
    }
</style>