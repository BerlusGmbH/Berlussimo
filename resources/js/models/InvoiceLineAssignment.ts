import {ModelBase} from "./ModelBase"
import {InvoiceLine} from "./InvoiceLine";
import {BookingAccount} from "./BookingAccount";


export class InvoiceLineAssignment extends ModelBase {
    static __typename = 'InvoiceLineAssignment';
    id: number = -1;
    position: number;
    invoiceId: number;
    quantity: number;
    supplierId: number;
    itemNumber: string;
    price: number;
    VAT: number;
    rebate: number;
    discount: number;
    total: number;
    bookingAccountNumber: string;
    bookingAccount: BookingAccount;
    reassign: boolean = true;
    yearOfReassignment: string;
    accountingDate: string;
    costBearerType: string;
    costBearerId: number;
    costBearer: any;

    getID() {
        return this.id;
    }

    toString(): string {
        return this.total + ' €';
    }

    fill(filler: InvoiceLine) {
        this.position = filler.position;
        this.invoiceId = filler.invoiceId;
        this.quantity = filler.quantity;
        this.VAT = Number(filler.VAT);
        this.discount = Number(filler.discount);
        this.rebate = Number(filler.rebate);
        this.price = Number(filler.price);
    }

    getEntityIcon(): string {
        return 'mdi-receipt';

    }

    getDetailUrl() {
        return InvoiceLineAssignment.getBaseURL();
    }
}
