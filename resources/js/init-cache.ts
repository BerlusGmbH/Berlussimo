import {InMemoryCache} from "apollo-cache-inmemory";

let tokenElement = document.head.querySelector('meta[name="csrf-token"]');
let token = '';

if (tokenElement) {
    token = tokenElement['content'];
}

function initApolloCache(cache: InMemoryCache) {
    cache.writeData({
        data: {
            state: {
                __typename: "State",
                globalSelect: {
                    __typename: "GlobalSelect",
                    partner: null,
                    property: null,
                    bankAccount: null
                },
                message: '',
                isLegacy: false,
                phoneAtWorkplace: false,
                loading: 0,
                notificationsTabOpen: false,
                user: null,
                csrf: token,
                messages: {
                    __typename: "DisplayMessages",
                    info: [],
                    success: [],
                    warning: [],
                    error: []
                }
            }
        }
    });
    return Promise.resolve();
}

export {initApolloCache};
