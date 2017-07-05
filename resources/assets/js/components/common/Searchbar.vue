<template>
    <form>
        <div class="input-field">
            <input :id="id" type="search" required autocomplete="off"
                   v-model="query" @focusin="forceHideResult = false" @focusout="onFocusOut"
                   @keydown.down.prevent="selectNext" @keydown.enter.prevent="openSelected"
                   @keydown.up.prevent="selectPrevious">
            <label class="label-icon" :for="id"><i class="material-icons">search</i></label>
            <ul v-show="showResult" ref="answer" class="autocomplete-content dropdown-content">
                <li v-if="answerHasObjects" class='primary-color text-variation-2'>
                    <a tabindex='-1' :class="{active: isSelected('objekt', null)}"
                       @mouseenter="select('objekt', null)"
                       class='active-alternative primary-color text-variation-2'
                       :href='options.objektlisturl + escapedQuery'>Objekte<span
                            class='new badge' data-badge-caption=''>{{answer['objekt'].length}}</span></a>
                </li>
                <li :id="entity['OBJEKT_ID']" v-for="(entity, index) in answer['objekt']">
                    <a :class="{active: isSelected('objekt', index)}" @mouseenter="select('objekt', index)"
                       tabindex='-1' :href="options.objekturl + entity['OBJEKT_ID']">{{entity['OBJEKT_KURZNAME']}}</a>
                </li>
                <li v-if="answerHasHouses" class='primary-color text-variation-2'>
                    <a tabindex='-1' :class="{active: isSelected('haus', null)}"
                       @mouseenter="select('haus', null)"
                       class='active-alternative primary-color text-variation-2'
                       :href='options.hauslisturl + escapedQuery'>Häuser<span
                            class='new badge' data-badge-caption=''>{{answer['haus'].length}}</span></a>
                </li>
                <li :id="entity['HAUS_ID']" v-for="(entity, index) in answer['haus']">
                    <a :class="{active: isSelected('haus', index)}" @mouseenter="select('haus', index)"
                       tabindex='-1'
                       :href="options.hausurl + entity['HAUS_ID']">{{entity['HAUS_STRASSE'] + ' ' + entity['HAUS_NUMMER']}}</a>
                </li>
                <li v-if="answerHasUnits" class='primary-color text-variation-2'>
                    <a tabindex='-1' :class="{active: isSelected('einheit', null)}"
                       @mouseenter="select('einheit', null)"
                       class='active-alternative primary-color text-variation-2'
                       :href='options.einheitlisturl + escapedQuery'>Einheiten<span
                            class='new badge' data-badge-caption=''>{{answer['einheit'].length}}</span></a>
                </li>
                <li :id="entity['EINHEIT_ID']" v-for="(entity, index) in answer['einheit']">
                    <a :class="{active: isSelected('einheit', index)}" @mouseenter="select('einheit', index)"
                       tabindex='-1'
                       :href="options.einheiturl + entity['EINHEIT_ID']">{{entity['EINHEIT_KURZNAME']}}</a>
                </li>
                <li v-if="answerHasPersons" class='primary-color text-variation-2'>
                    <a tabindex='-1' :class="{active: isSelected('person', null)}"
                       @mouseenter="select('person', null)"
                       class='active-alternative primary-color text-variation-2'
                       :href='options.personlisturl + escapedQuery'>Personen<span
                            class='new badge' data-badge-caption=''>{{answer['person'].length}}</span></a>
                </li>
                <li :id="entity['id']" v-for="(entity, index) in answer['person']">
                    <a :class="{active: isSelected('person', index)}" @mouseenter="select('person', index)"
                       tabindex='-1' :href="options.personurl + entity.id">{{String(entity)}}</a>
                </li>
                <li v-if="answerHasPartners" class='primary-color text-variation-2'>
                    <a tabindex='-1' :class="{active: isSelected('partner', null)}"
                       @mouseenter="select('partner', null)"
                       class='active-alternative primary-color text-variation-2'
                       :href='options.partnerlisturl + escapedQuery'>Partner<span
                            class='new badge' data-badge-caption=''>{{answer['partner'].length}}</span></a>
                </li>
                <li :id="entity['PARTNER_ID']" v-for="(entity, index) in answer['partner']">
                    <a :class="{active: isSelected('partner', index)}" @mouseenter="select('partner', index)"
                       tabindex='-1' :href="options.partnerurl + entity['PARTNER_ID']">{{entity['PARTNER_NAME']}}</a>
                </li>
            </ul>
            <i v-if="!searching" id="searchbarClose" class="material-icons">close</i>
            <div v-if="searching" id="searchbarIndicator" class="preloader-wrapper small active"
                 style="position: absolute; top: 14px; right: 0.8rem">
                <div class="spinner-layer spinner-gray-only">
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
    </form>
</template>

