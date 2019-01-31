import {ApolloLink, FetchResult, NextLink, Observable, Operation} from "apollo-link";
import CSRFTokenFragment from "./CSRFTokenFragment.graphql";
import Pusher from "pusher-js";
import {InMemoryCache} from "apollo-cache-inmemory";

class PusherLink extends ApolloLink {

    pusher: Pusher;
    cache: InMemoryCache;

    constructor(options) {
        super();
        // Retain a handle to the Pusher client
        this.pusher = options.pusher;
        this.cache = options.cache;
    }

    request(operation: Operation, forward?: NextLink | undefined): Observable<FetchResult> {
        return new Observable(observer => {
            // Check the result of the operation
            if (!forward) {
                return;
            }
            forward(operation).subscribe({
                next: data => {
                    // If the operation has the subscription extension, it's a subscription
                    const subscriptionChannel = this._getChannel(
                        data,
                        operation
                    );

                    if (subscriptionChannel) {
                        this.pusher.config.auth.headers['X-CSRF-Token'] = this.cache.readFragment<any>({
                            fragment: CSRFTokenFragment,
                            id: 'State'
                        }).csrf;
                        this._createSubscription(subscriptionChannel, observer);
                    } else {
                        // No subscription found in the response, pipe data through
                        observer.next(data);
                        observer.complete();
                    }
                }
            });
        });
    }

    _getChannel(data, operation) {
        return !!data.extensions &&
        !!data.extensions.lighthouse_subscriptions &&
        !!data.extensions.lighthouse_subscriptions.channels
            ? data.extensions.lighthouse_subscriptions.channels[
                operation.operationName
                ]
            : null;
    }

    _createSubscription(subscriptionChannel, observer) {
        const pusherChannel = this.pusher.subscribe(subscriptionChannel);
        // Subscribe for more update
        pusherChannel.bind("lighthouse-subscription", payload => {
            if (!payload.more) {
                // This is the end, the server says to unsubscribe
                this.pusher.unsubscribe(subscriptionChannel);
                observer.complete();
            }
            const result = payload.result;
            if (result) {
                // Send the new response to listeners
                observer.next(result);
            }
        });
    }
}

export default PusherLink;
