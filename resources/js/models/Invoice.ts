import {ModelBase} from "./ModelBase";
import {BankAccount} from "./BankAccount";
import {InvoiceLine} from "./InvoiceLine";
import {Partner} from "./Partner";

export class Invoice extends ModelBase {
    static __typename = 'Invoice';
    id: number = -1;
    invoiceNumber: string;
    issuerInvoiceNumber: number;
    recipientInvoiceNumber: number;
    invoiceType: string;
    invoiceDate: string;
    dateOfReceipt: string;
    dueDate: string;
    payDate: string;
    netAmount: number;
    grossAmount: number;
    discountAmount: number;
    description: string;
    issuer: Partner;
    recipient: Partner;
    bankAccount: BankAccount;
    lines: InvoiceLine[];
    firstAdvancePayment: Invoice;
    advancePayments: Invoice[];
    serviceTimeStart: string;
    serviceTimeEnd: string;
    costForwarded: string;

    getID() {
        return this.id;
    }

    toString(): string {
        return this.invoiceNumber;
    }

    getEntityIcon(): string {
        return 'mdi-receipt';

    }

    getEntityIconTooltips(): Array<string> {
        let tooltips: Array<string> = [];
        tooltips.push(this.invoiceType);
        return tooltips;
    }

    getDetailUrl() {
        return Invoice.getBaseURL() + '/invoices/' + this.id;
    }

    isAdvancePayment() {
        return ['ADVANCE_PAYMENT_INVOICE', 'FINAL_ADVANCE_PAYMENT_INVOICE'].includes(this.invoiceType);
    }
}
