import {ApolloLink, FetchResult, NextLink, Observable, Operation} from "apollo-link";
import router from "./router";
import {initApolloCache} from "./init-cache";

class LoginRedirectLink extends ApolloLink {

    request(operation: Operation, forward?: NextLink | undefined): Observable<FetchResult> {
        return new Observable(observer => {
            // Check the result of the operation
            if (!forward) {
                return;
            }
            forward(operation).subscribe({
                next: (data) => {
                    if(!this.onLoginScreen() && this.unauthenticated(data)) {
                        initApolloCache(operation.getContext().cache);
                        router.push({name: 'web.login'});
                    }
                    observer.next(data);
                    observer.complete();
                }
            });
        });
    }

    unauthenticated(data) {
        return data.errors && data.errors[0] && data.errors[0].message === "Unauthenticated.";
    }

    onLoginScreen() {
        return router.currentRoute.name === 'web.login';
    }
}

export default LoginRedirectLink;
