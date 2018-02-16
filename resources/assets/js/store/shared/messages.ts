export default {
    namespaced: true,
    state() {
        return {
            messages: {
                'info': [],
                'success': [],
                'warning': [],
                'error': []
            }
        }
    },
    mutations: {
        updateMessages(state, parameters) {
            state.messages[parameters.type] = parameters.messages;
        },
        appendMessage(state, message) {
            state.messages.unshift(message);
        }
    }
}