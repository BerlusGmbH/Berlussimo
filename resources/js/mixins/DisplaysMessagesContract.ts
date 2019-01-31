import {FetchResult} from "apollo-link";


export interface DisplaysMessagesContract {
    showMessage<R = any>(message: string): Promise<FetchResult<R>>;
}
