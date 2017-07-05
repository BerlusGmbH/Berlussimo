export default {
    namespaced: true,
    state() {
        return {
            answer: [],
            selected: [],
            focused: {
                'type': null,
                'index': null
            },
            searching: false,
            request: null
        }
    },
    mutations: {
        updateAnswer(state, answer) {
            state.answer = answer;
        },
        addSelected(state, selected) {
            if (state.multiple) {
                state.selected.push(selected);
            } else {
                state.selected = [selected];
            }
        },
        removeSelected(state, index) {
            state.selected.splice(index, 1);
        },
        updateSearching(state, searching) {
            state.searching = searching;
        },
        updateFocused(state, focused) {
            state.focused = {
                type: focused.type,
                index: focused.index
            }
        },
        updateRequest(state, request) {
            state.request = request;
        }
    },
    getters: {
        answerHasObjects: (state) => {
            return state.answer['objekt'] && state.answer['objekt'].length > 0
        },
        answerHasHouses: (state) => {
            return state.answer['haus'] && state.answer['haus'].length > 0
        },
        answerHasUnits: (state) => {
            return state.answer['einheit'] && state.answer['einheit'].length > 0
        },
        answerHasPersons: (state) => {
            return state.answer['person'] && state.answer['person'].length > 0
        },
        answerHasPartners: (state) => {
            return state.answer['partner'] && state.answer['partner'].length > 0
        },
        answerHasResults: (state, getters) => {
            return getters.answerHasObjects
                || getters.answerHasHouses
                || getters.answerHasUnits
                || getters.answerHasPersons
                || getters.answerHasPartners;
        },
        firstSelected: state => {
            return state.selected.length > 0 ? state.selected[0] : null;
        }
    },
    actions: {
        search: function ({state, commit, dispatch}, parameter) {
            if (!state.searching) {
                commit('updateSearching', true);
            }
            if (state.answer !== []) {
                commit('updateAnswer', []);
            }
            dispatch('getAnswer', parameter);
        },
        getAnswer: _.debounce(function ({commit, state, dispatch}, {query, entities}) {
            if (state.request && state.request.state() === "pending") {
                state.request.abort();
            }
            if (query !== '') {
                let request = $.getJSON("/api/v1/search", {q: query, e: entities}).done(function (data) {
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
                    commit('updateSearching', false);
                    commit('updateAnswer', data);
                    commit('updateRequest', null);
                    dispatch('focusFirst');
                    if (state.waiting) {
                        commit('updateWaiting', false);
                        dispatch('select');
                    }
                }).fail(function (jqxhr, textStatus) {
                    if (textStatus !== "abort") {
                        commit('updateSearching', false);
                        commit('updateRequest', null);
                    }
                });
                commit('updateRequest', request);
            } else {
                commit('updateSearching', false);
                commit('updateAnswer', []);
            }
        }, 300),
        select({commit, state, getters}) {
            if (!state.searching && getters.answerHasResults) {
                let entity = state.answer[state.focused.type][state.focused.index];
                commit('addSelected', entity);
                commit('updateAnswer', []);
            }
        },
        focusFirst: function ({commit, state}) {
            let keys = Object.keys(state.answer);
            for (let i = 0; i < keys.length; i++) {
                let key = keys[i];
                if (state.answer[key].length > 0) {
                    commit('updateFocused', {type: key, index: 0});
                    return;
                }
            }
        },
        focusNext({state, getters, commit}) {
            if (!getters.answerHasResults) {
                return;
            }
            let type = state.focused.type;
            let index = state.focused.index;
            let lastIndex = state.answer[type].length - 1;
            if (index === lastIndex) {
                let typeKeys = Object.keys(state.answer);
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
                    if ((state.answer[type] && state.answer[type].length > 0) || currTypeIndex === typeIndex) {
                        break;
                    }
                }
                index = 0;
            } else {
                index += 1;
            }
            commit('updateFocused', {type: type, index: index});
        },
        focusPrevious({getters, state, commit}) {
            if (!getters.answerHasResults) {
                return;
            }
            let type = state.focused.type;
            let index = state.focused.index;
            if (index === 0) {
                let typeKeys = Object.keys(state.answer);
                let typeIndex = typeKeys.indexOf(type);
                let currTypeIndex = typeIndex;
                while (true) {
                    if (currTypeIndex === 0) {
                        currTypeIndex = typeKeys.length - 1;
                    } else {
                        currTypeIndex -= 1;
                    }
                    type = typeKeys[currTypeIndex];
                    if ((state.answer[type] && state.answer[type].length > 0) || currTypeIndex === typeIndex) {
                        break;
                    }
                }
                index = state.answer[type].length - 1;
            } else {
                index -= 1;
            }
            commit('updateFocused', {type: type, index: index});
        },
    }
}