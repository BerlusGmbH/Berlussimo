import {HttpLink} from 'apollo-link-http';
import {setContext} from 'apollo-link-context';
import {defaultDataIdFromObject, InMemoryCache, IntrospectionFragmentMatcher} from 'apollo-cache-inmemory';
import VueApollo from "vue-apollo";
import {Model} from "./models";
import {ApolloLink} from "apollo-link";
import PusherLink from "./PusherLink";
import Pusher from "pusher-js";
import ApolloClient from "apollo-client";
import introspectionQueryResultData from '../../storage/app/fragmentTypes.json';
import {initApolloCache} from "./init-cache";
import typeDefs from "./ClientSchema.graphql";
import {toggleNotificationsTabOpen, updateMessage} from "./resolvers";
import CSRFTokenFragment from "./CSRFTokenFragment.graphql";
import gql from "graphql-tag";
import LoginRedirectLink from "./LoginRedirectLink";

const apiEndpoint = window.location.origin + '/graphql';
const apiPort = window.location.port;

const fragmentMatcher = new IntrospectionFragmentMatcher({
    introspectionQueryResultData
});

const cache = new InMemoryCache({
    fragmentMatcher,
    dataIdFromObject(object) {
        switch (object.__typename) {
            case 'State':
                return object.__typename;
            case 'GlobalSelect':
                return object.__typename;
            default:
                return defaultDataIdFromObject(object);
        }
    }
});

initApolloCache(cache);


const pusherLink = new PusherLink({
    pusher: new Pusher('berlussimo', {
        authEndpoint: window.location.origin + '/graphql/subscriptions/auth',
        auth: {
            headers: {
                'X-CSRF-Token': cache.readFragment<any>({fragment: CSRFTokenFragment, id: 'State'}).csrf
            }
        },
        disableStats: true,
        wsHost: window.location.hostname,
        wsPort: apiPort,
        wssPort: apiPort
    }),
    cache
});

const csrfTokenLink = setContext((_, {headers}) => {
    // get the authentication token from local storage if it exists
    const token = cache.readFragment<any>({fragment: CSRFTokenFragment, id: 'State'}).csrf;
    // return the headers to the context so httpLink can read them
    return {
        headers: {
            ...headers,
            'X-CSRF-Token': token ? token : "",
        }
    }
});

const loginRedirectLink = new LoginRedirectLink();

const httpLink = new HttpLink({
    uri: apiEndpoint,
    credentials: 'same-origin',
});

const link = ApolloLink.from([csrfTokenLink, pusherLink, loginRedirectLink, httpLink]);

const apolloClient = new ApolloClient({
    link,
    cache,
    resolvers: {
        Mutation: {
            updateMessage,
            toggleNotificationsTabOpen
        }
    },
    typeDefs
});

apolloClient.onResetStore(() => initApolloCache(cache));
apolloClient.onClearStore(() => initApolloCache(cache));

const LoadingFragment = gql`
    fragment Loading on State {
        loading
    }
`;

const apolloProvider = new VueApollo({
    defaultClient: apolloClient,
    defaultOptions: {
        $query: {
            update(data) {
                for (let k of Object.keys(data)) {
                    return Model.applyPrototype(data[k]);
                }
            }
        }
    },
    watchLoading(_, countModifier): number {
        let loading = cache.readFragment<any>({fragment: LoadingFragment, id: 'State'}).loading;
        loading += countModifier;
        //cache.writeData({id: 'State', data: {loading: loading}});
        cache.writeFragment({
            id: 'State',
            fragment: gql`
                fragment UpdateLoading on State {
                    loading
                }
            `,
            data: {
                __typename: 'State',
                loading
            }
        });
        return loading;
    }
});

export default apolloProvider;
