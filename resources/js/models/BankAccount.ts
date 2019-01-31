import {ModelBase} from "./ModelBase";

export class BankAccount extends ModelBase {
    static __typename = 'BankAccount';
    id: number = -1;
    name: string = '';
    beneficiar: string = '';
    accountNumber: string = '';
    bankCode: string = '';
    IBAN: string = '';
    BIC: string = '';
    bank: string = '';

    getID() {
        return this.id;
    }

    toString(): string {
        return this.name;
    }

    getEntityIcon(): string {
        return 'mdi-currency-eur';
    }

    getEntityIconTooltips(): string[] {
        return ['Bankkonto'];
    }

    getAccount(): string {
        return 'IBAN: ' + this.IBAN + ' BIC: ' + this.BIC;
    }
}