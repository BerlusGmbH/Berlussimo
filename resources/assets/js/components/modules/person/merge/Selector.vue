<script>
    import Selector from '../../../common/Selector.vue'

    const NAMESPACE = 'modules/person/merge';

    export default {
        extends: Selector,
        computed: {
            ...Vuex.mapGetters(
                NAMESPACE + '/selector',
                [
                    'answerHasResults',
                    'answerHasObjects',
                    'answerHasHouses',
                    'answerHasUnits',
                    'answerHasPersons',
                    'answerHasPartners'
                ]
            ),
            ...Vuex.mapState(
                NAMESPACE + '/selector',
                {
                    answer: state => state.answer,
                    selected: state => state.selected,
                    focused: state => state.focused,
                    searching: state => state.searching
                }
            ),
        },
        methods: {
            ...Vuex.mapMutations(
                NAMESPACE + '/selector',
                [
                    'updateFocused',
                    'removeSelected'
                ]
            ),
            ...Vuex.mapActions(
                NAMESPACE + '/selector',
                [
                    'search',
                    'select',
                    'focusFirst',
                    'focusNext',
                    'focusPrevious'
                ]
            ),
        }
    }
</script>