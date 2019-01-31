import {ModelBase} from "./ModelBase";

export class BookingAccount extends ModelBase {
    static __typename = 'BookingAccount';
    id: number = -1;
    number: string = '';
    name: string = '';

    getID() {
        return this.id;
    }

    toString(): string {
        return this.name;
    }

    getEntityIcon(): string {
        return 'mdi-numeric';

    }

    getDetailUrl(): string {
        return BookingAccount.getBaseURL();
    }
}