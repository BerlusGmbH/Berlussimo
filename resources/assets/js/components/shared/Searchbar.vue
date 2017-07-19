<template>
    <form class="menu">
        <v-text-field :id="id" type="search" required autocomplete="off" prepend-icon="search"
                      v-model="query" @focusin="forceHideResult = false" @focusout="onFocusOut"
                      @keydown.down.prevent="selectNext" @keydown.enter.prevent="openSelected"
                      @keydown.up.prevent="selectPrevious" single-line hide-details></v-text-field>
        <v-list v-show="showResult" ref="answer"
                style="position: absolute; width: 100%; max-height: 500px; overflow-y: auto">
            <v-list-tile v-if="answerHasObjects">
                <v-list-tile-action>
                    <v-icon>mdi-city</v-icon>
                </v-list-tile-action>
                <v-list-tile-content>
                    <v-list-tile-title>Objekte</v-list-tile-title>
                </v-list-tile-content>
                <v-list-tile-action>
                    <v-chip label>{{answer['objekt'].length}}</v-chip>
                </v-list-tile-action>
            </v-list-tile>
            <v-divider v-if="answerHasObjects" inset></v-divider>
            <v-list-tile v-if="answerHasObjects" :id="entity['OBJEKT_ID']" v-for="(entity, index) in answer['objekt']"
                         :key="'objekt-' + entity['OBJEKT_ID']" tabindex='-1'>
                <v-list-tile-action></v-list-tile-action>
                <v-list-tile-content>
                    <v-list-tile-title>{{entity['OBJEKT_KURZNAME']}}</v-list-tile-title>
                </v-list-tile-content>
            </v-list-tile>
            <v-list-tile v-if="answerHasHouses">
                <v-list-tile-action>
                    <v-icon>mdi-domain</v-icon>
                </v-list-tile-action>
                <v-list-tile-content>
                    <v-list-tile-title>Häuser</v-list-tile-title>
                </v-list-tile-content>
                <v-list-tile-action>
                    <v-chip label>{{answer['haus'].length}}</v-chip>
                </v-list-tile-action>
            </v-list-tile>
            <v-divider v-if="answerHasHouses" inset></v-divider>
            <v-list-tile v-if="answerHasHouses" :id="entity['HAUS_ID']" v-for="(entity, index) in answer['haus']"
                         :key="'haus-' + entity['HAUS_ID']" tabindex='-1'>
                <v-list-tile-action></v-list-tile-action>
                <v-list-tile-content>
                    <v-list-tile-title>{{entity['HAUS_STRASSE']}} {{entity['HAUS_NUMMER']}}</v-list-tile-title>
                </v-list-tile-content>
            </v-list-tile>
            <v-list-tile v-if="answerHasUnits">
                <v-list-tile-action>
                    <v-icon>mdi-cube</v-icon>
                </v-list-tile-action>
                <v-list-tile-content>
                    <v-list-tile-title>Einheiten</v-list-tile-title>
                </v-list-tile-content>
                <v-list-tile-action>
                    <v-chip label>{{answer['einheit'].length}}</v-chip>
                </v-list-tile-action>
            </v-list-tile>
            <v-divider v-if="answerHasUnits" inset></v-divider>
            <v-list-tile v-if="answerHasUnits" :id="entity['EINHEIT_ID']" v-for="(entity, index) in answer['einheit']"
                         :key="'einheit-' + entity['EINHEIT_ID']" tabindex='-1'>
                <v-list-tile-action></v-list-tile-action>
                <v-list-tile-content>
                    <v-list-tile-title>{{entity['EINHEIT_KURZNAME']}}</v-list-tile-title>
                </v-list-tile-content>
            </v-list-tile>
            <v-list-tile v-if="answerHasPersons">
                <v-list-tile-action>
                    <v-icon>mdi-account</v-icon>
                </v-list-tile-action>
                <v-list-tile-content>
                    <v-list-tile-title>Personen</v-list-tile-title>
                </v-list-tile-content>
                <v-list-tile-action>
                    <v-chip label>{{answer['person'].length}}</v-chip>
                </v-list-tile-action>
            </v-list-tile>
            <v-divider v-if="answerHasPersons" inset></v-divider>
            <v-list-tile v-if="answerHasPersons" :id="entity['id']" v-for="(entity, index) in answer['person']"
                         :key="'person-' + entity['id']" tabindex='-1'>
                <v-list-tile-action></v-list-tile-action>
                <v-list-tile-content>
                    <v-list-tile-title>{{entity['name']}}, {{entity['first_name']}}</v-list-tile-title>
                </v-list-tile-content>
            </v-list-tile>
            <v-list-tile v-if="answerHasPartners">
                <v-list-tile-action>
                    <v-icon>mdi-account-multiple</v-icon>
                </v-list-tile-action>
                <v-list-tile-content>
                    <v-list-tile-title>Partner</v-list-tile-title>
                </v-list-tile-content>
                <v-list-tile-action>
                    <v-chip label>{{answer['partner'].length}}</v-chip>
                </v-list-tile-action>
            </v-list-tile>
            <v-divider v-if="answerHasPartners" inset></v-divider>
            <v-list-tile v-if="answerHasPartners" :id="entity['PARTNER_ID']"
                         v-for="(entity, index) in answer['partner']"
                         :key="'partner-' + entity['PARTNER_ID']" tabindex='-1'>
                <v-list-tile-action></v-list-tile-action>
                <v-list-tile-content>
                    <v-list-tile-title>{{entity['PARTNER_NAME']}}</v-list-tile-title>
                </v-list-tile-content>
            </v-list-tile>
        </v-list>
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
    </form>
