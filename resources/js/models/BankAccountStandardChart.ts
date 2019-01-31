import {ModelBase} from "./ModelBase";
import {BookingAccount} from "./BookingAccount";


export class BankAccountStandardChart extends ModelBase {
    static __typename = 'BankAccountStandardChart';
    id: number = -1;
    name: string = '';
    bookingAccounts: BookingAccount[] = [];

    getID() {
        return this.id;
    }

    toString(): string {
        return this.name;
    }

    getEntityIcon(): string {
        return 'mdi-table';

    }

    getDetailUrl(): string {
        return BankAccountStandardChart.getBaseURL();
    }
}