<script>
    export default {
        updated: function () {
            if (!this.searching && this.returnPressed) {
                this.returnPressed = false;
                this.openSelected();
            }
        },
        props: {
            'id': String,
            'options': {
                type: Object,
                default: function () {
                    return {
                        loginurl: '/',
                        objekturl: '/',
                        objektlisturl: '/',
                        hausurl: '/',
                        hauslisturl: '/',
                        einheiturl: '/',
                        einheitlisturl: '/',
                        personurl: '/',
                        personlisturl: '/',
                        partnerurl: '/',
                        partnerlisturl: '/'
                    }
                }
            }
        },
        data: function () {
            return {
                query: '',
                answer: [],
                selected: {
                    'type': null,
                    'index': null
                },
                forceHideResult: false,
                searching: false,
                returnPressed: false,
            }
        },
        watch: {
            query: function () {
                this.searching = true;
                this.getAnswer()
            }
        },
        computed: {
            showResult: function () {
                return !this.forceHideResult
                    && this.answerHasResults
            },
            answerHasObjects: function () {
                return this.answer['objekt'] && this.answer['objekt'].length > 0
            },
            answerHasHouses: function () {
                return this.answer['haus'] && this.answer['haus'].length > 0
            },
            answerHasUnits: function () {
                return this.answer['einheit'] && this.answer['einheit'].length > 0
            },
            answerHasPersons: function () {
                return this.answer['person'] && this.answer['person'].length > 0
            },
            answerHasPartners: function () {
                return this.answer['partner'] && this.answer['partner'].length > 0
            },
            answerHasResults: function () {
                return this.answerHasObjects
                    || this.answerHasHouses
                    || this.answerHasUnits
                    || this.answerHasPersons
                    || this.answerHasPartners;
            },
            escapedQuery: function () {
                return $.map(this.query.split(' '), function (item) {
                    return '"' + item + '"';
                }).join(' ');
            }
        },
        methods: {
            getAnswer: _.debounce(function () {
                let vm = this;
                $.getJSON("/api/v1/search?q=" + this.query).done(function (data) {
                    $.each(data, function (key, val) {
                        switch (key) {
                            case 'objekt':
                                $.each(val, function (objekt_key, objekt) {
                                    data[key][objekt_key] = Object.setPrototypeOf(objekt, Models.Objekt.prototype);
                                });
                                break;
                            case 'haus':
                                $.each(val, function (haus_key, haus) {
                                    data[key][haus_key] = Object.setPrototypeOf(haus, Models.Haus.prototype);
                                });
                                break;
                            case 'einheit':
                                $.each(val, function (einheit_key, einheit) {
                                    data[key][einheit_key] = Object.setPrototypeOf(einheit, Models.Einheit.prototype);
                                });
                                break;
                            case 'person':
                                $.each(val, function (person_key, person) {
                                    data[key][person_key] = Object.setPrototypeOf(person, Models.Person.prototype);
                                });
                                break;
                            case 'partner':
                                $.each(val, function (partner_key, partner) {
                                    data[key][partner_key] = Object.setPrototypeOf(partner, Models.Partner.prototype);
                                });
                                break;
                        }
                    });
                    vm.answer = data;
                    vm.selectFirst();
                }).fail(function (jqxhr, textStatus, error) {
                    let err = textStatus + ", " + error;
                    console.log("Request Failed: " + err);
                }).always(function () {
                    vm.searching = false;
                });
            }, 300),
            select: function (type, index) {
                this.selected = {
                    'type': type,
                    'index': index
                }
            },
            selectFirst: function () {
                let keys = Object.keys(this.answer);
                for (let i = 0; i < keys.length; i++) {
                    let key = keys[i];
                    if (this.answer[key].length > 0) {
                        this.select(key, 0);
                        return;
                    }
                }
            },
            selectNext: _.throttle(function () {
                let type = this.selected.type;
                let index = this.selected.index;
                let lastIndex = this.answer[type].length - 1;
                if (index === lastIndex) {
                    let typeKeys = Object.keys(this.answer);
                    let typeIndex = typeKeys.indexOf(type);
                    let typeLastIndex = typeKeys.length - 1;
                    let currTypeIndex = typeIndex;
                    while (true) {
                        if (currTypeIndex === typeLastIndex) {
                            currTypeIndex = 0;
                        } else {
                            currTypeIndex += 1;
                        }
                        type = typeKeys[currTypeIndex];
                        if ((this.answer[type] && this.answer[type].length > 0) || currTypeIndex === typeIndex) {
                            break;
                        }
                    }
                    index = null;
                } else if (index === null) {
                    index = 0;
                } else {
                    index += 1;
                }
                this.select(type, index);
                this.$nextTick(function () {
                    this.scrollToSelected();
                });
            }, 75),
            selectPrevious: _.throttle(function () {
                let type = this.selected.type;
                let index = this.selected.index;
                if (index === null) {
                    let typeKeys = Object.keys(this.answer);
                    let typeIndex = typeKeys.indexOf(type);
                    let currTypeIndex = typeIndex;
                    while (true) {
                        if (currTypeIndex === 0) {
                            currTypeIndex = typeKeys.length - 1;
                        } else {
                            currTypeIndex -= 1;
                        }
                        type = typeKeys[currTypeIndex];
                        if ((this.answer[type] && this.answer[type].length > 0) || currTypeIndex === typeIndex) {
                            break;
                        }
                    }
                    index = this.answer[type].length - 1;
                } else if (index === 0) {
                    index = null;
                } else {
                    index -= 1;
                }
                this.select(type, index);
                this.$nextTick(function () {
                    this.scrollToSelected();
                });
            }, 75),
            isSelected: function (type, index) {
                return this.selected.type === type && this.selected.index === index;
            },
            scrollToSelected: function () {
                let $answer = $(this.$refs.answer);
                let $selected = $answer.find('.active').first();
                $answer.stop(false, true, true);
                if ($selected && $selected.position()) {
                    $answer.animate({
                        scrollTop: $answer.scrollTop() - 200 + $selected.position().top
                    }, 100);
                }
            },
            openSelected: function () {
                if (this.searching) {
                    this.returnPressed = true;
                    return;
                }
                let $answer = $(this.$refs.answer);
                let $selected = $answer.find('.active').first();
                if ($selected && $selected[0]) {
                    $selected[0].click();
                }
            },
            onFocusOut: function (event) {
                this.forceHideResult = true;
                let $related = $(event.relatedTarget);
                let $answer = $(this.$refs.answer);
                if ($.contains($answer[0], $related[0])) {
                    this.openSelected();
                }
            }
        }
    }
</script>