</template>

<script lang="ts">
    import $ from "jquery";
    import _ from "lodash";
    import axios from "axios";
    import Vue from "vue";
    import {Prop, Watch} from "vue-property-decorator";
    import Component from "vue-class-component";
    import {Einheit, Haus, Objekt, Partner, Person} from "../../server/resources/models";

    class Selected {
        type: string = '';
        index: number | null = null;

        constructor(type: string, index: number | null) {
            this.type = type;
            this.index = index;
        }
    }

    @Component
    export default class Searchbar extends Vue {
        updated(): void {
            if (!this.searching && this.returnPressed) {
                this.returnPressed = false;
                this.openSelected();
            }
        }

        @Prop()
        id: string;

        @Prop({
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
        }) options: Object;


        query: string = '';
        answer: Object = {};
        selected: Selected = new Selected('', null);
        forceHideResult = false;
        searching = false;
        returnPressed = false;

        @Watch('query')
        onQueryChange() {
            this.searching = true;
            this.getAnswer()
        }

        get showResult() {
            return !this.forceHideResult
                    && this.answerHasResults
        }

        get answerHasObjects() {
            return this.answer['objekt'] && this.answer['objekt'].length > 0
        }

        get answerHasHouses() {
            return this.answer['haus'] && this.answer['haus'].length > 0
        }

        get answerHasUnits() {
            return this.answer['einheit'] && this.answer['einheit'].length > 0
        }

        get answerHasPersons() {
            return this.answer['person'] && this.answer['person'].length > 0
        }

        get answerHasPartners() {
            return this.answer['partner'] && this.answer['partner'].length > 0
        }

        get answerHasResults() {
            return this.answerHasObjects
                    || this.answerHasHouses
                    || this.answerHasUnits
                    || this.answerHasPersons
                    || this.answerHasPartners;
        }

        get escapedQuery() {
            return $.map(this.query.split(' '), function (item) {
                return '"' + item + '"';
            }).join(' ');
        }

        select(type: string, index: number | null): void {
            this.selected = new Selected(type, index);
        }

        selectFirst() {
            let keys = Object.keys(this.answer);
            for (let i = 0; i < keys.length; i++) {
                let key = keys[i];
                if (this.answer[key].length > 0) {
                    this.select(key, 0);
                    return;
                }
            }
        }

        selectNext = _.throttle(function (this: Searchbar) {
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
        }, 75);
        selectPrevious = _.throttle(function (this: Searchbar) {
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
        }, 75);

        isSelected(type: string, index: number | null) {
            return this.selected.type === type && this.selected.index === index;
        }

        scrollToSelected() {
            let $answer = $(this.$refs.answer);
            let $selected = $answer.find('.active').first();
            $answer.stop(false, true, true);
            if ($selected && $selected.position()) {
                $answer.animate({
                    scrollTop: $answer.scrollTop() - 200 + $selected.position().top
                }, 100);
            }
        }

        openSelected() {
            if (this.searching) {
                this.returnPressed = true;
                return;
            }
            let $answer = $(this.$refs.answer);
            let $selected = $answer.find('.active').first();
            if ($selected && $selected[0]) {
                $selected[0].click();
            }
        }

        onFocusOut(event) {
            this.forceHideResult = true;
            let $related = $(event.relatedTarget);
            let $answer = $(this.$refs.answer);
            if ($.contains($answer[0], $related[0])) {
                this.openSelected();
            }
        }

        getAnswer = _.debounce(function (this: Searchbar) {
            let vm = this;
            let token = document.head.querySelector('meta[name="csrf-token"]');

            if (token) {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = token['content'];
            }
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

            axios.get("/api/v1/search?q=" + this.query).then(function (response) {
                let data = response.data;
                $.each(data, function (key, val) {
                    switch (key) {
                        case 'objekt':
                            $.each(val, function (objekt_key, objekt) {
                                data[key][objekt_key] = Object.setPrototypeOf(objekt, Objekt.prototype);
                            });
                            break;
                        case 'haus':
                            $.each(val, function (haus_key, haus) {
                                data[key][haus_key] = Object.setPrototypeOf(haus, Haus.prototype);
                            });
                            break;
                        case 'einheit':
                            $.each(val, function (einheit_key, einheit) {
                                data[key][einheit_key] = Object.setPrototypeOf(einheit, Einheit.prototype);
                            });
                            break;
                        case 'person':
                            $.each(val, function (person_key, person) {
                                data[key][person_key] = Object.setPrototypeOf(person, Person.prototype);
                            });
                            break;
                        case 'partner':
                            $.each(val, function (partner_key, partner) {
                                data[key][partner_key] = Object.setPrototypeOf(partner, Partner.prototype);
                            });
                            break;
                    }
                });
                vm.answer = data;
                vm.selectFirst();
                vm.searching = false;
            }).catch(function (reason) {
                console.log("Request Failed: " + reason);
                vm.searching = false;
            });
        }, 300);
    }
</script>

<style>
    a.list__tile.active {
        background: rgba(0, 0, 0, .12);
    }
</style>